<?php

namespace Tests\Feature;

use App\Events\NewMessageEvent;
use App\Events\ReadMessageEvent;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		// Ensure broadcasting doesn't attempt real network calls during tests
		config(['broadcasting.default' => 'null']);
	}

	public function test_guests_are_redirected_from_chat_routes(): void
	{
		$user = User::factory()->create();
		$message = Chat::factory()->create();

		$this->get(route('chat.index'))
			->assertRedirect(route('login'));

		$this->get(route('chat.show', ['user' => $user->uuid]))
			->assertRedirect(route('login'));

		$this->post(route('chat.store', ['user' => $user->uuid]), ['message' => 'hello'])
			->assertRedirect(route('login'));

		$this->put(route('chat.update', ['chat' => $message->id]), ['message' => 'updated'])
			->assertRedirect(route('login'));

		$this->delete(route('chat.destroy', ['chat' => $message->id]))
			->assertRedirect(route('login'));
	}

	public function test_index_shows_users_list_in_inertia(): void
	{
		$me = User::factory()->create();
		$other = User::factory()->create();

		// Ensure there is conversation context so the user appears in the list
		Chat::factory()->create(['sender_id' => $me->id, 'receiver_id' => $other->id]);
		Chat::factory()->create(['sender_id' => $other->id, 'receiver_id' => $me->id]);

		$response = $this->actingAs($me)->get(route('chat.index'));

		$response->assertOk();
		$response->assertInertia(fn (Assert $page) => $page
			->component('Chat/Index')
			->has('users')
		);
	}

	public function test_show_redirects_when_chat_with_self(): void
	{
		$me = User::factory()->create();

		$this->actingAs($me)
			->get(route('chat.show', ['user' => $me->uuid]))
			->assertRedirect(route('chat.index'));
	}

	public function test_show_marks_unseen_messages_as_seen_and_dispatches_read_event(): void
	{
		Event::fake([ReadMessageEvent::class]);

		$me = User::factory()->create();
		$other = User::factory()->create();

		// Unseen messages from other -> me
		$m1 = Chat::factory()->create(['sender_id' => $other->id, 'receiver_id' => $me->id, 'seen_at' => null]);
		$m2 = Chat::factory()->create(['sender_id' => $other->id, 'receiver_id' => $me->id, 'seen_at' => null]);
		// Also some messages from me -> other
		Chat::factory()->create(['sender_id' => $me->id, 'receiver_id' => $other->id, 'seen_at' => null]);

		$response = $this->actingAs($me)->get(route('chat.show', ['user' => $other->uuid]));
		$response->assertOk();

		$this->assertNotNull($m1->fresh()->seen_at);
		$this->assertNotNull($m2->fresh()->seen_at);

		Event::assertDispatched(ReadMessageEvent::class);

		$response->assertInertia(fn (Assert $page) => $page
			->component('Chat/Show')
			->has('users')
			->has('chat_with')
			->has('messages')
		);
	}

	public function test_store_creates_message_and_broadcasts_event(): void
	{
		Event::fake([NewMessageEvent::class]);

		$me = User::factory()->create();
		$other = User::factory()->create();

		$response = $this->actingAs($me)
			->post(route('chat.store', ['user' => $other->uuid]), [
				'message' => 'Hello from test',
			]);

		$response->assertRedirect();

		$this->assertDatabaseHas('chats', [
			'sender_id' => $me->id,
			'receiver_id' => $other->id,
			'message' => 'Hello from test',
		]);

		Event::assertDispatched(NewMessageEvent::class);
	}

	public function test_update_only_sender_can_update_and_broadcasts_event(): void
	{
		Event::fake([NewMessageEvent::class]);

		$me = User::factory()->create();
		$other = User::factory()->create();
		$chat = Chat::factory()->create(['sender_id' => $me->id, 'receiver_id' => $other->id, 'message' => 'Old']);

		$response = $this->actingAs($me)
			->put(route('chat.update', ['chat' => $chat->id]), [
				'message' => 'Updated Message',
			]);

		$response->assertRedirect();
		$this->assertSame('Updated Message', $chat->fresh()->message);

		Event::assertDispatched(NewMessageEvent::class);
	}

	public function test_update_forbidden_for_non_sender(): void
	{
		Event::fake([NewMessageEvent::class]);

		$me = User::factory()->create();
		$other = User::factory()->create();
		$chat = Chat::factory()->create(['sender_id' => $other->id, 'receiver_id' => $me->id, 'message' => 'Original']);

		$response = $this->actingAs($me)
			->put(route('chat.update', ['chat' => $chat->id]), [
				'message' => 'Hacked',
			]);

		$response->assertStatus(403);
		$this->assertSame('Original', $chat->fresh()->message);
		Event::assertNotDispatched(NewMessageEvent::class);
	}

	public function test_destroy_soft_deletes_and_broadcasts_event(): void
	{
		Event::fake([NewMessageEvent::class]);

		$me = User::factory()->create();
		$other = User::factory()->create();
		$chat = Chat::factory()->create(['sender_id' => $me->id, 'receiver_id' => $other->id]);

		$response = $this->actingAs($me)
			->delete(route('chat.destroy', ['chat' => $chat->id]));

		$response->assertRedirect();
		$this->assertNotNull($chat->fresh()->message_deleted_at);
		Event::assertDispatched(NewMessageEvent::class);
	}

	public function test_destroy_forbidden_for_non_sender(): void
	{
		Event::fake([NewMessageEvent::class]);

		$me = User::factory()->create();
		$other = User::factory()->create();
		$chat = Chat::factory()->create(['sender_id' => $other->id, 'receiver_id' => $me->id]);

		$response = $this->actingAs($me)
			->delete(route('chat.destroy', ['chat' => $chat->id]));

		$response->assertStatus(403);
		$this->assertNull($chat->fresh()->message_deleted_at);
		Event::assertNotDispatched(NewMessageEvent::class);
	}

	public function test_show_inertia_contains_grouped_messages_structure(): void
	{
		$me = User::factory()->create();
		$other = User::factory()->create();

		// Create a few messages to ensure the grouping structure exists
		Chat::factory()->create(['sender_id' => $me->id, 'receiver_id' => $other->id]);
		Chat::factory()->create(['sender_id' => $other->id, 'receiver_id' => $me->id]);

		$response = $this->actingAs($me)
			->get(route('chat.show', ['user' => $other->uuid]));

		$response->assertOk();
		$response->assertInertia(fn (Assert $page) => $page
			->component('Chat/Show')
			->has('messages')
			->has('messages.0', fn (Assert $group) => $group
				->has('date')
				->has('messages')
			)
		);
	}
}


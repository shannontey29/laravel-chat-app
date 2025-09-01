<?php

namespace Tests\Unit;

use App\Events\NewMessageEvent;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewMessageEventTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function new_message_event_has_message_property()
    {
        $message = Chat::factory()->create();
        $event = new NewMessageEvent($message);

        $this->assertEquals($message, $event->message);
    }

    /** @test */
    public function new_message_event_broadcasts_on_private_channel()
    {
        $receiver = User::factory()->create(['uuid' => 'test-uuid']);
        $message = Chat::factory()->create(['receiver_id' => $receiver->id]);
        $message->load('receiver');
        
        $event = new NewMessageEvent($message);
        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
    // Private channels are prefixed with "private-" by Laravel
    $this->assertEquals('private-message.test-uuid', $channels[0]->name);
    }

    /** @test */
    public function new_message_event_implements_should_broadcast_now()
    {
        $message = Chat::factory()->create();
        $event = new NewMessageEvent($message);

        $this->assertInstanceOf(\Illuminate\Contracts\Broadcasting\ShouldBroadcastNow::class, $event);
    }

    /** @test */
    public function new_message_event_uses_dispatchable_trait()
    {
        $reflection = new \ReflectionClass(NewMessageEvent::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains('Illuminate\Foundation\Events\Dispatchable', $traits);
    }

    /** @test */
    public function new_message_event_uses_interacts_with_sockets_trait()
    {
        $reflection = new \ReflectionClass(NewMessageEvent::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains('Illuminate\Broadcasting\InteractsWithSockets', $traits);
    }

    /** @test */
    public function new_message_event_uses_serializes_models_trait()
    {
        $reflection = new \ReflectionClass(NewMessageEvent::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains('Illuminate\Queue\SerializesModels', $traits);
    }
}

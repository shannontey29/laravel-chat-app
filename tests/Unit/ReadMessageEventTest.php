<?php

namespace Tests\Unit;

use App\Events\ReadMessageEvent;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadMessageEventTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function read_message_event_has_message_property()
    {
        $message = Chat::factory()->create();
        $event = new ReadMessageEvent($message);

        $this->assertEquals($message, $event->message);
    }

    /** @test */
    public function read_message_event_broadcasts_on_private_channel()
    {
        $sender = User::factory()->create(['uuid' => 'sender-uuid']);
        $message = Chat::factory()->create(['sender_id' => $sender->id]);
        $message->load('sender');
        
        $event = new ReadMessageEvent($message);
        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
    // Private channels are prefixed with "private-" by Laravel
    $this->assertEquals('private-message.sender-uuid', $channels[0]->name);
    }

    /** @test */
    public function read_message_event_implements_should_broadcast_now()
    {
        $message = Chat::factory()->create();
        $event = new ReadMessageEvent($message);

        $this->assertInstanceOf(\Illuminate\Contracts\Broadcasting\ShouldBroadcastNow::class, $event);
    }

    /** @test */
    public function read_message_event_uses_dispatchable_trait()
    {
        $reflection = new \ReflectionClass(ReadMessageEvent::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains('Illuminate\Foundation\Events\Dispatchable', $traits);
    }

    /** @test */
    public function read_message_event_uses_interacts_with_sockets_trait()
    {
        $reflection = new \ReflectionClass(ReadMessageEvent::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains('Illuminate\Broadcasting\InteractsWithSockets', $traits);
    }

    /** @test */
    public function read_message_event_uses_serializes_models_trait()
    {
        $reflection = new \ReflectionClass(ReadMessageEvent::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains('Illuminate\Queue\SerializesModels', $traits);
    }
}

<?php

namespace Tests\Unit;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_defines_expected_fillable_and_casts()
    {
        $chat = new Chat();
        $this->assertSame([
            'sender_id', 'receiver_id', 'message', 'reply_id', 'seen_at', 'message_deleted_at'
        ], $chat->getFillable());

        $created = Chat::factory()->create([
            'seen_at' => '2024-01-01 10:00:00',
            'message_deleted_at' => '2024-01-01 11:00:00',
        ]);
        $this->assertInstanceOf(\Carbon\Carbon::class, $created->seen_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $created->message_deleted_at);
    }

    /** @test */
    public function relationships_sender_receiver_user_and_reply_work()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $chat = Chat::factory()->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
        ]);

        $this->assertTrue($chat->sender->is($sender));
        $this->assertTrue($chat->receiver->is($receiver));
        $this->assertTrue($chat->user->is($sender));

        $reply = Chat::factory()->reply($chat)->create();
        $this->assertEquals($chat->id, $reply->reply->id);
    }

    /** @test */
    public function factory_states_seen_and_deleted_work()
    {
        $seen = Chat::factory()->seen()->create();
        $this->assertNotNull($seen->seen_at);

        $deleted = Chat::factory()->deleted()->create();
        $this->assertNotNull($deleted->message_deleted_at);
    }
}

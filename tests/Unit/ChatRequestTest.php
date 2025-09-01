<?php

namespace Tests\Unit;

use App\Http\Requests\ChatRequest;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ChatRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function message_is_required()
    {
        $request = new ChatRequest();
        $validator = Validator::make(['message' => ''], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('message', $validator->errors()->toArray());
    }

    /** @test */
    public function message_must_be_string()
    {
        $request = new ChatRequest();
        $validator = Validator::make(['message' => 123], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('message', $validator->errors()->toArray());
    }

    /** @test */
    public function valid_message_passes_validation()
    {
        $request = new ChatRequest();
        $validator = Validator::make(['message' => 'Valid message'], $request->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function reply_id_is_optional()
    {
        $request = new ChatRequest();
        $validator = Validator::make(['message' => 'Valid message'], $request->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function reply_id_must_be_integer()
    {
        $request = new ChatRequest();
        $validator = Validator::make([
            'message' => 'Valid message',
            'reply_id' => 'not-integer'
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('reply_id', $validator->errors()->toArray());
    }

    /** @test */
    public function reply_id_must_exist_in_chats_table()
    {
        $request = new ChatRequest();
        $validator = Validator::make([
            'message' => 'Valid message',
            'reply_id' => 99999
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('reply_id', $validator->errors()->toArray());
    }

    /** @test */
    public function valid_reply_id_passes_validation()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        
        $chat = Chat::factory()->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
        ]);
        
        $request = new ChatRequest();
        $validator = Validator::make([
            'message' => 'Valid message',
            'reply_id' => $chat->id
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function authorize_returns_true()
    {
        $request = new ChatRequest();
        $this->assertTrue($request->authorize());
    }
}

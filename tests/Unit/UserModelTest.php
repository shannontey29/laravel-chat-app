<?php

namespace Tests\Unit;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function relationships_receive_send_and_messages_work_and_are_ordered_desc()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $recv1 = Chat::factory()->create(['sender_id' => $other->id, 'receiver_id' => $user->id]);
        $recv2 = Chat::factory()->create(['sender_id' => $other->id, 'receiver_id' => $user->id]);

        $send1 = Chat::factory()->create(['sender_id' => $user->id, 'receiver_id' => $other->id]);
        $send2 = Chat::factory()->create(['sender_id' => $user->id, 'receiver_id' => $other->id]);

        $this->assertEquals($recv2->id, $user->receiveMessages->first()->id);
        $this->assertEquals($recv1->id, $user->receiveMessages->last()->id);

        $this->assertEquals($send2->id, $user->sendMessages->first()->id);
        $this->assertEquals($send1->id, $user->sendMessages->last()->id);

        $this->assertTrue($user->messages->contains('id', $send1->id));
    }

    /** @test */
    public function fillable_hidden_casts_hashing_and_searchable_are_correct()
    {
        $user = new User();
        $this->assertSame([
            'name','username','email','password','uuid','last_seen_at','status'
        ], $user->getFillable());

        $this->assertSame(['password','remember_token'], $user->getHidden());

        $created = User::factory()->create([
            'email_verified_at' => '2024-01-01 00:00:00',
            'last_seen_at' => '2024-01-01 00:10:00',
            'password' => 'plain-text',
        ]);
        $this->assertInstanceOf(\Carbon\Carbon::class, $created->email_verified_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $created->last_seen_at);
        $this->assertTrue(\Hash::check('plain-text', $created->password));

        $this->assertSame('users_index', $user->searchableAs());

        $created->name = 'John';
        $created->username = 'johnny';
        $this->assertSame(['name' => 'John', 'username' => 'johnny'], $created->toSearchableArray());
    }
}

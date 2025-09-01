<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AuthenticatedSessionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_destroy_logs_user_out_and_updates_status_and_last_seen(): void
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $user = User::factory()->create([
            'status' => 'online',
            'last_seen_at' => null,
        ]);

        // Seed a session value to ensure session invalidation occurs
        $this->withSession(['foo' => 'bar']);

        $response = $this->actingAs($user)
            ->post(route('logout'));

        $response->assertRedirect('/');

        // User should be logged out
        $this->assertGuest('web');

        // User record should be updated
        $user->refresh();
        $this->assertSame('offline', $user->status);
        $this->assertNotNull($user->last_seen_at);
    $this->assertTrue($user->last_seen_at->isSameSecond($now));
    }
}

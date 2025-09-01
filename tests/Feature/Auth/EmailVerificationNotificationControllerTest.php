<?php

namespace Tests\Feature\Auth;

use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationNotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_receives_verification_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->post(route('verification.send'));

        $response->assertRedirect();
        $response->assertSessionHas('status', 'verification-link-sent');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_verified_user_is_redirected_to_home_without_sending(): void
    {
        Notification::fake();

        $user = User::factory()->create(); // verified by default in factory

        $response = $this->actingAs($user)
            ->post(route('verification.send'));

        $response->assertRedirect(RouteServiceProvider::HOME);
        Notification::assertNothingSent();
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NewPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_displays_reset_view_with_email_and_token(): void
    {
        $response = $this->get(route('password.reset', ['token' => 'token-xyz', 'email' => 'user@example.com']));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Auth/ResetPassword')
            ->where('email', 'user@example.com')
            ->where('token', 'token-xyz')
        );
    }

    public function test_store_resets_password_and_redirects_on_success(): void
    {
        Event::fake([PasswordReset::class]);

        $user = User::factory()->create([
            'email' => 'jane@example.com',
            'password' => Hash::make('old-password'),
        ]);

        $oldRemember = $user->remember_token;

        Password::shouldReceive('reset')
            ->once()
            ->andReturnUsing(function ($credentials, $closure) use ($user) {
                // Ensure controller passes through expected payload
                \PHPUnit\Framework\Assert::assertSame('jane@example.com', $credentials['email']);
                \PHPUnit\Framework\Assert::assertSame('new-password', $credentials['password']);
                \PHPUnit\Framework\Assert::assertSame('new-password', $credentials['password_confirmation']);
                \PHPUnit\Framework\Assert::assertSame('token-123', $credentials['token']);

                // Simulate broker invoking the closure with our user
                $closure($user);
                return Password::PASSWORD_RESET;
            });

        $response = $this->post(route('password.store'), [
            'email' => 'jane@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'token' => 'token-123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', trans(Password::PASSWORD_RESET));

        $user->refresh();
        $this->assertTrue(Hash::check('new-password', $user->password));
        $this->assertNotNull($user->remember_token);
        $this->assertNotSame($oldRemember, $user->remember_token);

        Event::assertDispatched(PasswordReset::class, function ($event) use ($user) {
            return $event->user->is($user);
        });
    }

    public function test_store_returns_validation_error_when_reset_fails(): void
    {
        Password::shouldReceive('reset')
            ->once()
            ->andReturn(Password::INVALID_TOKEN);

        $response = $this->from('/reset-password/token-123')
            ->post(route('password.store'), [
                'email' => 'john@example.com',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
                'token' => 'token-123',
            ]);

        // Redirect back with validation error on email
        $response->assertRedirect('/reset-password/token-123');
        $response->assertSessionHasErrors('email');
    }
}

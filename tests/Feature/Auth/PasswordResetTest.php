<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @test
     */
    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('api/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }
    /**
     * @test
     */
    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('api/forgot-password', ['email' => $user->email]);
        $new_password = 'new-password';
        Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use ($user,$new_password) {
            $response = $this->post('api/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => $new_password,
                'password_confirmation' => $new_password,
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertStatus(200);

            return true;
        });
        $updated_user = User::find($user->id);
        $this->assertTrue(Hash::check($new_password,$updated_user->password));
    }
}

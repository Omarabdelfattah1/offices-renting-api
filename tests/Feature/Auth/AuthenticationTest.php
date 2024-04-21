<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @test
     */
    public function it_can_login(): void
    {
        $user = User::factory()->create();

        $response = $this->post('api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertJsonStructure(['data'=>['token']]);
        $response2 = $this->get('api/user',[
            'Authorization' => 'Bearer '. $response->json()['data']['token']
        ]);
        $response2->assertOk();
    }
    /**
     * @test
     */
    public function it_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post('api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ],[
            'Accept' => 'Application/json'
        ]);
        $response->assertJsonValidationErrors(['email']);
    }
    /**
     * @test
     */
    public function it_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('new-token')->plainTextToken;
        $this->post('api/logout',[],[
            'Authorization' => 'Bearer '. $token,
            'Accept' => 'application/json'
        ]);
        $this->assertEquals(0,$user->tokens()->count());
    }
}

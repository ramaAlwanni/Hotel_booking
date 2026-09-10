<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\User;
use App\Notifications\OTPNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    //  Register Endpoint Tests (/api/user/register)
    // ============================================================

    /** @test */
    public function register_succeeds_and_returns_201_with_user_resource()
    {
        Notification::fake();

        $payload = [
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/user/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User registered successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'email',
                ],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    // ============================================================
    //  Login Endpoint Tests (/api/user/login)
    // ============================================================

    /** @test */
    public function login_succeeds_and_returns_200_with_token_payload()
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $payload = [
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/user/login', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User login successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'token_type',
                    'access_token_expires_at',
                    'user',
                ],
            ]);
    }

    // ============================================================
    //  Verify OTP Endpoint Tests (/api/user/verifyOtp)
    // ============================================================

    /** @test */
    public function verify_otp_succeeds_and_returns_200()
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $plainOtp = '123456';

        Cache::put("otp:user:{$user->id}", Hash::make($plainOtp), now()->addMinutes(5));

        $payload = [
            'email' => $user->email,
            'otp' => $plainOtp,
        ];

        $response = $this->postJson('/api/user/verifyOtp', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'OTP verified successfully',
                'data' => [
                    'email' => $user->email,
                ],
            ]);
    }

    // ============================================================
    //  Resend OTP Endpoint Tests (/api/user/resendOtp)
    // ============================================================

    /** @test */
    public function resend_otp_succeeds_and_returns_200()
    {
        Notification::fake();
        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->postJson('/api/user/resendOtp', ['email' => $user->email]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'OTP resent successfully. Please check your email.',
            ]);
    }

    // ============================================================
    //  Logout Endpoint Tests (/api/user/logout)
    // ============================================================

    /** @test */
    public function authenticated_user_can_logout_successfully()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/user/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User logout successfully',
            ]);

        $this->assertCount(0, $user->tokens);
    }

    /** @test */
    public function unauthenticated_user_cannot_logout()
    {
        $response = $this->postJson('/api/user/logout');

        $response->assertStatus(401);
    }
}

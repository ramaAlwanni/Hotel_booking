<?php

namespace Tests\Feature\Services\Api;

use App\Exceptions\General\EmailAlreadyVerifiedException;
use App\Exceptions\OTP\InvalidOTPException;
use App\Exceptions\OTP\OTPExpiredException;
use App\Models\User;
use App\Notifications\OTPNotification;
use App\Services\Api\OTPService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OTPServiceTest extends TestCase
{
    use RefreshDatabase;

    private OTPService $otpService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->otpService = app(OTPService::class);
    }

    // ============================================================
    //  sendOTP tests
    // ============================================================

    /** @test */
    public function send_otp_stores_hashed_otp_in_cache_and_sends_notification()
    {
        // Arrange
        Notification::fake();
        $user = User::factory()->create(['email_verified_at' => null]);

        // Act
        $this->otpService->sendOTP($user);

        // Assert
        $this->assertTrue(Cache::has("otp:user:{$user->id}"));

        Notification::assertSentTo(
            $user,
            OTPNotification::class,
            function ($notification) {
                return $notification->queue === 'default';
            }
        );
    }

    // ============================================================
    //  verifyOtp tests
    // ============================================================

    /** @test */
    public function verify_otp_succeeds_and_verifies_email_with_valid_otp()
    {
        // Arrange
        $user = User::factory()->create(['email_verified_at' => null]);
        $plainOtp = '123456';

        Cache::put("otp:user:{$user->id}", Hash::make($plainOtp), now()->addMinutes(5));

        $request = [
            'email' => $user->email,
            'otp' => $plainOtp,
        ];

        // Act
        $updatedUser = $this->otpService->verifyOtp($request);

        // Assert
        $this->assertNotNull($updatedUser->email_verified_at);
        $this->assertFalse(Cache::has("otp:user:{$user->id}"));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email_verified_at' => $updatedUser->email_verified_at,
        ]);
    }

    /** @test */
    public function verify_otp_throws_exception_if_email_is_already_verified()
    {
        // Arrange
        $user = User::factory()->create(['email_verified_at' => now()]);

        $request = [
            'email' => $user->email,
            'otp' => '123456',
        ];

        // Assert
        $this->expectException(EmailAlreadyVerifiedException::class);

        // Act
        $this->otpService->verifyOtp($request);
    }

    /** @test */
    public function verify_otp_throws_exception_when_otp_is_expired_or_not_in_cache()
    {
        // Arrange
        $user = User::factory()->create(['email_verified_at' => null]);

        $request = [
            'email' => $user->email,
            'otp' => '123456',
        ];

        // Assert
        $this->expectException(OTPExpiredException::class);

        // Act
        $this->otpService->verifyOtp($request);
    }

    /** @test */
    public function verify_otp_throws_exception_when_otp_is_invalid()
    {
        // Arrange
        $user = User::factory()->create(['email_verified_at' => null]);

        Cache::put("otp:user:{$user->id}", Hash::make('123456'), now()->addMinutes(5));

        $request = [
            'email' => $user->email,
            'otp' => '654321', // OTP خاطئ
        ];

        // Assert
        $this->expectException(InvalidOTPException::class);

        // Act
        $this->otpService->verifyOtp($request);
    }

    // ============================================================
    //  resendOtp tests
    // ============================================================

    /** @test */
    public function resend_otp_stores_new_hashed_otp_in_cache_and_sends_notification()
    {
        // Arrange
        Notification::fake();
        $user = User::factory()->create(['email_verified_at' => null]);

        $request = ['email' => $user->email];

        // Act
        $this->otpService->resendOtp($request);

        // Assert
        $this->assertTrue(Cache::has("otp:user:{$user->id}"));

        Notification::assertSentTo($user, OTPNotification::class);
    }

    /** @test */
    public function resend_otp_throws_exception_if_email_is_already_verified()
    {
        // Arrange
        $user = User::factory()->create(['email_verified_at' => now()]);

        $request = ['email' => $user->email];

        // Assert
        $this->expectException(EmailAlreadyVerifiedException::class);

        // Act
        $this->otpService->resendOtp($request);
    }
}

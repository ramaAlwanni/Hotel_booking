<?php

namespace Tests\Feature\Services\Api;

use App\Exceptions\Auth\InvalidCredentialsException;
use App\Exceptions\General\EmailNotVerifiedException;
use App\Models\User;
use App\Services\Api\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = app(AuthService::class);
    }

    // ============================================================
    //  register tests
    // ============================================================

    /** @test */
    public function register_creates_user_with_hashed_password()
    {
        // Arrange
        $data = [
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        // Act
        $user = $this->authService->register($data);

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    // ============================================================
    //  login tests
    // ============================================================

    /** @test */
    public function login_succeeds_with_valid_credentials_and_verified_email()
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $data = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        // Act 
        $result = $this->authService->login($data);

        // Assert 
        $this->assertNotNull($result['access_token']);
        $this->assertEquals('Bearer', $result['token_type']);
        $this->assertEquals('1 day', $result['access_token_expires_at']);
        $this->assertEquals('john@example.com', $result['user']->email);
    }

    /** @test */
    public function login_throws_exception_when_credentials_are_invalid()
    {
        // Arrange
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('correctPassword'),
            'email_verified_at' => now(),
        ]);

        $data = [
            'email' => 'john@example.com',
            'password' => 'wrongPassword'
        ];

        // Assert 
        $this->expectException(InvalidCredentialsException::class);

        // Act
        $this->authService->login($data);
    }

    /** @test */
    public function login_throws_exception_when_email_not_verified()
    {
        // Arrange
        User::factory()->create([
            'email' => 'unverified@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => null,
        ]);

        $data = [
            'email' => 'unverified@example.com',
            'password' => 'password123'
        ];

        // Assert 
        $this->expectException(EmailNotVerifiedException::class);

        // Act
        $this->authService->login($data);
    }

    /** @test */
    public function login_deletes_all_previous_tokens()
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // إنشاء توكن قديم للتأكد من حذفه
        $user->createToken('old_token');
        $this->assertCount(1, $user->tokens);

        $data = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        // Act
        $result = $this->authService->login($data);

        // Assert
        $this->assertNotNull($result['access_token']);
        $this->assertCount(1, $user->fresh()->tokens);
    }

    /** @test */
    public function login_returns_user_resource_with_correct_data()
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $data = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        // Act
        $result = $this->authService->login($data);

        // Assert
        $this->assertEquals('john@example.com', $result['user']->email);
        $this->assertEquals($user->id, $result['user']->id);
    }

    /** @test */
    public function login_sets_token_expiry_to_one_day()
    {
        // Arrange
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $data = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        // Act
        $result = $this->authService->login($data);

        // Assert
        $this->assertEquals('1 day', $result['access_token_expires_at']);
    }

    /** @test */
    public function login_returns_correct_token_type()
    {
        // Arrange
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $data = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        // Act
        $result = $this->authService->login($data);

        // Assert
        $this->assertEquals('Bearer', $result['token_type']);
    }
}

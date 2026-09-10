<?php

namespace Tests\Unit\Http\Requests\Api\Auth;

use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RegisterRequestTest extends TestCase
{
    use RefreshDatabase;

    private RegisterRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new RegisterRequest();
    }

    // ============================================================
    // Request Configuration Tests
    // ============================================================

    /** @test */
    public function request_authorization_returns_true()
    {
        $this->assertTrue($this->request->authorize());
    }

    /** @test */
    public function request_has_correct_validation_rules()
    {
        $rules = $this->request->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);

        $this->assertEquals('required|email|unique:users', $rules['email']);
        $this->assertEquals('required|min:6|string|confirmed', $rules['password']);
    }

    // ============================================================
    // Email Validation Tests - "required"
    // ============================================================

    /** @test */
    public function email_is_required()
    {
        $data = [
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertEquals(
            'The email field is required.',
            $validator->errors()->first('email')
        );
    }

    /** @test */
    public function email_fails_when_empty()
    {
        $data = [
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertEquals(
            'The email field is required.',
            $validator->errors()->first('email')
        );
    }

    // ============================================================
    // Email Validation Tests - "email" (format)
    // ============================================================

    /** @test */
    public function email_must_be_valid_format()
    {
        $invalidEmails = [
            'invalid-email',
            'test@',
            '@example.com',
            'test@.com',
            'test@example.',
            'test example.com',
            'test@example..com'
        ];

        foreach ($invalidEmails as $email) {
            $data = [
                'email' => $email,
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ];

            $validator = Validator::make($data, $this->request->rules());

            $this->assertTrue($validator->fails(), "Failed for email: {$email}");
            $this->assertArrayHasKey('email', $validator->errors()->toArray());
            $this->assertEquals(
                'The email field must be a valid email address.',
                $validator->errors()->first('email')
            );
        }
    }

    /** @test */
    public function email_passes_with_valid_formats()
    {
        $validEmails = [
            'test@example.com',
            'user.name@domain.com',
            'user+tag@domain.com',
            'user@sub.domain.com',
            'test@domain.co.uk'
        ];

        foreach ($validEmails as $email) {
            $data = [
                'email' => $email,
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ];

            $validator = Validator::make($data, $this->request->rules());
            $this->assertFalse($validator->fails(), "Failed for email: {$email}");
        }
    }

    // ============================================================
    // Email Validation Tests - "unique:users"
    // ============================================================

    /** @test */
    public function email_must_be_unique_in_users_table()
    {
        // Arrange: Create an existing user
        User::factory()->create([
            'email' => 'existing@example.com'
        ]);

        $data = [
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertEquals(
            'The email has already been taken.',
            $validator->errors()->first('email')
        );
    }

    /** @test */
    public function email_passes_when_unique()
    {
        $data = [
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->fails());
        $this->assertEmpty($validator->errors()->all());
    }

    // ============================================================
    // Password Validation Tests - "required"
    // ============================================================

    /** @test */
    public function password_is_required()
    {
        $data = [
            'email' => 'john@example.com'
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
        $this->assertEquals(
            'The password field is required.',
            $validator->errors()->first('password')
        );
    }

    /** @test */
    public function password_fails_when_empty()
    {
        $data = [
            'email' => 'john@example.com',
            'password' => ''
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
        $this->assertEquals(
            'The password field is required.',
            $validator->errors()->first('password')
        );
    }

    // ============================================================
    // Password Validation Tests - "min:6"
    // ============================================================

    /** @test */
    public function password_must_be_at_least_6_characters()
    {
        $shortPasswords = ['1', '12', '123', '1234', '12345'];

        foreach ($shortPasswords as $password) {
            $data = [
                'email' => 'john@example.com',
                'password' => $password,
                'password_confirmation' => $password
            ];

            $validator = Validator::make($data, $this->request->rules());

            $this->assertTrue($validator->fails(), "Failed for password: {$password}");
            $this->assertArrayHasKey('password', $validator->errors()->toArray());
            $this->assertEquals(
                'The password field must be at least 6 characters.',
                $validator->errors()->first('password')
            );
        }
    }

    /** @test */
    public function password_passes_with_minimum_6_characters()
    {
        $data = [
            'email' => 'john@example.com',
            'password' => '123456',
            'password_confirmation' => '123456'
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->fails());
        $this->assertEmpty($validator->errors()->all());
    }

    /** @test */
    public function password_passes_with_more_than_6_characters()
    {
        $data = [
            'email' => 'john@example.com',
            'password' => 'securePassword123',
            'password_confirmation' => 'securePassword123'
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->fails());
        $this->assertEmpty($validator->errors()->all());
    }

    // ============================================================
    // Password Validation Tests - "string"
    // ============================================================

    /** @test */
    public function password_must_be_a_string()
    {
        $data = [
            'email' => 'john@example.com',
            'password' => 123456, // Integer, not string
            'password_confirmation' => 123456
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
        $this->assertEquals(
            'The password field must be a string.',
            $validator->errors()->first('password')
        );
    }

    /** @test */
    public function password_passes_when_string()
    {
        $data = [
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->fails());
        $this->assertEmpty($validator->errors()->all());
    }

    // ============================================================
    // Password Validation Tests - "confirmed"
    // ============================================================

    /** @test */
    public function password_must_have_confirmation_match()
    {
        $data = [
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentPassword'
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
        $this->assertEquals(
            'The password field confirmation does not match.',
            $validator->errors()->first('password')
        );
    }

    /** @test */
    public function password_fails_when_confirmation_is_missing()
    {
        $data = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
        $this->assertEquals(
            'The password field confirmation does not match.',
            $validator->errors()->first('password')
        );
    }

    /** @test */
    public function password_passes_when_confirmation_matches()
    {
        $data = [
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->fails());
        $this->assertEmpty($validator->errors()->all());
    }

    // ============================================================
    // Combined Validation Tests (Multiple Failures)
    // ============================================================

    /** @test */
    public function validation_returns_multiple_errors_when_multiple_fields_are_invalid()
    {
        $data = [
            'email' => 'invalid-email',
            'password' => '12345', // Too short
            'password_confirmation' => '12345'
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());

        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
        $this->assertCount(2, $errors);
    }

    /** @test */
    public function validation_returns_multiple_errors_when_all_fields_are_missing()
    {
        $data = [];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());

        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
        $this->assertCount(2, $errors);
    }

    /** @test */
    public function validation_passes_with_all_valid_data()
    {
        $data = [
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->fails());
        $this->assertEmpty($validator->errors()->all());
        $this->assertTrue($validator->passes());
    }

    // ============================================================
    // Exception Handling Tests
    // ============================================================

    /** @test */
    public function validator_throws_validation_exception_when_validation_fails()
    {
        $this->expectException(ValidationException::class);

        $data = [
            'email' => 'invalid-email',
            'password' => '12345'
        ];

        $validator = Validator::make($data, $this->request->rules());
        $validator->validate();
    }

    /** @test */
    public function validation_exception_contains_correct_error_messages()
    {
        try {
            $data = [
                'email' => 'invalid-email',
                'password' => '12345'
            ];

            $validator = Validator::make($data, $this->request->rules());
            $validator->validate();
        } catch (ValidationException $e) {
            $errors = $e->errors();

            $this->assertArrayHasKey('email', $errors);
            $this->assertArrayHasKey('password', $errors);
            $this->assertEquals(
                'The email field must be a valid email address.',
                $errors['email'][0]
            );
            $this->assertEquals(
                'The password field must be at least 6 characters.',
                $errors['password'][0]
            );
        }
    }

    /** @test */
    public function validation_exception_has_correct_status_code()
    {
        try {
            $data = [
                'email' => 'invalid-email',
                'password' => '12345'
            ];

            $validator = Validator::make($data, $this->request->rules());
            $validator->validate();
        } catch (ValidationException $e) {
            $this->assertEquals(422, $e->status);
        }
    }

    /** @test */
    public function validation_exception_has_correct_format()
    {
        try {
            $data = [
                'email' => 'invalid-email',
                'password' => '12345'
            ];

            $validator = Validator::make($data, $this->request->rules());
            $validator->validate();
        } catch (ValidationException $e) {
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertIsArray($e->errors());
            $this->assertNotEmpty($e->errors());
        }
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'テスト太郎',
            'email' => 'example@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    /** @test */
    public function test_name_is_required()
    {
        $response = $this->from('/register')->post('/register', $this->validPayload([
            'name' => '',
        ]));

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['name']);
        $this->assertSame(
            'お名前を入力してください',
            session('errors')->first('name')
        );
    }

    public function test_email_is_required()
    {
        $response = $this->from('/register')->post('/register', $this->validPayload([
            'email' => '',
        ]));

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['email']);
        $this->assertSame(
            'メールアドレスを入力してください',
            session('errors')->first('email')
        );
    }

    public function test_password_must_be_at_latest_8_characters()
    {
        $response = $this->from('/register')->post('/register', $this->validPayload([
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]));

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['password']);
        $this->assertSame(
            'パスワードは8文字以上で入力してください',
            session('errors')->first('password')
        );
    }

    public function test_password_confirmation_must_match()
    {
        $response = $this->from('/register')->post('/register', $this->validPayload([
            'password' => 'password123',
            'password_confirmation' => 'password987',
        ]));

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['password']);
        $this->assertSame(
            'パスワードと一致しません',
            session('errors')->first('password')
        );
    }

    public function test_password_is_required()
    {
        $response = $this->from('/register')->post('/register', $this->validPayload([
            'password' => '',
            'password_confirmation' => '',
        ]));

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['password']);
        $this->assertSame(
            'パスワードを入力してください',
            session('errors')->first('password')
        );
    }

    public function test_user_can_register_and_user_is_saved_in_database()
    {
        $payload = $this->validPayload([
            'email' => 'hash_' . uniqid() . '@test.com',
        ]);

        $this->post('/register', $payload)->assertStatus(302);

        $user = \App\Models\Staff::where('email', $payload['email'])->first();
        $this->assertNotNull($user);
        $this->assertTrue(\Hash::check($payload['password'], $user->password));
    }
}

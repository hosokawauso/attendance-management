<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;



class LoginTest extends TestCase
{
    use refreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    private function validStaff(array $overrides = []): Staff
    {
        return Staff::factory()->create(array_merge([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ], $overrides));
    }

    public function test_email_is_required_on_login()
    {
        $this->validStaff();

        $response = $this->from('/login')->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertSame(
            'メールアドレスを入力してください',
            session('errors')->first('email')
        );
    }

    public function test_password_is_required_on_login()
    {
        $this->validStaff();

        $response = $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['password']);
        $this->assertSame(
            'パスワードを入力してください',
            session('errors')->first('password')
        );
    }

    public function test_invalid_credentials_show_validation_message()
    {
        $this->validStaff([
            'email' => 'real@example.com',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertSame(
            'ログイン情報が登録されていません。',
            session('errors')->first('email')
        );
    }
}

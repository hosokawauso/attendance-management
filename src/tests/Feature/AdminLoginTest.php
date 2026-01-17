<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    private function adminStaff(array $overrides = []): Staff
    {
        return Staff::factory()->admin()->create(array_merge([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ], $overrides));
    }

    public function test_admin_email_is_required_on_login()
    {
        $this->adminStaff();

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertSame(
            'メールアドレスを入力してください',
            session('errors')->first('email')
        );
    }

    public function test_admin__password_is_required_on_login()
    {
        $this->adminStaff();

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['password']);
        $this->assertSame(
            'パスワードを入力してください',
            session('errors')->first('password')
        );
    }

    public function test_admin_invalid_credentials_show_validation_message()
    {
        $this->adminStaff([
            'email' => 'adminreal@example.com',
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'adminwrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertSame(
            'ログイン情報が登録されていません。',
            session('errors')->first('email')
        );
    }
}

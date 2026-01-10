<?php

namespace Database\Factories;

use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class StaffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password123'),
            'is_admin' => false,
        ];
    }

    public function admin(): self
    {
        return $this->state(function () {
            return [
                'is_admin' => true,
                'email' => 'admin@example.com',
                'name' => '管理スタッフ',
            ];
        });
    }
}

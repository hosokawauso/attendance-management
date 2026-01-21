<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceCorrectRequestRest;

class AttendanceCorrectRequestRestFactory extends Factory
{
    protected $model = AttendanceCorrectRequestRest::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_correct_request_id' => null,
            'requested_start_rest' => '12:00:00',
            'requested_end_rest' => '12:45:00',
            'sort_order' => 1,
        ];
    }
}

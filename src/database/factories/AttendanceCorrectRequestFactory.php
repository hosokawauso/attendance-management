<?php

namespace Database\Factories;

use App\Models\AttendanceCorrectRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceCorrectRequestFactory extends Factory
{
    protected $model = AttendanceCorrectRequest::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $start = Carbon::createFromTime(9, 0)->addMinutes($this->faker->numberBetween(-60, 60));
        $end   = (clone $start)->addHours(8)->addMinutes($this->faker->numberBetween(-60, 90));


        return [
            'staff_id' => null,
            'stamp_id' => null,
            'status' => AttendanceCorrectRequest::STATUS_PENDING,
            'requested_start_work' => $start->format('H:i'),
            'requested_end_work' => $end->format('H:i'),
            'requested_remarks' => $this->faker->realText(20),
            /* 'admin_comment' => $this->faker->realText(20), */
        ];
    }

    public function pending()
    {
        return $this->state(fn() => [
            'status' => AttendanceCorrectRequest::STATUS_PENDING,
            'approved_at' => null,
        ]);
    }

    public function approved()
    {
        return $this->state(fn() => [
            'status' => AttendanceCorrectRequest::STATUS_APPROVED,
            'approved_at' => now(),
        ]);
    }

}

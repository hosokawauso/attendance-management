<?php

namespace Database\Factories;

use App\Models\Stamp;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class StampFactory extends Factory
{
    protected $model = Stamp::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $start = Carbon::createFromTime(9, 0)->addMinutes($this->faker->numberBetween(-30, 30));
        $end = $start->copy()->addHours(8)->addMinutes($this->faker->numberBetween(-60, 90));

        return [
            'staff_id' => null,
            'stamp_date' => now()->toDateString(),
            'start_work' => $start->format('H:i'),
            'end_work'   => $end->format('H:i'),
        ];
    }
}

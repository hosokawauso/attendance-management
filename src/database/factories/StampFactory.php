<?php

namespace Database\Factories;

use App\Models\Stamp;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

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

    public function workMinutes(): int
    {
        if (!$this->start_work || !$this->end_work) return 0;

        $start = Carbon::parse($this->stamp_date->toDateString().' '.$this->start_work);
        $end   = Carbon::parse($this->stamp_date->toDateString().' '.$this->end_work);

        if ($end->lt($start)) $end->addDay();

        return $start->diffInMinutes($end);
    }
}

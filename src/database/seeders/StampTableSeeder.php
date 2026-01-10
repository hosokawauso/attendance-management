<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Staff;
use App\Models\Stamp;

class StampTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $staffs = Staff::all();

        $start = Carbon::create(2025, 10, 1);
        $end = Carbon::create(2025, 12, 31);

        $period = CarbonPeriod::create($start, $end);

        foreach ($staffs as $staff) {
            foreach ($period as $date) {
                Stamp::factory()->create([
                    'staff_id' => $staff->id,
                    'stamp_date' => $date->toDateString(),
                ]);
            }
        }
    }
}

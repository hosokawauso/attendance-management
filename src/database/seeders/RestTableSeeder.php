<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;
use App\Models\Stamp;
use App\Models\Rest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class RestTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $staffs = Staff::all();

        $start = Carbon::create(2026, 1, 1);
        $end   = Carbon::create(2026, 1, 31);

        $period = CarbonPeriod::create($start, $end);

        foreach ($staffs as $staff) {
            foreach ($period as $date) {
                $stamp = Stamp::factory()->create([
                    'staff_id'   => $staff->id,
                    'stamp_date' => $date->toDateString(),
                ]);

                Rest::factory()->create([
                    'stamp_id' => $stamp->id,
                ]);
            }
        }
    }
}

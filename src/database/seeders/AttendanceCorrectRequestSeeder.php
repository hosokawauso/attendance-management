<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Staff;
use App\Models\Stamp;
use App\Models\AttendanceCorrectRequest;

class AttendanceCorrectRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $staffs = Staff::all();

        foreach ($staffs as $staff) {
            $stampIds = Stamp::where('staff_id', $staff->id)
                ->inRandomOrder()
                ->limit(10)
                ->pluck('id')
                ->values();

            $approved = $stampIds->slice(0, 5);
            $pending = $stampIds->slice(5, 5);

            foreach ($approved as $stampId) {
                AttendanceCorrectRequest::factory()
                    ->approved()
                    ->create([
                        'staff_id' => $staff->id,
                        'stamp_id' => $stampId,
                    ]);
                }

            foreach ($pending as $stampId) {
                AttendanceCorrectRequest::factory()
                    ->pending()
                    ->create([
                        'staff_id' => $staff->id,
                        'stamp_id' => $stampId,
                    ]);
            }
        }
    }
}

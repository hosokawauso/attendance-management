<?php

namespace Tests\Feature;

use Tests\TestCase;
use Carbon\Carbon;
use App\Models\Staff;
use App\Models\Stamp;
use App\Models\Rest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    private function loginStaff(): Staff
    {
        $staff = Staff::factory()->create();
        $this->actingAs($staff);
        return $staff;
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_status_is_idle_when_staff_is_off_duty()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 11, 15, 46, 0, 'Asia/Tokyo'));

        $this->loginStaff();

        $response = $this->get(route('attendance'));

        $response->assertStatus(200);
        $response->assertSee('勤務外');

        Carbon::setTestNow();
    }

    public function test_status_is_working_when_staff_is_clocked_in()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 11, 15, 46, 0, 'Asia/Tokyo'));

        $staff = $this->loginStaff();

        Stamp::factory()->create([
            'staff_id' => $staff->id,
            'stamp_date' => now('Asia/Tokyo')->toDateString(),
            'start_work' => now('Asia/Tokyo')->copy()->setTime(9, 0, 0),
            'end_work' => null,
        ]);

        $response = $this->get(route('attendance'));

        $response->assertStatus(200);
        $response->assertSee('出勤中');

        Carbon::setTestNow();
    }

    public function test_status_is_resting_when_user_is_on_break()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 11, 15, 46, 0, 'Asia/Tokyo'));

        $staff = $this->loginStaff();

        $stamp = Stamp::factory()->create([
            'staff_id' => $staff->id,
            'stamp_date' => now('Asia/Tokyo')->toDateString(),
            'start_work' => now('Asia/Tokyo')->copy()->setTime(9, 0, 0),
            'end_work' => null,
        ]);

        Rest::factory()->create([
            'stamp_id' => $stamp->id,
            'start_rest' => now('Asia/Tokyo')->copy()->setTime(12, 0, 0),
            'end_rest' => null,
        ]);

        $response = $this->get(route('attendance'));

        $response->assertStatus(200);
        $response->assertSee('休憩中');

        Carbon::setTestNow();
    }

        public function test_status_is_finished_when_user_is_clocked_out()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 11, 15, 46, 0, 'Asia/Tokyo'));

        $staff = $this->loginStaff();

        $stamp = Stamp::factory()->create([
            'staff_id' => $staff->id,
            'stamp_date' => now('Asia/Tokyo')->toDateString(),
            'start_work' => now('Asia/Tokyo')->copy()->setTime(9, 0, 0),
            'end_work' => now('Asia/Tokyo')->copy()->setTime(18, 0, 0),
        ]);

        $response = $this->get(route('attendance'));

        $response->assertStatus(200);
        $response->assertSee('退勤済');

        Carbon::setTestNow();
    }

}

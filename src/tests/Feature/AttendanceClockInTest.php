<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\Staff;
use App\Models\Stamp;
use App\Models\Rest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    private function loginStaff(): Staff
    {
        $staff = Staff::factory()->create();
        $this->actingAs($staff);
        return $staff;
    }

    private function freezeNow(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 11, 9, 0, 0, 'Asia/Tokyo'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // 固定解除
        parent::tearDown();
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_staff_can_clock_in_from_idle_state()
    {
        $this->freezeNow();

        $staff = $this->loginStaff();

        $response = $this->get(route('attendance'));
        $response->assertStatus(200);
        $response->assertSee('出勤');

        $response = $this->post(route('attendance'), [
            'action' => 'clock-in',
        ]);

        $response = $this->get(route('attendance'));
        $response->assertSee('出勤中');
    }

    public function test_staff_cannot_clock_in_twice_in_one_day()
    {
        $this->freezeNow();

        $staff = $this->loginStaff();

        Stamp::factory()->create([
            'staff_id' => $staff->id,
            'stamp_date' => now('Asia/Tokyo')->toDateString(),
            'start_work' => now('Asia/Tokyo')->copy()->setTime(9, 0),
            'end_work'   => now('Asia/Tokyo')->copy()->setTime(18, 0),
        ]);

        $response = $this->get(route('attendance'));

        $response->assertStatus(200);
        $response->assertDontSee('出勤');
    }

    public function test_clock_in_time_is_shown_on_attendance_list()
    {
        $this->freezeNow();

        $staff = $this->loginStaff();

        $this->post(route('attendance'), [
            'action' => 'clock-in',
        ]);

        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('09:00');
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\Staff;
use App\Models\Stamp;
use App\Models\Rest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

        private function loginStaff(): Staff
    {
        $staff = Staff::factory()->create();
        $this->actingAs($staff);
        return $staff;
    }

    private function freezeNow(int $hour, int $min): void
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 11, $hour, $min, 0, 'Asia/Tokyo'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function makeWorking(Staff $staff): Stamp
    {
        return Stamp::factory()->create([
            'staff_id'   => $staff->id,
            'stamp_date' => now('Asia/Tokyo')->toDateString(),
            'start_work' => now('Asia/Tokyo')->copy()->setTime(9, 0),
            'end_work'   => null,
        ]);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_staff_can_clock_out_from_working()
    {
        $this->freezeNow(9, 0);

        $staff = $this->loginStaff();
        $stamp = $this->makeWorking($staff);

        $response = $this->get(route('attendance'));
        $response->assertStatus(200);
        $response->assertSee('退勤');

        $this->freezeNow(18, 0);
        $this->post(route('attendance'), [
            'action' => 'clock-out',
        ])->assertRedirect();

        $response = $this->get(route('attendance'));
        $response->assertSee('退勤済');
    }

    public function test_clock_out_time_is_shown_on_attendance_list()
    {
        $this->freezeNow(9, 0);

        $staff = $this->loginStaff();

        $this->post(route('attendance'), [
            'action' => 'clock-in',
        ])->assertRedirect();

        $this->freezeNow(18, 0);
        $this->post(route('attendance'), [
            'action' => 'clock-out',
        ])->assertRedirect();

        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);
        $response->assertSee('18:00');
    }
}

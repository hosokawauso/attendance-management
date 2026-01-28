<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\Staff;
use App\Models\Stamp;
use App\Models\Rest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceRestTest extends TestCase
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
        \Illuminate\Support\Carbon::setTestNow();
        \Carbon\Carbon::setTestNow();
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
    public function test_staff_can_start_rest_from_working()
    {
        $this->freezeNow(12, 0);

        $staff = $this->loginStaff();
        $this->makeWorking($staff);

        $response = $this->get(route('attendance'));
        $response->assertStatus(200);
        $response->assertSee('休憩入');

        $this->post(route('attendance'), [
            'action' => 'rest-start',
        ]);

        $response = $this->get('attendance');
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');
    }

    public function test_staff_can_start_rest_multiple_times_in_a_day()
    {
        $this->freezeNow(9, 0);

        $staff = $this->loginStaff();
        $this->makeWorking($staff);

        // 1回目 休憩入 → 休憩戻
        $this->freezeNow(12, 0);
        $this->post(route('attendance'), [
            'action' => 'rest-start'
        ]);

        $this->freezeNow(12, 30);
        $this->post(route('attendance'), [
            'action' => 'rest-end'
        ]);

        // 戻ったらまた「休憩入」が出る
        $response = $this->get(route('attendance'));
        $response->assertSee('出勤中');
        $response->assertSee('休憩入');

        // 2回目も開始できる（念のため）
        $this->freezeNow(15, 0);
        $this->post(route('attendance'), [
            'action' => 'rest-start'
        ]);

        $response = $this->get(route('attendance'));
        $response->assertSee('休憩中');
    }

    public function test_staff_can_end_rest_and_return_to_working()
    {
        $this->freezeNow(9, 0);

        $staff = $this->loginStaff();
        $this->makeWorking($staff);

        $this->freezeNow(12, 0);
        $this->post(route('attendance'), [
            'action' => 'rest-start'
        ]);

        $response = $this->get(route('attendance'));
        $response->assertSee('休憩戻');

        $this->freezeNow(12, 30);
        $this->post(route('attendance'), [
            'action' => 'rest-end'
        ]);

        $response = $this->get(route('attendance'));
        $response->assertSee('出勤中');
        $response->assertSee('休憩入');
    }

    public function test_staff_can_end_rest_multiple_times_in_a_day()
    {
        $this->freezeNow(9, 0);

        $staff = $this->loginStaff();
        $this->makeWorking($staff);

        // 1回目
        $this->freezeNow(12, 0);
        $this->post(route('attendance'), [
            'action' => 'rest-start'
        ]);
        $this->freezeNow(12, 10);
        $this->post(route('attendance'), [
            'action' => 'rest-end'
        ]);

        // 2回目 入る
        $this->freezeNow(15, 0);
        $this->post(route('attendance'), [
            'action' => 'rest-start'
        ]);

        // 休憩中なら「休憩戻」が出る
        $response = $this->get(route('attendance'));
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');
    }

    public function test_rest_time_is_shown_on_attendance_list()
    {
        $this->freezeNow(9, 0);

        $staff = $this->loginStaff();
        $this->makeWorking($staff);

        // 休憩 12:00 - 12:30
        $this->freezeNow(12, 0);
        $this->post(route('attendance'), [
            'action' => 'rest-start'
        ]);
        $this->freezeNow(12, 30);
        $this->post(route('attendance'), [
            'action' => 'rest-end'
        ]);

        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);

        $response->assertSee('0:30');
    }

}

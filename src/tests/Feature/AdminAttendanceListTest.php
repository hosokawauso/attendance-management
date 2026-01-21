<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

use App\Models\Staff;
use App\Models\Stamp;
use App\Models\Rest;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    private function freezeNow(int $year, int $month, int $day, int $hour = 9, int $min = 0): void
    {
        Carbon::setTestNow(Carbon::create($year, $month, $day, $hour, $min, 0, 'Asia/Tokyo'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function loginAdmin(): Staff
    {
        $admin = Staff::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin);
        return $admin;
    }

    private function makeStaffWithStamp(string $name, string $date, string $start = '09:00', string $end = '18:00'): Staff
    {
        $staff = Staff::factory()->create([
            'name' => $name,
        ]);

        $testDate = Carbon::parse($date, 'Asia/Tokyo');

        Stamp::factory()->create([
            'staff_id' => $staff->id,
            'stamp_date' => $testDate->toDateString(),
            'start_work' => $testDate->copy()->setTime((int)substr($start,0,2), (int)substr($start,3,2)),
            'end_work'   => $testDate->copy()->setTime((int)substr($end,0,2), (int)substr($end,3,2)),
        ]);

        return $staff;
    }

    private function openAdminAttendanceList(?string $date = null)
    {
        $params = [];

        if ($date !== null) {
            $params['date'] = $date;
        }

        return $this->get(route('admin.attendance.list', $params));
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_can_view_all_staff_attendance_for_the_day()
    {
        $this->freezeNow(2026, 1, 11);

        $this->loginAdmin();

        // 当日の勤怠
        $this->makeStaffWithStamp('スタッフA','2026-01-11', '09:00', '18:00');
        $this->makeStaffWithStamp('スタッフB', '2026-01-11', '10:00', '19:00');

        // 別日の勤怠
        $this->makeStaffWithStamp('スタッフC', '2026-01-10', '09:00', '18:00');

        $response = $this->openAdminAttendanceList('2026-01-11');
        $response->assertStatus(200);

        // 当日の全ユーザー
        $response->assertSee('スタッフA');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('スタッフB');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        // 別日のユーザー
        $response->assertDontSee('スタッフC');
    }

    public function test_today_date_is_shown_when_opening_admin_attendance_list()
    {
        $this->freezeNow(2026, 1, 11);

        $this->loginAdmin();

        $response = $this->openAdminAttendanceList();
        $response->assertStatus(200);

        $response->assertSee('2026/01/11');
    }

    public function test_click_prev_day_shows_previous_day_attendance()
    {
        $this->freezeNow(2026, 1, 11);

        $this->loginAdmin();

        $this->makeStaffWithStamp('スタッフA', '2026-01-10', '09:00', '18:00');
        $this->makeStaffWithStamp('スタッフB', '2026-01-11', '10:00', '19:00');

        $today = $this->openAdminAttendanceList('2026-01-11');
        $today->assertStatus(200);
        $today->assertSee('スタッフB');
        $today->assertDontSee('スタッフA');

        $prev = $this->openAdminAttendanceList('2026-01-10');
        $prev->assertStatus(200);
        $prev->assertSee('スタッフA');
        $prev->assertDontSee('スタッフB');

        $prev->assertSee('2026/01/10');
    }

    public function test_click_next_day_shows_next_day_attendance()
    {
        $this->freezeNow(2026, 1, 11);

        $this->loginAdmin();

        $this->makeStaffWithStamp('スタッフB', '2026-01-11', '10:00', '19:00');
        $this->makeStaffWithStamp('スタッフC', '2026-01-12', '09:00', '18:00');

        // まず当日
        $today = $this->openAdminAttendanceList('2026-01-11');
        $today->assertStatus(200);
        $today->assertSee('スタッフB');
        $today->assertDontSee('スタッフC');

        // 翌日
        $next = $this->openAdminAttendanceList('2026-01-12');
        $next->assertStatus(200);
        $next->assertSee('スタッフC');
        $next->assertDontSee('スタッフB');

        $next->assertSee('2026/01/12');
    }

}

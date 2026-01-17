<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\Staff;
use App\Models\Stamp;
use App\Models\Rest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    private function loginStaff(): Staff
    {
        $staff = Staff::factory()->create();
        $this->actingAs($staff);
        return $staff;
    }

    private function freezeNow(int $year, int $month, int $day, int $hour = 9, int $min = 0): void
    {
        Carbon::setTestNow(Carbon::create($year, $month, $day, $hour, $min, 0, 'Asia/Tokyo'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function createStamp(Staff $staff): Stamp
    {
        return Stamp::factory()->create([
            'staff_id'   => $staff->id,
            'stamp_date' => '2026-01-11',
            'start_work' => Carbon::create(2026, 1, 11, 9, 0, 0, 'Asia/Tokyo'),
            'end_work'   => Carbon::create(2026, 1, 11, 18, 0, 0, 'Asia/Tokyo'),
        ]);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_list_shows_only_my_attendance_records()
    {
        $this->freezeNow(2026, 1, 11, 9, 0);

        $me = $this->loginStaff();
        $other = Staff::factory()->create();

        $myStamp = $this->createStamp($me, '2026-01-11', '09:00', '18:00');

        $this->createStamp($other, '2026-01-11', '10:00', '19:00');

        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);

        $response->assertSee('01/11');
        $response->assertSee('09:00');

        $response->assertDontSee('10:00');
    }

    public function test_list_shows_current_month()
    {
        $this->freezeNow(2026, 1, 11, 9, 0);

        $staff = $this->loginStaff();

        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);

        $response->assertSee('2026/01');
    }

    public function test_click_prev_month_shows_previous_month()
    {
        $this->freezeNow(2026, 1, 11, 9, 0);

        $staff = $this->loginStaff();

        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);

        $response->assertSee('month=2025-12');

        $prev = $this->get(route('attendance.list', ['month' => '2025-12']));
        $prev->assertStatus(200);
        $prev->assertSee('2025/12');
    }

    public function test_click_next_month_shows_next_month()
    {
        $this->freezeNow(2026, 1, 11, 9, 0);

        $staff = $this->loginStaff();

        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);

        $response->assertSee('month=2026-02');

        $next = $this->get(route('attendance.list', ['month' => '2026-02']));
        $next->assertStatus(200);
        $next->assertSee('2026/02');
    }

    public function test_click_detail_moves_to_daily_detail_page()
    {
        $this->freezeNow(2026, 1, 11, 9, 0);

        $staff = $this->loginStaff();

        $stamp = $this->createStamp($staff);

        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);

        $response->assertSee("/attendance/detail/{$stamp->id}");

        $detail = $this->get(route('attendance.detail',  ['stamp' => $stamp->id]));
        $detail->assertStatus(200);

        $detail->assertSee('09:00');
        $detail->assertSee('18:00');
    }

}

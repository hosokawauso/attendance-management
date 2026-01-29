<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use App\Models\Staff;
use App\Models\Stamp;
use App\Models\Rest;

class StaffInformationAcquisitionTest extends TestCase
{
    use RefreshDatabase;

        private function createAdmin(): Staff
    {
        return Staff::factory()->create([
            'is_admin' => true,
        ]);
    }

    private function createStaff(array $override = []): Staff
    {
        return Staff::factory()->create(array_merge([
            'is_admin' => false,
        ], $override));
    }

    private function makeStamp(Staff $staff, string $date = '2026-01-11', string $start = '09:00', string $end = '18:00'): Stamp
    {
        $testDate = Carbon::parse($date, 'Asia/Tokyo');

        return Stamp::factory()->create([
            'staff_id'   => $staff->id,
            'stamp_date' => $testDate->toDateString(),
            'start_work' => $testDate->copy()->setTime((int)substr($start, 0, 2), (int)substr($start, 3, 2)),
            'end_work'   => $testDate->copy()->setTime((int)substr($end, 0, 2), (int)substr($end, 3, 2)),
        ]);
    }

    private function makeRest(Stamp $stamp): void
    {
        Rest::factory()->create([
            'stamp_id'    => $stamp->id,
            'start_rest'  => '12:00:00',
            'end_rest'    => '13:00:00',
        ]);
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_can_view_all_staff_names_and_emails()
    {
        $admin = $this->createAdmin();
        $staffs = Staff::factory()->count(3)->create(['is_admin' => false]);

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.list'));

        $response->assertStatus(200);

        foreach ($staffs as $staff) {
            $response->assertSee($staff->name);
            $response->assertSee($staff->email);
        }
    }

    public function test_admin_can_view_selected_staff_attendance_list()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();

        $stamp = $this->makeStamp($staff, '2026-01-11', '09:00', '18:00');

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$staff->id}?month=2026-01");

        $response->assertStatus(200);
        $response->assertSee('2026/01');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_admin_can_view_previous_month_attendance()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();

        $this->makeStamp($staff, '2026-01-11', '09:00', '18:00');

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$staff->id}?month=2025-12");

        $response->assertStatus(200);
        $response->assertSee('2025/12');
    }

    public function test_admin_can_view_next_month_attendance()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();

        $stamp = $this->makeStamp($staff, '2026-01-11', '09:00', '18:00');

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$staff->id}?month=2026-02");

        $response->assertStatus(200);
        $response->assertSee('2026/02');
    }

    public function test_admin_can_view_attendance_detail()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();

        $stamp = $this->makeStamp($staff, '2026-01-11', '09:00', '18:00');
        $this->makeRest($stamp, '12:00', '13:00');

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.detail', $stamp->id));

        $response->assertStatus(200);
        $response->assertSee('2026');
        $response->assertSee('1月11日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        // 休憩表示もしているなら
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}

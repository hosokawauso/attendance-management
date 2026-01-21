<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

use App\Models\Staff;
use App\Models\Stamp;
use App\Models\AttendanceCorrectRequest;
use App\Models\AttendanceCorrectRequestRest;

class AdminAttendanceCorrectionTest extends TestCase
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

    private function makeStamp(
        Staff $staff,
        string $date = '2026-01-11',
        string $start = '09:00',
        string $end = '18:00'): Stamp
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
            'start_rest'  => '12:00',
            'end_rest'    => '13:00',
        ]);
    }

    private function makeCorrectionRequest(
        Staff $staff,
        ?Stamp $stamp = null,
        int $status = 1,
        ?string $requestedStart = '08:30',
        ?string $requestedEnd = '17:30',
        string $requestedRemarks = 'test',
    ): AttendanceCorrectRequest
    {
        $stamp ??= $this->makeStamp($staff, '2026-01-11');

        return AttendanceCorrectRequest::factory()->create([
            'staff_id' => $staff->id,
            'stamp_id' => $stamp->id,
            'status' => $status,
            'requested_start_work' => $requestedStart,
            'requested_end_work' => $requestedEnd,
            'requested_remarks' => $requestedRemarks,
        ]);
    }

    private function makeCorrectionRequestRests(AttendanceCorrectRequest $apply): void
    {
        AttendanceCorrectRequestRest::factory()->create([
            'attendance_correct_request_id' => $apply->id,
            'requested_start_rest' => '12:00',
            'requested_end_rest' => '12:45',
            'sort_order' => 1,
        ]);

        AttendanceCorrectRequestRest::factory()->create([
            'attendance_correct_request_id' => $apply->id,
            'requested_start_rest' => '15:00',
            'requested_end_rest' => '15:15',
            'sort_order' => 2,
        ]);
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_pending_requests_are_listed_for_admin()
    {
        $admin = $this->createAdmin();

        $staffA = $this->createStaff(['name' => 'PENDING太郎']);
        $staffB = $this->createStaff(['name' => 'APPROVED花子']);

        // 承認待ち2件 承認済み1件
        $pending = $this->makeCorrectionRequest($staffA, status: 1, requestedRemarks: 'PENDING-UNIQUE');
        $approved = $this->makeCorrectionRequest($staffA, status: 2, requestedRemarks: 'APPROVED-UNIQUE');

        $response = $this->actingAs($admin)
            ->get(route('stamp_correction_request.list'));

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('PENDING-UNIQUE');
    }

    public function test_approved_requests_are_listed_for_admin()
    {
        $admin = $this->createAdmin();

        $staffA = $this->createStaff(['name' => 'PENDING太郎']);
        $staffB = $this->createStaff(['name' => 'APPROVED花子']);

        $pending = $this->makeCorrectionRequest($staffA, status: 1, requestedRemarks: 'PENDING-UNIQUE');
        $approved = $this->makeCorrectionRequest($staffA, status: 2, requestedRemarks: 'APPROVED-UNIQUE');

        $response = $this->actingAs($admin)
            ->get(route('stamp_correction_request.list'));

        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee('APPROVED-UNIQUE');

    }

    public function test_admin_can_view_correction_request_detail()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();

        $stamp = $this->makeStamp($staff, '2026-01-11', '09:00', '18:00');

        $apply = $this->makeCorrectionRequest(
            $staff,
            stamp: $stamp,
            status: 1,
            requestedStart: '08:30',
            requestedEnd: '17:30',
            requestedRemarks: '電車遅延のため'
        );

        $this->makeCorrectionRequestRests($apply);

        $response = $this->actingAs($admin)
            ->get("/stamp_correction_request/approve/{$apply->id}");

        $response->assertStatus(200);

        $response->assertSee('08:30');
        $response->assertSee('17:30');
        $response->assertSee('電車遅延のため');

        $response->assertSee('12:00');
        $response->assertSee('12:45');
    }

    public function test_admin_can_approve_request_and_stamp_is_updated()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();

        $stamp = $this->makeStamp($staff, '2026-01-11', '09:00', '18:00');

        $apply = $this->makeCorrectionRequest(
            $staff,
            stamp: $stamp,
            status: 1,
            requestedStart: '08:30:00',
            requestedEnd: '17:30:00',
            requestedRemarks: '承認テスト'
        );

        $response = $this->actingAs($admin)
            ->post("/stamp_correction_request/approve/{$apply->id}");

        $response->assertStatus(302);

        $this->assertDatabaseHas('attendance_correct_requests', [
            'id' => $apply->id,
            'status' => 2,
        ]);

        $this->assertDatabaseHas('stamps', [
            'id' => $stamp->id,
            'start_work' => '08:30:00',
            'end_work'   => '17:30:00',
        ]);
    }

}


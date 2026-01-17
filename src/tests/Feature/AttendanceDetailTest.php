<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use App\Models\Staff;
use App\Models\Stamp;
use App\Models\Rest;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private function loginStaff(): Staff
    {
        $staff = Staff::factory()->create([
            'name' => 'テスト太郎',
        ]);
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


    private function makeStamp(Staff $staff): Stamp
    {
        $stampDate = Carbon::create(2026, 1, 11, 0, 0, 0, 'Asia/Tokyo');

        return Stamp::factory()->create([
            'staff_id' => $staff->id,
            'stamp_date' => $stampDate->toDateString(),
            'start_work' => $stampDate->copy()->setTime(9, 0),
            'end_work' => $stampDate->copy()->setTime(18, 0),
        ]);

    }

    private function makeRests(Stamp $stamp): Stamp
    {
        Rest::factory()->create([
            'stamp_id' => $stamp->id,
            'start_rest' => '12:00:00',
            'end_rest' => '12:45:00',
        ]);

        Rest::factory()->create([
            'stamp_id' => $stamp->id,
            'start_rest' => '15:00:00',
            'end_rest' => '15:15:00',
        ]);

        return $stamp;
    }

    private function openDetail(Stamp $stamp)
    {
        return $this->get(route('attendance.detail', ['stamp' => $stamp->id]));
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_detail_name_is_login_staff_name()
    {
        $staff = $this->loginStaff();
        $stamp = $this->makeStamp($staff);

        $response = $this->openDetail($stamp);
        $response->assertStatus(200);

        $response->assertSee($staff->name);
    }

    public function test_detail_date_is_selected_date()
    {
        $staff = $this->loginStaff();
        $stamp = $this->makeStamp($staff);

        $response = $this->openDetail($stamp);
        $response->assertStatus(200);

        $date = Carbon::parse($stamp->stamp_date);

        $response->assertSee($date->format('Y年'));
        $response->assertSee($date->format('m月d日'));

    }

    public function test_detail_start_end_work_matches_stamp()
    {
        $staff = $this->loginStaff();
        $stamp = $this->makeStamp($staff);

        $response = $this->openDetail($stamp);
        $response->assertStatus(200);

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

public function test_detail_rests_match_stamp()
{
    $staff = $this->loginStaff();
    $stamp = $this->makeStamp($staff);

    $this->makeRests($stamp);

    $response = $this->openDetail($stamp);
    $response->assertStatus(200);

    $response->assertSee('12:00');
    $response->assertSee('12:45');
    $response->assertSee('15:00');
    $response->assertSee('15:15');
}}

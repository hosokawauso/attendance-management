<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

use App\Models\Staff;
use App\Models\Stamp;
use App\Models\Rest;

class AdminAttendanceDetailCorrectionTest extends TestCase
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

    /**
     * 管理者ログイン（ここだけあなたの実装に合わせて修正）
     * guard が別なら actingAs($admin, 'admin') にする
     */
    private function loginAdmin(): Staff
    {
        $admin = Staff::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin);
        return $admin;
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

    private function makeRests(Stamp $stamp): void
    {
        Rest::factory()->create([
            'stamp_id'    => $stamp->id,
            'start_rest'  => '12:00:00',
            'end_rest'    => '12:45:00',
        ]);

        Rest::factory()->create([
            'stamp_id'    => $stamp->id,
            'start_rest'  => '15:00:00',
            'end_rest'    => '15:15:00',
        ]);
    }


    private function detailUrl(Stamp $stamp): string
    {
        return route('admin.attendance.detail', ['stamp' => $stamp->id]);
    }

    private function openDetail(Stamp $stamp)
    {
        return $this->get($this->detailUrl($stamp));
    }

    private function postDetail(Stamp $stamp, array $payload)
    {
        return $this->post($this->detailUrl($stamp), $payload);
    }

    private function validPayload(): array
    {
        return [
            'start_work' => '09:00',
            'end_work'   => '18:00',
            'rests' => [
                ['start' => '12:00', 'end' => '12:45'],
                ['start' => '15:00', 'end' => '15:15'],
            ],
            'admin_comment' => '出勤時刻を修正します',
        ];
    }    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_detail_shows_selected_stamp_data()
    {
        $this->freezeNow(2026, 1, 11);

        $this->loginAdmin();

        $staff = Staff::factory()->create([
            'name' => 'スタッフA',
        ]);
        $stamp = $this->makeStamp($staff, '2026-01-11', '09:00', '18:00');
        $this->makeRests($stamp);

        $response = $this->openDetail($stamp);
        $response->assertStatus(200);

        $response->assertSee('スタッフA');

        $response->assertSee('2026年');
        $response->assertSee('01月11日');

        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('12:00');
        $response->assertSee('12:45');
        $response->assertSee('15:00');
        $response->assertSee('15:15');
    }
    public function test_error_when_start_work_is_after_end_work()
    {
        $this->freezeNow(2026, 1, 11);

        $this->loginAdmin();

        $staff = Staff::factory()->create();
        $stamp = $this->makeStamp($staff);

        $this->openDetail($stamp)->assertStatus(200);

        $payload = $this->validPayload();
        $payload['start_work'] = '19:00';
        $payload['end_work']   = '18:00';

        $response = $this->postDetail($stamp, $payload);

        $response->assertRedirect();
        $response->assertSessionHasErrors('end_work');

        $this->assertSame(
            '出勤時間もしくは退勤時間が不適切な値です',
            session('errors')->first('end_work')
        );
    }

    public function test_error_when_rest_start_is_after_end_work()
    {
        $this->freezeNow(2026, 1, 11);

        $this->loginAdmin();

        $staff = Staff::factory()->create();
        $stamp = $this->makeStamp($staff);

        $this->openDetail($stamp)->assertStatus(200);

        $payload = $this->validPayload();
        $payload['end_work'] = '18:00';
        $payload['rests'][0]['start'] = '19:00';

        $response = $this->postDetail($stamp, $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('rests.0.start');

        $this->assertSame(
            '休憩時間が不適切な値です',
            session('errors')->first('rests.0.start')
        );
    }

    public function test_error_when_rest_end_is_after_end_work()
    {
        $this->freezeNow(2026, 1, 11);

        $this->loginAdmin();

        $staff = Staff::factory()->create();
        $stamp = $this->makeStamp($staff);

        $this->openDetail($stamp)->assertStatus(200);

        $payload = $this->validPayload();
        $payload['end_work'] = '18:00';
        $payload['rests'][0]['end']   = '19:00';

        $response = $this->postDetail($stamp, $payload);

        $response->assertRedirect();
        $response->assertSessionHasErrors('rests.0.end');

        $this->assertSame(
            '休憩時間もしくは退勤時間が不適切な値です',
            session('errors')->first('rests.0.end')
        );
    }

    public function test_error_when_admin_comment_is_empty()
    {
        $this->freezeNow(2026, 1, 11);

        $this->loginAdmin();

        $staff = Staff::factory()->create();
        $stamp = $this->makeStamp($staff);

        $this->openDetail($stamp)->assertStatus(200);

        $payload = $this->validPayload();
        $payload['admin_comment'] = '';

        $response = $this->postDetail($stamp, $payload);

        $response->assertRedirect();
        $response->assertSessionHasErrors('admin_comment');

        $this->assertSame(
            '備考を記入してください',
            session('errors')->first('admin_comment')
        );
    }
}

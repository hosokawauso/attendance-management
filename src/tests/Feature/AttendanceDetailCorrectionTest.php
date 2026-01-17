<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use App\Models\Staff;
use App\Models\Stamp;
use App\Models\Rest;

class AttendanceDetailCorrectionTest extends TestCase
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

    private function makeRests(Stamp $stamp): void
    {
        Rest::factory()->create([
            'stamp_id' => $stamp->id,
            'start_rest' => '12:00',
            'end_rest' => '13:00',
        ]);
    }

    private function openDetail(Stamp $stamp)
    {
        return $this->get(route('attendance.detail', ['stamp' => $stamp->id]));
    }

    private function postDetail(Stamp $stamp, array $payload)
    {
        return $this->post(route('attendance.detail', ['stamp' => $stamp->id]), $payload);
    }

    private function validPayload(): array
    {
        return [
            'start_work' => '09:00',
            'end_work'   => '18:00',
            'rests' => [
                ['start' => '12:00', 'end' => '13:00'],
            ],
            'requested_remarks' => '修正理由です',
        ];
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_error_when_start_work_is_after_end_work()
    {
        $staff = $this->loginStaff();
        $stamp = $this->makeStamp($staff);

        $this->get("/attendance/detail/{$stamp->id}")->assertStatus(200);

        $payload = $this->validPayload();
        $payload['start_work'] = '19:00';
        $payload['end_work']   = '18:00';

        $response = $this->post("/attendance/detail/{$stamp->id}", $payload);

        $response->assertRedirect();
        $response->assertSessionHasErrors('start_work');

        // メッセージまで厳密に見る（あなたのwithValidatorの文言に合わせる）
        $this->assertSame(
            '出勤時間もしくは退勤時間が不適切な値です',
            session('errors')->first('start_work')
        );
    }

    public function test_error_when_rest_start_is_after_end_work()
    {
        $staff = $this->loginStaff();
        $stamp = $this->makeStamp($staff);

        $this->get("/attendance/detail/{$stamp->id}")->assertStatus(200);

        $payload = $this->validPayload();
        $payload['end_work'] = '18:00';
        $payload['rests'][0]['start'] = '19:00';
        $payload['rests'][0]['end']   = '19:10';

        $response = $this->post("/attendance/detail/{$stamp->id}", $payload);

        $response->assertRedirect();
        $response->assertSessionHasErrors('rests');

        $this->assertSame(
            '休憩時間が不適切な値です',
            session('errors')->first('rests')
        );
    }


    public function test_error_when_rest_end_is_after_end_work()
    {
        $staff = $this->loginStaff();
        $stamp = $this->makeStamp($staff);

        $this->get("/attendance/detail/{$stamp->id}")->assertStatus(200);

        $payload = $this->validPayload();
        $payload['end_work'] = '18:00';
        $payload['rests'][0]['start'] = '17:50';
        $payload['rests'][0]['end']   = '19:00';

        $response = $this->post("/attendance/detail/{$stamp->id}", $payload);

        $response->assertRedirect();
        $response->assertSessionHasErrors('rests');

        $this->assertSame(
            '休憩時間もしくは退勤時間が不適切な値です',
            session('errors')->first('rests')
        );
    }

    public function test_error_when_remarks_is_empty()
    {
        $staff = $this->loginStaff();
        $stamp = $this->makeStamp($staff);

        $this->get("/attendance/detail/{$stamp->id}")->assertStatus(200);

        $payload = $this->validPayload();
        $payload['remarks'] = '';

        $response = $this->post("/attendance/detail/{$stamp->id}", $payload);

        $response->assertRedirect();
        $response->assertSessionHasErrors('remarks');

        $this->assertSame(
            '備考を記入してください',
            session('errors')->first('remarks')
        );
    }

    public function test_correction_request_is_created()
    {
        $staff = $this->loginStaff();
        $stamp = $this->makeStamp($staff);
        $this->makeRests($stamp);

        $payload = $this->validPayload();
        $payload['start_work'] = '09:30'; // 何かしら修正
        $payload['remarks'] = '出勤時刻を修正します';

        $this->postDetail($stamp, $payload)->assertRedirect();

        /**
         * ここはあなたのDB設計に合わせてassertを調整してね。
         * 例：修正申請テーブルが attendance_correct_requests で、
         * staff_id と stamp_id と remarks を持っている想定。
         */
        $this->assertDatabaseHas('attendance_correct_requests', [
            'staff_id' => $staff->id,
            'stamp_id' => $stamp->id,
            'requested_remarks'  => '出勤時刻を修正します',
        ]);
    }

    public function test_pending_list_shows_all_my_requests()
    {
        $staff = $this->loginStaff();
        $stamp1 = $this->makeStamp($staff);
        $stamp2 = $this->makeStamp($staff);

        // 2回申請（このPOSTが申請作成をする前提）
        $p1 = $this->validPayload(); $p1['remarks'] = '申請1';
        $p2 = $this->validPayload(); $p2['remarks'] = '申請2';
        $this->postDetail($stamp1, $p1)->assertRedirect();
        $this->postDetail($stamp2, $p2)->assertRedirect();

        // ここも route 名はあなたの環境に合わせて変更
        $response = $this->get(route('stamp_correction_request.list'));
        $response->assertStatus(200);
        $response->assertSee('申請1');
        $response->assertSee('申請2');
    }
    public function test_approved_list_shows_admin_approved_requests()
    {
        $staff = $this->loginStaff();
        $stamp = $this->makeStamp($staff);

        $payload = $this->validPayload();
        $payload['remarks'] = '承認される申請';
        $this->postDetail($stamp, $payload)->assertRedirect();

        /**
         * ここは「管理者が承認した状態」を作る必要があるので、
         * あなたの承認テーブル/カラムに合わせて “承認済み” に更新する処理を入れてね。
         *
         * 例：attendance_correct_requests に status カラムがあって approved にする、など。
         *
         * DB設計が分かればここをピンポイントで書き換えるよ。
         */
        // \DB::table('attendance_correct_requests')->where(...)->update(['status' => 'approved']);

        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);
        $response->assertSee('承認される申請');
    }

    public function test_click_detail_from_request_list_goes_to_attendance_detail()
    {
        $staff = $this->loginStaff();
        $stamp = $this->makeStamp($staff);

        $payload = $this->validPayload();
        $payload['remarks'] = '詳細遷移テスト';
        $this->postDetail($stamp, $payload)->assertRedirect();

        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);

        // 一旦 “詳細ボタンがある” を確認（表示文言はあなたのBladeに合わせて）
        $response->assertSee('詳細');

        // 画面遷移そのものは「詳細のURLが含まれている」で確認するのが手早い
        // ここも実際のリンク先に合わせて調整
        $response->assertSee('/attendance/detail/' . $stamp->id);
    }}

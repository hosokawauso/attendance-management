<?php

namespace App\Http\Controllers;

use App\Models\Stamp;
use App\Models\Rest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function handle(Request $request)
    {
        [$state, $stamp] = $this->calcState();

        if ($request->isMethod('post')) {
            $action  = $request->input('action'); // clock-in / break-start / break-end / clock-out
            $allowed = [
                'idle'     => ['clock-in'],
                'working'  => ['break-start','clock-out'],
                'on_break' => ['break-end'],
                'finished' => [],
            ];
            abort_unless(in_array($action, $allowed[$state] ?? [], true), 409, 'invalid transition');

            DB::transaction(function () use ($action, &$stamp) {
                $now  = now('Asia/Tokyo');
                $date = $now->toDateString();

                // 当日のスタンプを用意
                $stamp = $stamp ?? Stamp::firstOrCreate(
                    ['staff_id' => Auth::id(), 'stamp_date' => $date],
                    ['approved' => false]
                );

                if ($action === 'clock-in') {
                    // すでに開始済みなら弾く（stateガードで通常は来ない）
                    if ($stamp->start_work) abort(409, 'already started');
                    $stamp->start_work = $now;
                    $stamp->save();
                    return;
                }

                if ($action === 'break-start') {
                    // 未クローズの休憩がないことを保証
                    $open = $stamp->rests()->whereNull('end_rest')->exists();
                    if ($open) abort(409, 'break already started');
                    $stamp->rests()->create([
                        'stamp_date' => $date,
                        'start_rest' => $now,
                    ]);
                    return;
                }

                if ($action === 'break-end') {
                    $rest = $stamp->rests()->whereNull('end_rest')->latest('start_rest')->first();
                    if (!$rest) abort(409, 'no open break');
                    $rest->end_rest = $now;
                    $rest->save();
                    return;
                }

                if ($action === 'clock-out') {
                    // 休憩中は退勤不可
                    $open = $stamp->rests()->whereNull('end_rest')->exists();
                    if ($open) abort(409, 'break not ended');

                    $stamp->end_work = $now;

                    // 合計計算（分）
                    $work = Carbon::parse($stamp->start_work)->diffInMinutes($stamp->end_work);
                    $rest = $stamp->restMinutes();
                    $stamp->total_work = max(0, $work - $rest);

                    $stamp->save();
                    return;
                }
            });

            return back(); // /attendance を再描画
        }

        // 本日の休憩ログ（見せたいなら）
        $rests = $stamp?->rests()->orderBy('start_rest')->get() ?? collect();

        $now = now('Asia/Tokyo');

        return view('staff.attendance', [
            'state' => $state,
            'stamp' => $stamp,
            'rests' => $rests,
            'now' => $now,
        ]);
    }

    /** 今日の状態を決める */
    private function calcState(): array
    {
        $today = today('Asia/Tokyo');
        $stamp = Stamp::where('staff_id', Auth::id())
            ->whereDate('stamp_date', $today)
            ->first();

        if (!$stamp || !$stamp->start_work) return ['idle', $stamp];
        if ($stamp->end_work) return ['finished', $stamp];

        $openBreak = $stamp->rests()->whereNull('end_rest')->exists();
        return $openBreak ? ['on_break', $stamp] : ['working', $stamp];
    }
}

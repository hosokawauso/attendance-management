<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Staff;
use App\Models\Stamp;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\AdminAttendanceUpdateRequest;


class AdminController extends Controller
{
    public function login(AdminLoginRequest $request)
{
    if (!Auth::attempt($request->validated())) {
        return back()
            ->withErrors(['email' => 'ログイン情報が登録されていません。'])
            ->withInput();
    }

    $request->session()->regenerate();

    return redirect()->route('admin.attendance.list');
}

    public function attendanceList(Request $request)
    {

        $staff = Auth::user();

        $dateParam = $request->query('date', now('Asia/Tokyo')->toDateString());

        try {
        $date = Carbon::createFromFormat('Y-m-d', $dateParam, 'Asia/Tokyo')->startOfDay();
        } catch (\Throwable $e) {
        $date= now('Asia/Tokyo')->startOfDay();
        }

        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();


        $prevDate = $date->copy()->subDay();
        $nextDate = $date->copy()->addDay();

        $stamps = Stamp::with('staff', 'rests')
            ->whereBetween('stamp_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('staff_id')
            ->orderBy('start_work')
            ->get();

        return view('admin.admin_attendance_list', compact(
            'staff',
            'stamps',
            'date',
            'prevDate',
            'nextDate',
        ));

    }

    public function staffList(Request $request)
    {
        $staffs = Staff::orderBy('id')->get();

        return view('admin.staff_list', compact('staffs'));
    }

    public function staffMonthly(Request $request, Staff $staff)
    {

    abort_unless(Auth::user()?->is_admin, 403);

        $monthParam = (string) $request->query('month', now('Asia/Tokyo')->format('Y-m'));
        try {
        $month = CarbonImmutable::createFromFormat('Y-m', $monthParam, 'Asia/Tokyo')->startOfMonth();
        } catch (\Throwable $e) {
            $month = CarbonImmutable::now('Asia/Tokyo')->startOfMonth();
        }

        $start = $month->startOfMonth();
        $end = $month->endOfMonth();

        $prevMonth = $month->subMonth()->format('Y-m');
        $nextMonth = $month->addMonth()->format('Y-m');

        $monthlyStamps = Stamp::with('rests')
            ->where('staff_id', $staff->id)
            ->whereBetween('stamp_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('stamp_date')
            ->get();

        $stampsByDate = $monthlyStamps->keyBy(function ($stamp) {
            if ($stamp->stamp_date instanceof \Carbon\CarbonInterface) {
                return $stamp->stamp_date->toDateString();
            }

            return (string) $stamp->stamp_date;
        });

        $period = CarbonPeriod::create($start,$end);


        return view('admin.admin_attendance_staff', compact(
            'staff',
            'month',
            'period',
            'stampsByDate',
            'prevMonth',
            'nextMonth',
        ));

    }

    public function detail(Stamp $stamp)
    {
        $stamp->load('staff', 'rests');

        $admin = Auth::user();
        $rests = $stamp->rests;
        $staff = $stamp->staff;

        return view('admin.admin_attendance', compact(
            'admin',
            'staff',
            'stamp',
            'rests',
        ));
    }


    public function update(AdminAttendanceUpdateRequest $request, Stamp $stamp)
    {
        $admin = Auth::user();

        $validated = $request->validated();

        if(!empty($validated['start_work'])) {
            $stamp->start_work = $stamp->stamp_date
                ->copy()
                ->setTimeFromTimeString($validated['start_work']);
        } else {
            $stamp->start_work = null;
        }

        if(!empty($validated['end_work'])) {
            $stamp->end_work = $stamp->stamp_date
                ->copy()
                ->setTimeFromTimeString($validated['end_work']);
        } else {
            $stamp->end_work = null;
        }

        $stamp->remarks = $validated['remarks'] ?? null;
        $stamp->save();

        $restInputs = $validated['rests'] ?? [];

        if (!is_array($restInputs)) {
            $restInputs = [];
        }

        $stamp->rests()->delete();

        foreach($restInputs as $row) {
            $start = $row['start'] ?? null;
            $end = $row['end'] ?? null;

            if(!$start && !$end) {
                continue;
            }

            $stamp->rests()->create([
                'start_rest' => $start,
                'end_rest' => $end,
            ]);
        }

        return redirect()
            ->route('admin.attendance.detail', $stamp->id);

    }

    public function staffMonthlyCsv(Staff $staff)
    {
        $monthParam = request('month', now('Asia/Tokyo')->format('Y-m'));

        try {
            $month = Carbon::createFromFormat('!Y-m', $monthParam, 'Asia/Tokyo')->startOfMonth();
        } catch (\Exception $e) {
            $month = now('Asia/Tokyo')->startOfMonth();
        }

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        $period = CarbonPeriod::create($start, $end);

        // この月の打刻をまとめて取得（休憩も）
        $stamps = Stamp::with('rests')
            ->where('staff_id', $staff->id)
            ->whereBetween('stamp_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        // stamp_date をキーにして引けるように
        $stampsByDate = $stamps->keyBy(fn($s) => Carbon::parse($s->stamp_date)->format('Y-m-d'));

        $filename = sprintf('attendance_%s_%s.csv', $staff->id, $month->format('Y-m'));

        return response()->streamDownload(function () use ($staff, $month, $period, $stampsByDate) {
            $out = fopen('php://output', 'w');

            // Excel文字化け対策（UTF-8 BOM）
            fwrite($out, "\xEF\xBB\xBF");

            // ヘッダ
            fputcsv($out, ['スタッフ名', '対象月', '日付', '出勤', '退勤', '休憩(H:MM)', '合計(H:MM)']);

            foreach ($period as $date) {
                $dateKey = $date->format('Y-m-d');
                $stamp = $stampsByDate[$dateKey] ?? null;

                $startWork = '';
                $endWork   = '';
                $restHm    = '';
                $netWorkHm = '';

                if ($stamp) {
                    $startWork = $stamp->start_work ? substr((string)$stamp->start_work, 0, 5) : '';
                    $endWork   = $stamp->end_work   ? substr((string)$stamp->end_work, 0, 5) : '';

                    // 休憩（分）
                    $restMin = (int) $stamp->restMinutes();
                    $restHm  = sprintf('%d:%02d', intdiv($restMin, 60), $restMin % 60);

                    // 合計（分）= 勤務分 - 休憩分
                    $workMin = 0;
                    if (!empty($stamp->start_work) && !empty($stamp->end_work)) {
                        $day = $date->format('Y-m-d');
                        $start = Carbon::parse($day.' '.$stamp->start_work, 'Asia/Tokyo');
                        $end   = Carbon::parse($day.' '.$stamp->end_work, 'Asia/Tokyo');
                        $workMin = $start->diffInMinutes($end);
                    }

                    $netWorkMin = max(0, $workMin - $restMin);
                    $netWorkHm  = sprintf('%d:%02d', intdiv($netWorkMin, 60), $netWorkMin % 60);
                }

                fputcsv($out, [
                    $staff->name,
                    $month->format('Y-m'),
                    $date->format('Y-m-d'),
                    $startWork,
                    $endWork,
                    $restHm,
                    $netWorkHm,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Stamp;
use App\Model\Staff;
use Carbon\CarbonPeriod;
use App\Http\Requests\AttendanceUpdateRequest;
use App\Http\Requests\CorrectRequest;
use App\Models\AttendanceCorrectRequest;
use Illuminate\Support\Facades\DB;


class StaffController extends Controller
{
    public function attendanceIndex(Request $request)
    {
        $monthParam = $request->query('month', now('Asia/Tokyo')->format('Y-m'));
        try {
        $month = Carbon::createFromFormat('Y-m', $monthParam, 'Asia/Tokyo')->startOfMonth();
        } catch (\Throwable $e) {
        $month = now('Asia/Tokyo')->startOfMonth();
        }

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $prevMonth = $month->copy()->subMonth()->format('Y-m');
        $nextMonth = $month->copy()->addMonth()->format('Y-m');

        $monthlyStamps = Stamp::with('rests')
            ->where('staff_id', Auth::id())
            ->whereBetween('stamp_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('stamp_date')
            ->get();

        $stampsByDate = $monthlyStamps->keyBy(function ($stamp) {
            if ($stamp->stamp_date instanceof \Carbon\Carbon) {
                return $stamp->stamp_date->toDateString();
            }

            return (string) $stamp->stamp_date;
        });

        $period = CarbonPeriod::create($start,$end);

        return view('staff.attendance_list', compact(
            'period',
            'stampsByDate',
            'month',
            'prevMonth',
            'nextMonth',
        ));
    }


    public function requestIndex(Request $request)
    {
        $staff = Auth::user();

        $base = AttendanceCorrectRequest::with('stamp')
            ->where('staff_id', $staff->id)
            ->orderByDesc('created_at');

        $pendingRequests = (clone $base)
            ->where('status', AttendanceCorrectRequest::STATUS_PENDING)
            ->get();

        $approvedRequests = (clone $base)
            ->where('status', AttendanceCorrectRequest::STATUS_APPROVED)
            ->get();

        return view('stamp_correction_request', compact(
            'pendingRequests',
            'approvedRequests',
            'staff',
        ));
    }

    public function detail($id)
    {
        $staff = Auth::user();

        $stamp = Stamp::with('staff', 'rests')
            ->where('id', $id)
            ->where('staff_id', $staff->id)
            ->firstOrFail();

        $stamp->load(['rests' => function ($q) {
            $q->orderBy('start_rest');
        }]);

        $rests = $stamp->rests->values();

        $latestReq = AttendanceCorrectRequest::where('stamp_id', $stamp->id)
            ->orderByDesc('created_at')
            ->first();

        return view('staff.attendance_detail', compact(
            'staff',
            'stamp',
            'rests',
            'latestReq',
        ));
    }

    public function attendanceCorrectionRequestStore(AttendanceUpdateRequest $request, Stamp $stamp)
    {
        $staff = Auth::user();
        abort_unless($stamp->staff_id === $staff->id, 403);

        $hasPending = AttendanceCorrectRequest::where('stamp_id', $stamp->id)
            ->where('status', AttendanceCorrectRequest::STATUS_PENDING)
            ->exists();

        if ($hasPending) {
            return back()->with('error', '承認待ちのため修正できません。');
        }

        $validated = $request->validated();
        $restInputs = $validated['rests'] ?? [];

        DB::transaction(function () use  ($validated, $restInputs,$stamp, $staff) {
            $req = AttendanceCorrectRequest::create([
                'stamp_id' => $stamp->id,
                'staff_id' => $staff->id,
                'status'    => AttendanceCorrectRequest::STATUS_PENDING,
                'requested_start_work' => $validated['start_work'] ?? null,
                'requested_end_work'   => $validated['end_work'] ?? null,
                'requested_remarks'    => $validated['remarks'],
            ]);

            foreach ($restInputs as $i => $row) {
                $start = $row['start'] ?? null;
                $end   = $row['end'] ?? null;

                if (!$start && !$end) {
                    continue;
                }

                $req->rests()->create([
                    'requested_start_rest' => $start,
                    'requested_end_rest'   => $end,
                    'sort_order'           => $i,
                ]);
            }
        });

        return redirect('/stamp_correction_request/list');
    }

    public function requestShow(CorrectRequest $attendance_correct_request)
    {
        $staff = Auth::user();

        abort_unless($attendance_correct_request->staff_id === $staff->id, 403);

        $attendance_correct_request->load('stamp');

        return view('stamp_correction_request_detail',[
        'staff' => $staff,
        'req' => $attendance_correct_request,

    ]);

    }
}


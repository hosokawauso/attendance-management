<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Stamp;
use App\Model\Staff;
use Carbon\CarbonPeriod;
use App\Http\Requests\AttendanceUpdateRequest;

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
        $staffId = $staff->id;


        $pendingStamps = Stamp::where('staff_id', $staffId)
            ->where('status', Stamp::STATUS_PENDING)
            ->orderBy('stamp_date', 'desc')
            ->get();


        $approvedStamps = Stamp::where('staff_id', $staffId)
            ->where('status', Stamp::STATUS_APPROVED)
            ->orderBy('stamp_date', 'desc')
            ->get();


        return view('staff.stamp_correction_request', compact(
            'pendingStamps',
            'approvedStamps',
            'staff'
        ));

    }

    public function detail($id)
    {
        $staff = Auth::user();

        $stamp = Stamp::with('staff', 'rests')
            ->where('id', $id)
            ->where('staff_id', $staff->id)
            ->firstOrFail();

        $stamp->load('rests');
        $rests = $stamp->rests;

        return view('staff.attendance_detail', compact('staff', 'stamp', 'rests'));
    }

    public function update(AttendanceUpdateRequest $request, Stamp $stamp)
    {
        $staff = Auth::user();

        if ($stamp->staff_id !== $staff->id) {
        abort(403);
        }

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

        $restInputs = $validated['rests'] ?? null;

        if (!is_array($restInputs)) {
            $restInputs = [];
        }

        foreach($restInputs as $row) {
            $start = $row['start'] ?? null;
            $end = $row['end'] ?? null;

            if(!$start && !$end) {
                continue;
            }

            $stamp->rests()->create([
                'stamp_date' => $stamp->stamp_date,
                'start_rest' => $start,
                'end_rest' => $end,
            ]);
        }

        $stamp->status = Stamp::STATUS_PENDING;
        $stamp->save();

        return redirect()
            ->route('attendance.detail', $stamp->id);

    }
}


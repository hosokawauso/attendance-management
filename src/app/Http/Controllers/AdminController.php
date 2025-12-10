<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Staff;
use App\Models\Stamp;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Http\Requests\AdminAttendanceUpdateRequest;


class AdminController extends Controller
{
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

        return redirect()
            ->route('admin.attendance.detail', $stamp->id);

    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceCorrectRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\carbon;

class AttendanceCorrectionRequestController extends Controller
{
    public function index()
    {
        $staff = Auth::user();

        $attendanceModel = AttendanceCorrectRequest::with('stamp')
            ->orderByDesc('created_at');
        $pendingModel = (clone $attendanceModel)->where('status', AttendanceCorrectRequest::STATUS_PENDING);
        $approvedModel = (clone $attendanceModel)->where('status', AttendanceCorrectRequest::STATUS_APPROVED);
        if (!$staff->is_admin) {

            $pendingModel = $pendingModel->where('staff_id', $staff->id);
            $approvedModel = $approvedModel->where('staff_id', $staff->id);
        }
        $pendingRequests = $pendingModel->get();
        $approvedRequests = $approvedModel->get();


        return view('stamp_correction_request', compact(
            'staff',
            'pendingRequests',
            'approvedRequests',
        ));
    }

    public function show(AttendanceCorrectRequest $attendance_correct_request)
    {
        $attendance_correct_request->load(['staff', 'stamp', 'rests']);

        return view('admin.stamp_correction_request_approve', [
            'req' => $attendance_correct_request,
            'requestedRests' => $attendance_correct_request->rests,
        ]);
    }

    public function approve(AttendanceCorrectRequest $attendance_correct_request)
    {
        $admin = Auth::user();
        abort_unless($admin->is_admin, 403);
        abort_unless($attendance_correct_request->status === AttendanceCorrectRequest::STATUS_PENDING, 400);

        $attendance_correct_request->load(['stamp.rests', 'rests']);

        DB::transaction(function () use ($attendance_correct_request, $admin) {

            $stamp = $attendance_correct_request->stamp;

            $baseDate = Carbon::parse($stamp->stamp_date)->startOfDay();

            $sw = $attendance_correct_request->getRawOriginal('requested_start_work');
            $ew = $attendance_correct_request->getRawOriginal('requested_end_work');

            if(!empty($sw)) {
                $stamp->start_work = $baseDate->copy()
                    ->setTimeFromTimeString(substr((string)$sw, 0, 5));
            }


            if(!empty($ew)) {
                $stamp->end_work = $baseDate->copy()
                    ->setTimeFromTimeString(substr((string)$ew, 0, 5));

            }

            $stamp->remarks = $attendance_correct_request->requested_remarks;
            $stamp->save();

            $stamp->rests()->delete();

            foreach($attendance_correct_request->rests as $r) {
                if(!$r->requested_start_rest && !$r->requested_end_rest) continue;

                $stamp->rests()->create([
                    'stamp_date' => $baseDate->todateString(),
                    'start_rest' => $r->getRawOriginal('requested_start_rest'),
                    'end_rest' => $r->getRawOriginal('requested_end_rest'),
                ]);
            }

            $attendance_correct_request->status = AttendanceCorrectRequest::STATUS_APPROVED;
            $attendance_correct_request->approved_by = $admin->id;
            $attendance_correct_request->approved_at = now();
            $attendance_correct_request->save();

        });

        return response()->json([
            'ok' => true,
            'status' => $attendance_correct_request->status,
            'label' => '承認済み',
        ]);
    }

}

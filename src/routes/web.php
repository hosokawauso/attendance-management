<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCorrectionRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// 管理者ログイン
Route::get('/admin/login', function () {
    return view('auth.admin_login');
})->middleware('guest')->name('admin.login');

Route::post('/admin/login', [AdminController::class, 'login'])
    ->middleware('guest')
    ->name('admin.login.confirm');

// ログアウト管理
Route::post('/logout', function (Request $request) {

    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
})->name('logout');

Route::post('/admin/logout', function (Request $request) {

    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/admin/login');
})->name('/admin.logout');


// 管理者ルート
Route::middleware('auth')->group(function () {
    Route::get('/admin/attendance/list', [AdminController::class, 'attendanceList'])->name('admin.attendance.list');
});


Route::get('/admin/staff/list', [AdminController::class, 'staffList'])->name('admin.staff.list');

Route::get('/admin/attendance/list/{staff}', [AdminController::class, 'staffMonthly'])->name('admin.attendance.staff.monthly');

Route::get('/stamp_correction_request/list', [AdminController::class, 'approved'])->name('admin.stamp_correction_request.list');

Route::get('/admin/attendance/{stamp}', [AdminController::class, 'detail'])->name('admin.attendance.detail');

Route::post('/admin/attendance/{stamp}', [AdminController::class, 'update'])->name('admin.attendance.update');






// 一般スタッフのルート
Route::get('/attendance/list', [StaffController::class, 'attendanceIndex'])->name('attendance.list');

Route::get('/attendance/detail/{stamp}', [StaffController::class, 'detail'])->name('attendance.detail');

Route::post('/attendance/detail/{stamp}', [StaffController::class, 'attendanceCorrectionRequestStore'])->name('attendance.request');

Route::get('/stamp_correction_request/{attendance_correct_request}', [StaffController::class, 'requestShow'])->name('stamp_correction_request.show');




// 修正申請申請のルート
Route::middleware('auth')->group(function () {

    Route::get('/stamp_correction_request/list', [AttendanceCorrectionRequestController::class, 'index'])->name('stamp_correction_request.list');

    Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AttendanceCorrectionRequestController::class, 'show'])->name('stamp_correction_request.show');

    Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [AttendanceCorrectionRequestController::class, 'approve'])->name('stamp_correction_request.approve');

});




// 勤務時間の登録ルート
Route::middleware('auth')
    ->match(['get','post'], '/attendance', [AttendanceController::class, 'handle'])->name('attendance');
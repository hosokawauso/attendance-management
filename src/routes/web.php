<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;

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
Route::get('/admin/login', function () {
    return view('auth.admin_login');
})->middleware('guest')->name('admin.login');


Route::middleware('auth')->group(function () {
    Route::get('/admin/attendance/list', [AdminController::class, 'attendanceList'])->name('admin.attendance.list');
});


Route::get('/admin/staff/list', [AdminController::class, 'staffList'])->name('admin.staff.list');

Route::get('/stamp_correction_request/list', [AdminController::class, 'approved'])->name('admin.stamp_correction_request.list');

Route::get('/admin/attendance/{stamp}', [AdminController::class, 'detail'])->name('admin.attendance.detail');

Route::put('/admin/attendance/{stamp}', [AdminController::class, 'update'])->name('admin.attendance.update');






/* Route::get('/attendance', [StaffController::class, 'record'])->name('attendance.record');
 */
Route::get('/attendance/list', [StaffController::class, 'attendanceIndex'])->name('attendance.list');

/* Route::middleware('auth:staff')->group(function () {
    Route::get('/stamp_correction_request/list', [StaffController::class, 'requestIndex'])->name('request.list');
});
 */
Route::get('/stamp_correction_request/list', [StaffController::class, 'requestIndex'])->name('request.list');

Route::get('/attendance/detail/{stamp}', [StaffController::class, 'detail'])->name('attendance.detail');

Route::put('/attendance/detail/{stamp}', [StaffController::class, 'update'])->name('attendance.update');


Route::middleware('auth')
    ->match(['get','post'], '/attendance', [AttendanceController::class, 'handle'])->name('attendance');
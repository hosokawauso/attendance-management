<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->query('page', 'recommend');
        return view('staff.attendance_list', compact('page'));
    }
}

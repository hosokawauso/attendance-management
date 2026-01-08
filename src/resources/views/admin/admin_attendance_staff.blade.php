@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/admin_attendance_staff.css') }}">
@endsection

@section('content')
<div class="attendance-wrap">

<h2 class="page-title">{{ $staff->name }}さんの勤怠一覧</h2>
  <div class="top-tabs">
      <a class="nav-btn" href="{{ route('admin.attendance.staff.monthly', ['staff' => $staff->id, 'month' => $prevMonth]) }}">← 前月</a>
    <div class="nav-center">
      <span class="nav-icon" aria-hidden="true">📅</span>
      <strong>{{ $month->format('Y') }}/{{ $month->format('m') }}</strong>
    </div>
    <a class="nav-btn nav-next" href="{{ route('admin.attendance.staff.monthly', ['staff' => $staff->id, 'month' => $nextMonth]) }}">翌月 →</a>
  </div>

  <div class="card card-table">
    <div class="table-wrapper">
      @if($period->count())
        <table class="month-table">
          <thead>
            <tr>
              <th>日付</th>
              <th>出勤</th>
              <th>退勤</th>
              <th>休憩</th>
              <th>合計</th>
              <th>詳細</th>
            </tr>
          </thead>
          <tbody>
          @php
            // ループの外で1回だけ定義
            $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
          @endphp

          @foreach($period as $date)
            @php
              $dateKey = $date->format('Y-m-d');

              $stamp = $stampsByDate[$dateKey] ?? null;

              $restMin = $stamp?->restMinutes() ?? 0;
              $workMin = ($stamp && $stamp->start_work && $stamp->end_work)
                          ? $stamp->start_work->diffInMinutes($stamp->end_work)
                          : 0;
              $restHour = intdiv($restMin, 60);
              $restMinute = $restMin % 60;
              $restHm = sprintf('%02d:%02d', $restHour, $restMinute);

              // 実労働時間 (分)
              $netWorkMin = max(0, $workMin - $restMin);
              $netWorkHour = intdiv($netWorkMin, 60);
              $netWorkMinute = $netWorkMin % 60;
              $netWorkHm = sprintf('%02d:%02d', $netWorkHour, $netWorkMinute);

              $week = (int) $date->format('w');
            @endphp

            <tr>
              <td>{{ $date->format('m/d') }}（{{ $weekdays[$week] }}）</td>

              <td>{{ $stamp?->start_work?->format('H:i') ?? ' ' }}</td>
              <td>{{ $stamp?->end_work?->format('H:i') ?? ' ' }}</td>

              <td>{{ $stamp ? $restHm : ' ' }}</td>

              <td>{{ $stamp ? $netWorkHm : ' ' }}</td>

              <td>
                @if ($stamp)
                  <a href="{{ route('admin.attendance.detail', ['stamp' => $stamp->id]) }}">詳細</a>
                @else
                  <div>詳細</div>
                @endif
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </div>
</div>
@endsection

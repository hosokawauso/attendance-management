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

              // まずデフォルト（stamp無い日）
              $restMin = 0;
              $workMin = 0;
              $restHm = ' ';
              $netWorkHm = ' ';

              if ($stamp) {
                  // 休憩（分）
                  $restMin = $stamp->restMinutes();

                  // TIME文字列を「stamp_date + time」でCarbon化して差分計算
                  if (!empty($stamp->start_work) && !empty($stamp->end_work)) {
                      $day = $date->format('Y-m-d');
                      $start = \Carbon\Carbon::parse($day.' '.$stamp->start_work, 'Asia/Tokyo');
                      $end   = \Carbon\Carbon::parse($day.' '.$stamp->end_work, 'Asia/Tokyo');
                      $workMin = $start->diffInMinutes($end);
                  }

                  $restHm = sprintf('%d:%02d', intdiv($restMin, 60), $restMin % 60);

                  $netWorkMin = max(0, $workMin - $restMin);
                  $netWorkHm = sprintf('%d:%02d', intdiv($netWorkMin, 60), $netWorkMin % 60);
              }

              $week = (int) $date->format('w');
            @endphp            <tr>
              <td>{{ $date->format('m/d') }}（{{ $weekdays[$week] }}）</td>

              <td>{{ $stamp?->start_work ? substr($stamp->start_work, 0, 5) : ' ' }}</td>
              <td>{{ $stamp?->end_work ? substr($stamp->end_work, 0, 5) : ' ' }}</td>

              <td>{{ $stamp ? $restHm : ' ' }}</td>

              <td>{{ $stamp ? $netWorkHm : ' ' }}</td>

              <td>
                @if ($stamp)
                  <a href="{{ route('admin.attendance.detail', $stamp->id) }}">詳細</a>
                @else
                  <div>詳細</div>
                @endif
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      @endif
      <a class="nav-btn"
        href="{{ route('admin.attendance.staff.monthly.csv', ['staff' => $staff->id, 'month' => $month->format('Y-m')]) }}">
        CSV出力
      </a>
    </div>
  </div>
</div>
@endsection

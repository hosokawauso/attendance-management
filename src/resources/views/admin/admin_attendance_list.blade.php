@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/admin_attendance_list.css')}}">
@endsection

@section('content')
  <div class="admin-attendance-wrap">

  {{-- タイトル --}}
  <h1 class="page-title">
    {{ $date->translatedFormat('Y年n月j日') }}の勤怠
  </h1>

  {{-- 日付ナビ --}}
  <div class="card card-nav">
    <div class="top-tabs">
      <a class="date-nav__btn"
        href="{{ route('admin.attendance.list', ['date' => $prevDate->toDateString()]) }}">
        ← 前日
      </a>

      <div class="nav-center">
        <span class="nav-icon">📅</span>
        <span class="nav-date">{{ $date->format('Y/m/d') }}</span>
      </div>

      <a class="date-nav__btn"
        href="{{ route('admin.attendance.list', ['date' => $nextDate->toDateString()]) }}">
        翌日 →
      </a>
    </div>
  </div>

  <div class="card card-table">
    <div class="table-wrapper">
      @if($stamps->count())
        <table class="month-table">
          <thead>
            <tr>
              <th>名前</th>
              <th>出勤</th>
              <th>退勤</th>
              <th>休憩</th>
              <th>合計</th>
              <th>詳細</th>
            </tr>
          </thead>
          <tbody>

{{--           @foreach($stamps as $stamp)
            @php
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
          @endphp

          <tr>
            <td>{{ $stamp?->staff?->name ?? ' ' }}</td>
            <td>{{ $stamp?->start_work?->format('H:i') ?? ' ' }}</td>
            <td>{{ $stamp?->end_work?->format('H:i') ?? ' ' }}</td>
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
        @endforeach --}}

@foreach($stamps as $stamp)
  @php
    $restMin = $stamp?->restMinutes() ?? 0;

    $workMin = 0;
    if ($stamp && $stamp->start_work && $stamp->end_work) {
        $day = $stamp->stamp_date instanceof \Carbon\Carbon
            ? $stamp->stamp_date->toDateString()
            : (string) $stamp->stamp_date;

        $start = \Carbon\Carbon::parse($day.' '.(string)$stamp->start_work, 'Asia/Tokyo');
        $end   = \Carbon\Carbon::parse($day.' '.(string)$stamp->end_work, 'Asia/Tokyo');

        // 日跨ぎがあり得るなら
        if ($end->lt($start)) {
            $end->addDay();
        }

        $workMin = $start->diffInMinutes($end);
    }

    $restHm = sprintf('%02d:%02d', intdiv($restMin, 60), $restMin % 60);

    $netWorkMin = max(0, $workMin - $restMin);
    $netWorkHm  = sprintf('%02d:%02d', intdiv($netWorkMin, 60), $netWorkMin % 60);
  @endphp

  <tr>
    <td>{{ $stamp?->staff?->name ?? ' ' }}</td>

    {{-- time型は文字列なので format() じゃなく substr() --}}
    <td>{{ $stamp?->start_work ? substr((string)$stamp->start_work, 0, 5) : ' ' }}</td>
    <td>{{ $stamp?->end_work   ? substr((string)$stamp->end_work, 0, 5) : ' ' }}</td>

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
    </div>
  </div>
</div>
@endsection
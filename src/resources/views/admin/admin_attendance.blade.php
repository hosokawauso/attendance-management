@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/admin_attendance.css')}}">
@endsection

@section('content')
<div class="detail-wrap">
  <div class="page-title" >
    <h2>勤怠詳細</h2>
  </div>

  <form action="{{ route('admin.attendance.update', $stamp->id)}}" method="POST">
    @csrf
    <div class="card card-table">
      <div class="table-wrapper">

    <table class="detail-table">

      <colgroup>
        <col class="col-th">
        <col class="col-td">
        <col class="col-err">
      </colgroup>

      <tr>
        <th>名前</th>
        <td>{{ $staff->name }}</td>
      </tr>

      <tr>
        <th>日付</th>
        <td class="date-range">
          <span class="date-year">
            {{ $stamp->stamp_date->format('Y年') }}
          </span>
          <span class="date-md">
            {{ $stamp->stamp_date->format('m月d日') }}
          </span>
        </td>
      </tr>

      <tr>
        <th>出勤・退勤</th>
        <td class="time-range">
          <div class="time-pair">
          <input type="time" name="start_work" value="{{ $stamp->start_work_carbon?->format('H:i') ?? '-' }}">
          <span class="range-sep">～</span>
          <input type="time" name="end_work" value="{{ $stamp->end_work_carbon?->format('H:i') ?? '-' }}">
          </div>
        </td>
        <td class="error-col">
          <p class="error-message">@error('start_work'){{ $message }}@enderror</p>

          <p class="error-message">@error('end_work'){{ $message }}@enderror</p>
        </td>
      </tr>

      @php
        $restCount = max(1, $rests->count());
      @endphp

      @for ($i = 0; $i < $restCount; $i++)
        @php
          $rest = $rests[$i] ?? null;
        @endphp

        <tr>
          <th>休憩{{ $i > 0 ? $i + 1 : '' }}</th>
          <td class="time-range">
            <div class="time-pair">
            <input type="time" name="rests[{{ $i }}][start]"
                  value="{{ $rest?->start_rest?->format('H:i') ?? '' }}">
            <span class="range-sep">～</span>
            <input type="time" name="rests[{{ $i }}][end]"
                  value="{{ $rest?->end_rest?->format('H:i') ?? '' }}">
            </div>
          </td>
          <td class="error-col">
            @error('rests') {{ $message }} @enderror
          </td>
        </tr>
      @endfor

        <tr>
          <th >備考</th>
          <td class="admin_comment">
            <input type="text"
            class="admin_comment-input"
            name="admin_comment"
            value="{{ old('admin_comment', $stamp->admin_comment) }}">
          </td>
          <td class="error-col">
            @error('admin_comment'){{ $message }} @enderror
          </td>
        </tr>
      </table>
    </div>
    </div>

    <div class="detail-footer">
      <button type="submit" class="correction-btn">修正</button>
    </div>

  </form>
</div>
@endsection
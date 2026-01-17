@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css')}}">
@endsection

@section('content')
<div class="detail-container">
  <div class="page-title" >
    <h2>勤怠詳細</h2>
  </div>
  <form action="{{ route( 'attendance.request', $stamp->id )}}" method="POST">
    @csrf
    <table class="detail-table">
      <tr>
        <th>名前</th>
        <td>{{ $staff->name }}</td>
      </tr>

      <tr>
        <th>日付</th>
        <td>
          {{ $stamp->stamp_date->format('Y年') }}
          {{ $stamp->stamp_date->format('m月d日') }}
        </td>
      </tr>

      <tr>
        <th>出勤・退勤</th>
        <td>
          <input type="time" name="start_work" value="{{ $stamp->start_work ? substr((string)$stamp->start_work, 0, 5) : '' }}">
          ～
          <input type="time" name="end_work" value="{{ $stamp->end_work ? substr((string)$stamp->end_work, 0, 5) : '' }}">

          @error('start_work')
            <div class="error">{{ $message }}</div>
          @enderror

          @error('end_work')
            <div class="error">{{ $message }}</div>
          @enderror
        </td>
      </tr>

      @php
        $restCount = max(1, $rests->count());
      @endphp

      @error('rests')
        <tr>
          <th></th>
          <td>
            <div class="error">{{ $message }}</div>
          </td>
        </tr>
      @enderror

      @for ($i = 0; $i < $restCount; $i++)
        @php
          $rest = $rests[$i] ?? null;
        @endphp

        <tr>
          <th>休憩{{ $i > 0 ? $i + 1 : '' }}</th>
          <td class="time-range">
            <input type="time" name="rests[{{ $i }}][start]"
                  value="{{ $rest?->start_rest?->format('H:i') ?? '' }}">
            <span class="range-sep">～</span>
            <input type="time" name="rests[{{ $i }}][end]"
                  value="{{ $rest?->end_rest?->format('H:i') ?? '' }}">
            @error("rests.$i.start")
              <div class="error">{{ $message }}</div>
            @enderror

            @error("rests.$i.end")
              <div class="error">{{ $message }}</div>
            @enderror
          </td>
        </tr>
      @endfor

        <tr>
          <th>備考</th>
          <td>
            <input type="text"
            class="remarks-input"
            name="remarks"
            value="{{ old('remarks', $stamp->remarks) }}">
      @error('remarks')
        <div class="error">{{ $message }}</div>
      @enderror
          </td>
        </tr>
    </table>
    <div class="detail-footer">
      @php
        $reqStatus = $latestReq->status ?? null;
      @endphp

      @if (is_null($reqStatus))
        <button type="submit" class="correction-btn">修正</button>
      @elseif($reqStatus === \App\Models\AttendanceCorrectRequest::STATUS_PENDING)
          <p class="pending-message">*承認待ちのため修正はできません。</p>
      @elseif ($reqStatus === \App\Models\AttendanceCorrectRequest::STATUS_APPROVED)
      <p class="approved-message">*承認済みのため修正できません。</p>
      @endif
    </div>

  </form>

</div>
@endsection
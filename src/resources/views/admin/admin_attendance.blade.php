@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css')}}">
@endsection

@section('content')
<div class="detail-container">
  <div class="page-title" >
    <h2>勤怠詳細</h2>
  </div>
  <form action="{{ route('admin.attendance.update', $stamp->id)}}" method="POST">
    @csrf
    @method('PUT')

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
          <input type="time" name="start_work" value="{{ $stamp->start_work->format('H:i') }}">
          ～
          <input type="time" name="end_work" value="{{ $stamp->end_work->format('H:i') }}">
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
          <th>休憩{{ $i + 1 }}</th>
          <td>
            <input type="time" name="rests[{{ $i }}][start]"
                  value="{{ $rest?->start_rest?->format('H:i') ?? '' }}">
                  ～
            <input type="time" name="rests[{{ $i }}][end]"
                  value="{{ $rest?->end_rest?->format('H:i') ?? '' }}">
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
      <button type="submit" class="correction-btn">修正</button>
    </div>

  </form>
</div>
@endsection
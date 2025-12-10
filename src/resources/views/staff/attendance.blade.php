@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

{{-- 状態に応じてボタンを出し分け（JSなし・1URLで完結） --}}
@if ($state === 'idle')
<div class="timestamp">
  <div class="status">勤務外</div>
  <div class="date">
    {{ $now->locale('ja')->isoFormat('YYYY年MM月DD日(ddd)')}}
  </div>
  <div class="current-time" id="current-time">
    {{-- {{ $now->format('H:i') }} --}}
  </div>

  <form method="POST" action="{{ route('attendance') }}">
    @csrf
    <button name="action" value="clock-in">出勤</button>
  </form>
</div>

@elseif ($state === 'working')
  <div class="status">出勤中</div>
  <div class="date">
    {{ $now->locale('ja')->isoFormat('YYYY年MM月DD日(ddd)')}}
  </div>
  <div class="current-time" id="current-time">
{{--     {{ $now->format('H:i') }}
 --}}  </div>

  <form method="POST" action="{{ route('attendance') }}">
    @csrf
    <button name="action" value="break-start">休憩入</button>
  </form>
  <form method="POST" action="{{ route('attendance') }}">
    @csrf
    <button name="action" value="clock-out">退勤</button>
  </form>

@elseif ($state === 'on_break')
  <div class="status">休憩中</div>
  <div class="date">
    {{ $now->locale('ja')->isoFormat('YYYY年MM月DD日(ddd)')}}
  </div>
  <div class="current-time" id="current-time">
{{--     {{ $now->format('H:i') }}
 --}}  </div>

  <form method="POST" action="{{ route('attendance') }}">
    @csrf
    <button name="action" value="break-end">休憩戻</button>
  </form>

@elseif ($state === 'finished')
  <div class="status">退勤済</div>
  <div class="date">
    {{ $now->locale('ja')->isoFormat('YYYY年MM月DD日(ddd)')}}
  </div>
  <div class="current-time" id="current-time">
{{--     {{ $now->format('H:i') }}
 --}}  </div>

  <p>お疲れさまでした。</p>
@endif

@push('script')
  <script>
    (function () {
      function showCurrentTime() {
        const time = document.getElementById('current-time');
        if(!time) return;

        const now = new Date();
        const nowHours = String(now.getHours()).padStart(2, '0');
        const nowMinutes = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');

        time.textContent = `${nowHours}:${nowMinutes}:${s}`;
      }
      showCurrentTime();

      setInterval(showCurrentTime, 1000)
    })();
  </script>
@endpush

@endsection

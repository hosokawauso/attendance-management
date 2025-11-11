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
  <div class="current-time">
    {{ $now->format('H:i') }}
  </div>

  <form method="POST" action="{{ route('attendance') }}">@csrf
    <button name="action" value="clock-in">出勤</button>
  </form>
</div>

@elseif ($state === 'working')
  <div class="status">出勤中</div>
  <div class="date">
    {{ $now->locale('ja')->isoFormat('YYYY年MM月DD日(ddd)')}}
  </div>
  <div class="current-time">
    {{ $now->format('H:i') }}
  </div>

  <form method="POST" action="{{ route('attendance') }}" style="display:inline">@csrf
    <button name="action" value="break-start">休憩入</button>
  </form>
  <form method="POST" action="{{ route('attendance') }}" style="display:inline">
    @csrf
    <button name="action" value="clock-out">退勤</button>
  </form>

@elseif ($state === 'on_break')
  <div class="status">休憩中</div>
  <div class="date">
    {{ $now->locale('ja')->isoFormat('YYYY年MM月DD日(ddd)')}}
  </div>
  <div class="current-time">
    {{ $now->format('H:i') }}
  </div>

  <form method="POST" action="{{ route('attendance') }}">
    @csrf
    <button name="action" value="break-end">休憩戻</button>
  </form>

@elseif ($state === 'finished')
  <div class="status">退勤済</div>
  <div class="date">
    {{ $now->locale('ja')->isoFormat('YYYY年MM月DD日(ddd)')}}
  </div>
  <div class="current-time">
    {{ $now->format('H:i') }}
  </div>

  <p>お疲れさまでした。</p>
@endif

{{-- @if ($stamp)
  <h3 style="margin-top:1rem;">本日のログ</h3>
  <ul>
    <li>出勤: {{ $stamp->start_work?->format('H:i') ?? '-' }}</li>
    <li>退勤: {{ $stamp->end_work?->format('H:i') ?? '-' }}</li>
  </ul>
  @if ($rests->count())
    <h4>休憩</h4>
    <ul>
      @foreach($rests as $r)
        <li>{{ $r->start_rest?->format('H:i') }} - {{ $r->end_rest?->format('H:i') ?? '...' }}</li>
      @endforeach
    </ul>
  @endif
@endif
 --}}@endsection

@extends('layouts.app')

<!-- @section('css')
<link rel="stylesheet" href="{{ asset('css/admin/stamp_correction_request_approve.css')}}">
@endsection
 -->
@section('content')
<div class="detail-wrap">
  <div class="page-title" >
    <h2>勤怠詳細</h2>
  </div>

  <div class="card card-table">
    <div class="table-wrapper">


    <table class="detail-table">
      <tr>
        <th>名前</th>
        <td>{{ $req->staff->name ?? '-' }}</td>
      </tr>

      <tr>
        <th>日付</th>
        <td class="time-range">
          <span class="date-year">
            {{ $req->stamp?->stamp_date->format('Y年') ?? '-' }}
          </span>
          <span class="date-md">
            {{ $req->stamp?->stamp_date->format('m月d日') ?? '-' }}
          </span>
        </td>
      </tr>

    @foreach($requestedRests as $i => $r)
      <tr>
        <th>出勤・退勤</th>
        <td class="time-range">
          <span>
            {{ $req->requested_start_work?->format('H:i') }}
          </span>
          <span class="range-sep">～</span>
          <span>
            {{ $req->requested_end_work?->format('H:i') }}
          </span>
        </td>
      </tr>
      <tr>
        <th>休憩{{ $i > 0 ? $i + 1 : '' }}</th>
        <td class="time-range">
          <span>{{ $r->requested_start_rest ? substr($r->requested_start_rest, 0, 5) : '-' }}</span>
          <span> ～ </span>
          <span>{{ $r->requested_end_rest ? substr($r->requested_end_rest, 0, 5) : '-' }}</span>
        @endforeach
        </td>
      </tr>

        <tr>
          <th>備考</th>
          <td>
            {{ $req->requested_remarks ?? '-' }}
          </td>
        </tr>
      </table>
  </div>
</div>

<div class="detail-footer">
  <button
    type="button"
    id="approveBtn"
    class="approve-btn {{ $req->status === \App\Models\AttendanceCorrectRequest::STATUS_PENDING ? 'is-pending' : 'is-approved' }}"
    data-url="{{ route('stamp_correction_request.approve', ['attendance_correct_request' => $req->id]) }}"
    {{ $req->status !== \App\Models\AttendanceCorrectRequest::STATUS_PENDING ? 'disabled' : '' }}
  >
    {{ $req->status === \App\Models\AttendanceCorrectRequest::STATUS_PENDING ? '承認' : '承認済み' }}
  </button>
</div>


<script>
  document.getElementById('approveBtn')?.addEventListener('click', async (e) => {
    const btn = e.currentTarget;
    const url = btn.dataset.url;
    if (!url || btn.disabled) return;

    btn.disabled = true;

    try{
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
        },
      });

      if(!res.ok) throw new Error('approve failed');
      const data = await res.json();

      btn.textContent = data.label ?? '承認済み';
    } catch (err) {
      btn.disabled = false;
      alert('承認に失敗しました');
    }
  });
</script>
@endsection
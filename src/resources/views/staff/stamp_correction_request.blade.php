@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request.css') }}">
@endsection

@section('content')
<div class="list-container">
  <h2 class="page-title">申請一覧</h2>
  <div>
    <div class="top__tabs">
      <button type="button" class="tab-btn is-active" data-tab="pending">
        承認待ち
      </button>
      <button type="button" class="tab-btn" data-tab="approved">
        承認済み
      </button>
    </div>

    <div id="tab-pending" class="tab-panel is-active">
      <table>
        <thead>
          <tr>
            <th>状態</th>
            <th>名前</th>
            <th>対象日時</th>
            <th>申請理由</th>
            <th>申請日時</th>
            <th>詳細</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pendingStamps as $stamp)
          <tr>
            <td>{{ $stamp->approved ? '承認済み' : '承認待ち' }}</td>
            <td>{{ $stamp->staff->name ?? '-' }}</td>
            <td>{{ $stamp->stamp_date?->format('Y-m-d') ?? '-' }}</td>
            <td>{{ $stamp->remarks ?? '-' }}</td>
            <td>{{ $stamp->created_at?->format('Y-m-d') ?? '-' }}</td>
            <td><a href="/attendance/detail/{{$stamp->id}}">詳細</a></td>
          </tr>
          @empty
            <tr>
              <td>承認待ちの申請はありません。</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>


    <div id="tab-approved" class="tab-panel">
    <table>
      <thead>
        <tr>
          <th>状態</th>
          <th>名前</th>
          <th>対象日時</th>
          <th>申請理由</th>
          <th>申請日時</th>
          <th>詳細</th>
        </tr>
      </thead>
      <tbody>
        @forEach($approvedStamps as $stamp)
          <tr>
            <td>{{ $stamp->approved ? '承認済み' : '承認待ち' }}</td>
            <td>{{ $stamp->staff->name ?? '-' }}</td>
            <td>{{ $stamp->stamp_date?->format('Y-m-d') ?? '-' }}</td>
            <td>{{ $stamp->remarks ?? '-' }}</td>
            <td>{{ $stamp->created_at?->format('Y-m-d') ?? '-' }}</td>
            <td><a href="/attendance/detail/{{$stamp->id}}">詳細</a></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

<script>
  const tabButtons = document.querySelectorAll('.tab-btn');
  const tabPanels  = document.querySelectorAll('.tab-panel');

  tabButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const tab = btn.getAttribute('data-tab');

      // 1) ボタンの is-active 切り替え
      tabButtons.forEach(b => b.classList.remove('is-active'));
      btn.classList.add('is-active');

      // 2) パネルの is-active 切り替え
      tabPanels.forEach(panel => panel.classList.remove('is-active'));

      if (tab === 'pending') {
        document.getElementById('tab-pending').classList.add('is-active');
      } else if (tab === 'approved') {
        document.getElementById('tab-approved').classList.add('is-active');
      }
    });
  });
</script>
  </div>
</div>


@endsection

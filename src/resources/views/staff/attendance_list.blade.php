@extends('layouts.app')

@section('css')

@endsection

@section('content')
<div class="list-container">
  <h2 class="page-title">申請一覧</h2>
  <div>
    <div class="top__tabs">
    <a class="{{ $page === 'recommend' ? 'active' : '' }}" href="/">承認待ち</a>
    <a class="{{ $page === 'mylist' ? 'active' : '' }}" href="/?page=mylist">承認済み</a>
    </div>
    <div>
      <table>
        <tr>
          <th>状態</th>
          <th>名前</th>
          <th>対象日時</th>
          <th>申請理由</th>
          <th>申請日時</th>
          <th>詳細</th>
        </tr>
        <tr>
          
        </tr>
      </table>
    </div>
  </div>
</div>
@endsection

@extends('layouts.app')

@section('css')
  <link rel="stylesheet" href="{{ asset('css/admin/staff_list.css' )}}">
@endsection

@section('content')
 <div>
  <div class="list-container">
    <h2>スタッフ一覧</h2>
  </div>
  <div>
    <table>
      <thead>
        <tr>
          <th>名前</th>
          <th>メールアドレス</th>
          <th>月次勤務</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($staffs as $staff)
        @if($staff && !$staff->is_admin)
      <tr>
        <td>{{ $staff->name }}</td>
        <td>{{ $staff->email }}</td>
        <td>
          <a href="#">詳細</a>
        </td>
      </tr>
      @endif
      @endforeach
      </tbody>

    </table>
  </div>
 </div>
@endsection
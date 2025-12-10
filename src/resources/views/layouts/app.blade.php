<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>COACHTECH勤怠管理アプリ</title>
  <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
  <link rel="stylesheet" href="{{ asset('css/header.css') }}">
  <link rel="stylesheet" href="{{ asset('css/common.css') }}">
 

  @yield('css')
</head>
<body>

<header>
    @yield('header')
    <div class="header-inner">
      <div class="logo-space" >
      <img class="logo" src="{{ asset('img/COACHTECHlogo.jpg')}}" alt="COACHTECH" >
      </div>

          <nav class="nav" aria-label="メイン">
            
      @auth
        @php 
          $user = Auth::user();
        @endphp

        @if($user->is_admin)
          {{-- 管理者メニュー --}}
          <a href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
          <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
          <a href="/stamp_correction_request/list">申請一覧</a>
        @else
          {{-- スタッフメニュー --}}
          <a href="/attendance">勤怠</a>
          <a href="/attendance/list">勤怠一覧</a>
          <a href="/stamp_correction_request/list">申請</a>
        @endif

        {{-- 共通：ログアウトリンク --}}
        <a href="#"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
          ログアウト
        </a>
      @endauth

      @guest
        {{-- 未ログイン時は何も出さなくてOK --}}
      @endguest
    </nav>
  </div>
</header>

{{-- ★ 隠しログアウトフォーム（ヘッダーの「外」に1つだけ） --}}
@auth
  <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
    @csrf
  </form>
@endauth


  <div class="content">
  @yield('content')
  <div class="container">
  </div>
  </div>
  @stack('script')
</body>
</html>

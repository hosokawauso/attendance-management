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

      <div class="nav-wrap">
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
            <form action="/admin/logout" method="POST">
              @csrf
              <input class="logout-form" type="submit" value="ログアウト">
            </form>
          @else
            {{-- スタッフメニュー --}}
            <a href="/attendance">勤怠</a>
            <a href="/attendance/list">勤怠一覧</a>
            <a href="/stamp_correction_request/list">申請</a>
            <div>
            <form action="/logout" method="POST">
              @csrf
              <input class="logout-form" type="submit" value="ログアウト">
            </form>
            </div>

          @endif
        @endauth

        @guest
          {{-- 未ログイン時は何も出さなくてOK --}}
        @endguest
      </nav>
    </div>
  </div>
</header>

  <div class="content">
  @yield('content')
  <div class="container">
  </div>
  </div>
  @stack('scripts')
</body>
</html>

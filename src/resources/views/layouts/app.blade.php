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
{{--     <div class="app">
    <header class="header">
      <a href="/">
        <div class="logo">
          <img src="{{ asset('img/COACHTECHlogo.jpg') }}" alt="COACHTECH">
        </div>
      </a>

      @auth
 --}}      {{-- ログイン中だけ表示 --}}
      <nav class="header__nav">
{{--     <a href="#">勤怠一覧</a>
    <a href="#">スタッフ一覧</a>
    <a href="#">申請一覧</a>
    <a href="#">ログアウト</a>


      </nav>
      @endauth
 --}}

<header>
    @yield('header')
    <div class="header-inner">
      <div >
      <img class="logo" src="{{ asset('img/COACHTECHlogo.jpg')}}" alt="COACHTECH" >
      </div>
      <nav class="nav" aria-label="メイン">
    <a href="#">勤怠</a>
    <a href="#">日記一覧</a>
    <a href="#">申請</a>
     <a href="#"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">ログアウト</a>
      </nav>
    </div>
  </header>
  <div class="content">
  @yield('content')
  <div class="container">
  <div class="h-12 w-2 bg-gradient-to-b from-sub-color to-main-color"></div>
  </div>
  </div>
  @stack('scripts')
</body>
</html>




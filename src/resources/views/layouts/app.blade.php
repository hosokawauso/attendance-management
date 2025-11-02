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
      <div >
      <img class="logo" src="{{ asset('img/COACHTECHlogo.jpg')}}" alt="COACHTECH" >
      </div>
    </div>
  </header>
  @yield('content')
</body>
</html>
@extends('layouts.app')

@section('content')
<div class="login-wrapper">
  <h1>管理者ログイン</h1>

  @if ($errors->any())
    <div class="error-box">
      @foreach ($errors->all() as $error)
        <p>{{ $error }}</p>
      @endforeach
    </div>
  @endif

  <form class="login-form__form" action="{{ route('login') }}" method="POST">
    @csrf

    <div class="login-form__group">
      <label class="login-form__label" for="email">メールアドレス</label>
      <input class="login-form__input"
             type="email"
             name="email"
             id="email"
             value="{{ old('email') }}"
             autocomplete="email"
             required>
      <p class="login-form__error-message">
        @error('email')
          {{ $message }}
        @enderror
      </p>
    </div>

    <div class="login-form__group">
      <label class="login-form__label" for="password">パスワード</label>
      <input class="login-form__input"
             type="password"
             name="password"
             id="password"
             autocomplete="current-password"
             required>
      <p class="login-form__error-message">
        @error('password')
          {{ $message }}
        @enderror
      </p>
    </div>

    <button class="login-form__button-submit" type="submit">
      管理者ログインする
    </button>
  </form>
</div>
@endsection

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Athena Crash Reporting')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('header')
</head>
<body>

<nav class="navbar navbar-dark navbar-expand-lg bg-body-tertiary bg-dark" data-bs-theme="dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Athena Backend</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
            @auth
            <div class="navbar-nav">
                <a class="nav-link {{ Route::is('crash.index') ? 'active' : '' }}" href="{{ route('crash.index') }}">Crash
                    Reports</a>
            </div>
            <div class="navbar-nav">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    {{ Auth::user()->username }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="{{ route('user.getInfo', ['user' => Auth::id() ]) }}">Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('auth.logout') }}" method="post">
                            @csrf
                            <button type="submit" class="dropdown-item">Logout</button>
                        </form>
                    </li>
                </ul>
            @else
                <a class="nav-link {{ Route::is('auth.login') ? 'active' : '' }}" href="{{ route('auth.login') }}">Login</a>
            @endauth
                </div>
            </div>
    </div>
</nav>

<div class="container-fluid mt-4">
    @yield('content')
</div>

<script src="{{ asset('js/app.js') }}"></script>

@stack('scripts')
</body>
</html>

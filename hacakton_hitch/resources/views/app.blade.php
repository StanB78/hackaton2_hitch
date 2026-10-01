<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HitchTracker') – HitchTracker</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="@yield('body_class')">
<header class="topbalk">
    <a class="logo" href="{{ url('/') }}">HitchTracker</a>
    @auth
        <nav>
            @if (auth()->user()->isReiziger())
                <a href="{{ route('route') }}">Nieuwe rit</a>
            @endif
            <a href="{{ route('index') }}">Mijn account</a>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="link">Uitloggen ({{ auth()->user()->name }})</button>
            </form>
        </nav>
    @endauth
</header>

<main>
    @if (session('status'))
        <div class="melding ok">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="melding fout">
            @foreach ($errors->all() as $fout)
                <div>{{ $fout }}</div>
            @endforeach
        </div>
    @endif

    @yield('content')
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@stack('scripts')
</body>
</html>

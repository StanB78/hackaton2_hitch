@extends('app')
@section('title', 'Inloggen')
@section('content')
    <link rel="stylesheet" href="{{ asset('resources/css/app.css') }}">
    <section class="kaartje smal">
        <h1>Inloggen</h1>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <label>E-mailadres
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            </label>
            <label>Wachtwoord
                <input type="password" name="password" required>
            </label>
            <button type="submit" class="knop">Inloggen</button>
        </form>
        <p>Nog geen account? <a href="{{ route('register') }}">Registreren</a></p>
    </section>
@endsection

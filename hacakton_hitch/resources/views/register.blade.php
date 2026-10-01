@extends('app')
@section('title', 'Registreren')
@section('content')
    <section class="kaartje smal">
        <h1>Account maken</h1>
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <label>Naam
                <input type="text" name="name" value="{{ old('name') }}" required autofocus>
            </label>
            <label>E-mailadres
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>
            <label>Wachtwoord (minimaal 8 tekens)
                <input type="password" name="password" required>
            </label>
            <label>Herhaal wachtwoord
                <input type="password" name="password_confirmation" required>
            </label>
            <button type="submit" class="knop">Registreren</button>
        </form>
        <p>Al een account? <a href="{{ route('login') }}">Inloggen</a></p>
    </section>
@endsection

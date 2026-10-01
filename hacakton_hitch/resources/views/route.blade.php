@extends('app')
@section('title', 'Nieuwe rit')
@section('content')
    <section class="kaartje smal">
        <h1>Waar wil je naartoe?</h1>
        <p>Vul je bestemming in. Je ziet direct de route, de geschatte reistijd en de geschatte prijs, nog voordat je instapt.</p>
        <form method="POST" action="{{ route('ritten.store') }}">
            @csrf
            <label>Bestemming
                <input type="text" name="bestemming" value="{{ old('bestemming') }}" placeholder="bijv. Rijksmuseum, Amsterdam" required autofocus>
            </label>
            <input type="hidden" name="start_lat" id="start_lat">
            <input type="hidden" name="start_lng" id="start_lng">
            <p class="klein" id="locatie-info">Je huidige locatie wordt als vertrekpunt gebruikt als je toestemming geeft.</p>
            <button type="submit" class="knop">Bereken route en prijs</button>
        </form>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/start-locatie.js') }}"></script>
@endpush

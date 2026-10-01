@extends('app')
@section('title', 'In-taxi scherm')
@section('body_class', 'taxi')
@section('content')
    <section class="kaartje">
        <h1>Rit naar {{ $rit->bestemming }}</h1>
        <p><span class="badge" data-veld="status_label">{{ $rit->status->label() }}</span></p>

        <div id="kaart" data-config='@json($kaart)'></div>

        <div id="waarschuwing" class="melding fout" @if (! $rit->op_afwijking) hidden @endif>
            ⚠ Afwijking van de geplande route
        </div>

        <dl class="cijfers groot-cijfers">
            <div><dt>Huidige prijs</dt><dd data-veld="huidige_prijs">–</dd></div>
            <div><dt>Geschatte eindprijs</dt><dd data-veld="geschatte_eindprijs">–</dd></div>
            <div><dt>Gereden</dt><dd data-veld="gereden_km">–</dd></div>
        </dl>
        <p class="klein">Dit scherm toont dezelfde gegevens als de app van de reiziger. De reiziger kan de reis zelf beëindigen.</p>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/tracking.js') }}"></script>
@endpush

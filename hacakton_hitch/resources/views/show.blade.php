@extends('app')
@use('App\Enums\RitStatus')
@section('title', 'Rit naar ' . $rit->bestemming)
@section('content')
    <section class="kaartje">
        <h1>Rit naar {{ $rit->bestemming }}</h1>
        <p><span class="badge" data-veld="status_label">{{ $rit->status->label() }}</span>
            @if ($rit->chauffeur) · Chauffeur: {{ $rit->chauffeur->name }} @endif
        </p>

        <div id="kaart" data-config='@json($kaart)'></div>

        {{-- Waarschuwing bij afwijking van de route (US-02) --}}
        <div id="waarschuwing" class="melding fout" @if (! $rit->op_afwijking) hidden @endif>
            ⚠ Let op: de taxi wijkt af van de geplande route.
        </div>

        {{-- GEPLAND: schatting vooraf (Epic 1) --}}
        @if ($rit->status === RitStatus::Gepland)
            <dl class="cijfers">
                <div><dt>Afstand</dt><dd>{{ number_format($geplandeKm, 1, ',', '.') }} km</dd></div>
                <div><dt>Geschatte reistijd</dt><dd>{{ $rit->geschatte_reistijd }} min</dd></div>
                <div><dt>Geschatte prijs</dt><dd>€ {{ number_format($rit->geschatte_kosten, 2, ',', '.') }}</dd></div>
            </dl>
            <form method="POST" action="{{ route('ritten.start', $rit) }}">
                @csrf
                <button type="submit" class="knop">Start rit</button>
            </form>
        @endif

        {{-- ONDERWEG: live volgen (Epic 2) --}}
        @if ($rit->status === RitStatus::Onderweg)
            <dl class="cijfers">
                <div><dt>Huidige prijs</dt><dd data-veld="huidige_prijs">–</dd></div>
                <div><dt>Geschatte eindprijs</dt><dd data-veld="geschatte_eindprijs">–</dd></div>
                <div><dt>Gereden</dt><dd data-veld="gereden_km">–</dd></div>
                <div><dt>Tijd</dt><dd data-veld="minuten">–</dd></div>
            </dl>
            <p class="klein">Volle lijn = gereden route, stippellijn = nog te rijden route.
                <a href="{{ route('ritten.taxi', $rit) }}" target="_blank">Open in-taxi scherm</a></p>

            @if ($kaart['demo'])
                <div class="demo">
                    <strong>Demo (alleen in debug-modus)</strong>
                    <button type="button" id="demo-start" class="knop klein-knop">Simuleer rit</button>
                    <label class="inline"><input type="checkbox" id="demo-afwijking"> afwijken van route</label>
                </div>
            @endif

            <form method="POST" action="{{ route('ritten.beeindig', $rit) }}"
                  onsubmit="return confirm('Weet je zeker dat je de reis wilt beëindigen?')">
                @csrf
                <button type="submit" class="knop gevaar groot">Reis beëindigen</button>
            </form>
        @endif

        {{-- AFGELOPEN: eindprijs en beoordeling (Epic 3) --}}
        @if ($rit->status->isEinde())
            <h2>Ritoverzicht</h2>
            <dl class="cijfers">
                <div><dt>Geschatte prijs</dt><dd>€ {{ number_format($rit->geschatte_kosten, 2, ',', '.') }}</dd></div>
                <div><dt>Eindprijs</dt><dd class="groot-getal">€ {{ number_format($rit->eindprijs, 2, ',', '.') }}</dd></div>
                <div><dt>Gereden afstand</dt><dd>{{ number_format($geredenKm, 1, ',', '.') }} km</dd></div>
                <div><dt>Reistijd</dt><dd>{{ (int) round(($rit->eind_tijd->timestamp - $rit->start_tijd->timestamp) / 60) }} min</dd></div>
            </dl>
            @if ($rit->status === RitStatus::VoortijdigBeeindigd)
                <p class="klein">Deze rit is voortijdig beëindigd; de eindprijs is berekend over het gereden deel.</p>
            @endif

            @if ($rit->status !== RitStatus::Afgerekend && auth()->id() === $rit->user_id)
                <form method="POST" action="{{ route('ritten.afrekenen', $rit) }}">
                    @csrf
                    <button type="submit" class="knop">Eindprijs bevestigen</button>
                </form>
            @endif

            @if ($rit->beoordeling)
                <h3>Je beoordeling</h3>
                <p>{{ str_repeat('★', $rit->beoordeling->score) }}{{ str_repeat('☆', 5 - $rit->beoordeling->score) }}
                    @if ($rit->beoordeling->opmerking) – {{ $rit->beoordeling->opmerking }} @endif</p>
            @elseif (auth()->id() === $rit->user_id)
                <h3>Beoordeel je rit (optioneel)</h3>
                <form method="POST" action="{{ route('beoordeling.store', $rit) }}">
                    @csrf
                    <div class="sterren">
                        @for ($i = 1; $i <= 5; $i++)
                            <label class="inline"><input type="radio" name="score" value="{{ $i }}" required> {{ $i }}</label>
                        @endfor
                    </div>
                    <label>Opmerking
                        <textarea name="opmerking" rows="3" maxlength="500">{{ old('opmerking') }}</textarea>
                    </label>
                    <button type="submit" class="knop">Beoordeling opslaan</button>
                </form>
            @endif
        @endif
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/tracking.js') }}"></script>
@endpush

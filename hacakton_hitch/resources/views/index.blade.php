@extends('app')
@section('title', 'Mijn account')
@section('content')
    <section class="kaartje">
        <h1>Mijn account</h1>
        <p>{{ auth()->user()->name }} · {{ auth()->user()->email }} · rol: {{ auth()->user()->rol }}</p>
    </section>

    <section class="kaartje">
        <h2>Mijn ritten</h2>
        @forelse ($ritten as $rit)
            <a class="rij" href="{{ route('ritten.show', $rit) }}">
                <strong>{{ $rit->bestemming }}</strong>
                <span>{{ $rit->created_at->format('d-m-Y H:i') }}</span>
                <span class="badge">{{ $rit->status->label() }}</span>
                <span>
                    @if ($rit->eindprijs !== null)
                        € {{ number_format($rit->eindprijs, 2, ',', '.') }}
                    @else
                        ± € {{ number_format($rit->geschatte_kosten, 2, ',', '.') }}
                    @endif
                </span>
            </a>
        @empty
            <p>Nog geen ritten.</p>
        @endforelse

        {{ $ritten->links() }}
    </section>
@endsection

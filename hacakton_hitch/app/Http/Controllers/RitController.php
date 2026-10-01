<?php

namespace App\Http\Controllers;

use App\Exceptions\RouteNietGevondenException;
use App\Models\Rit;
use App\Services\RitService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RitController extends Controller
{
    public function __construct(private RitService $ritten)
    {
    }

    /** "Mijn account": overzicht van eigen ritten. */
    public function index(Request $request)
    {
        $user = $request->user();

        $ritten = Rit::query()
            ->where($user->isChauffeur() ? 'chauffeur_id' : 'user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('index', compact('ritten'));
    }

    /** Epic 1: bestemming invoeren. */
    public function create(Request $request)
    {
        abort_unless($request->user()->isReiziger(), 403);

        return view('route');
    }

    /** US-01: route, reistijd en prijs berekenen. */
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isReiziger(), 403);

        $data = $request->validate([
            'bestemming' => ['required', 'string', 'min:2', 'max:255'],
            'start_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'start_lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $standaard = config('hitchtracker.standaard_startpunt');
        $lat = (float) ($data['start_lat'] ?? $standaard['lat']);
        $lng = (float) ($data['start_lng'] ?? $standaard['lng']);

        try {
            $rit = $this->ritten->plan($request->user(), $data['bestemming'], $lat, $lng);
        } catch (RouteNietGevondenException $e) {
            return back()->withInput()->withErrors(['bestemming' => $e->getMessage()]);
        }

        return redirect()->route('ritten.show', $rit);
    }

    /** Epic 1-3 op één pagina, afhankelijk van de status van de rit. */
    public function show(Rit $rit)
    {
        Gate::authorize('view', $rit);
        $rit->load(['tarief', 'beoordeling', 'chauffeur', 'reiziger']);

        return view('show', [
            'rit' => $rit,
            'kaart' => $this->ritten->kaartData($rit, 'reiziger'),
            'geplandeKm' => $rit->routes()->where('type', 'gepland')->value('afstand_km'),
            'geredenKm' => $rit->routes()->where('type', 'gereden')->value('afstand_km'),
        ]);
    }

    /** In-taxi scherm: dezelfde gegevens, grote weergave, alleen kijken. */
    public function taxiScherm(Rit $rit)
    {
        Gate::authorize('view', $rit);

        return view('taxi', [
            'rit' => $rit,
            'kaart' => $this->ritten->kaartData($rit, 'taxi'),
        ]);
    }

    public function status(Rit $rit): JsonResponse
    {
        Gate::authorize('view', $rit);

        return response()->json($this->ritten->status($rit));
    }

    public function start(Rit $rit): RedirectResponse
    {
        Gate::authorize('bedien', $rit);

        return $this->uitvoeren($rit, fn () => $this->ritten->start($rit));
    }

    /** US-02: GPS-positie ontvangen van de browser. */
    public function locatie(Request $request, Rit $rit): JsonResponse
    {
        Gate::authorize('bedien', $rit);

        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        try {
            return response()->json(
                $this->ritten->registreerLocatie($rit, (float) $data['lat'], (float) $data['lng'])
            );
        } catch (DomainException $e) {
            return response()->json(['fout' => $e->getMessage()], 409);
        }
    }

    public function beeindig(Rit $rit): RedirectResponse
    {
        Gate::authorize('bedien', $rit);

        return $this->uitvoeren($rit, fn () => $this->ritten->beeindig($rit));
    }

    public function afrekenen(Rit $rit): RedirectResponse
    {
        Gate::authorize('afrekenen', $rit);

        return $this->uitvoeren($rit, fn () => $this->ritten->afrekenen($rit));
    }

    private function uitvoeren(Rit $rit, callable $actie): RedirectResponse
    {
        try {
            $actie();
        } catch (DomainException $e) {
            return redirect()->route('ritten.show', $rit)->withErrors(['rit' => $e->getMessage()]);
        }

        return redirect()->route('ritten.show', $rit);
    }
}

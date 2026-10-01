// Kaart + live bijwerken (US-02). JavaScript toont alleen; alle berekeningen gebeuren in Laravel.
(() => {
    const el = document.getElementById('kaart');
    if (!el || typeof L === 'undefined') return;

    const cfg = JSON.parse(el.dataset.config);
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const euro = new Intl.NumberFormat('nl-NL', { style: 'currency', currency: 'EUR' });

    const formatters = {
        huidige_prijs: (v) => (v == null ? '–' : euro.format(v)),
        geschatte_eindprijs: (v) => (v == null ? '–' : euro.format(v)),
        gereden_km: (v) => v.toFixed(2).replace('.', ',') + ' km',
        minuten: (v) => Math.round(v) + ' min',
        status_label: (v) => v,
    };

    // Kaart
    const map = L.map(el);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap-bijdragers',
    }).addTo(map);

    const gepland = L.polyline(cfg.gepland, { color: '#2563eb', weight: 4, dashArray: '8 8' }).addTo(map); // nog te rijden
    const gereden = L.polyline(cfg.gereden, { color: '#111827', weight: 5 }).addTo(map);                  // gereden
    L.marker([cfg.bestemming.lat, cfg.bestemming.lng]).addTo(map).bindPopup(cfg.bestemming.naam);
    let marker = null;

    if (cfg.gepland.length) {
        map.fitBounds(gepland.getBounds(), { padding: [20, 20] });
    } else {
        map.setView([cfg.start.lat, cfg.start.lng], 13);
    }

    function render(data) {
        if (data.status !== cfg.status) {
            window.location.reload(); // fase van de rit is veranderd
            return;
        }
        for (const [sleutel, formatteer] of Object.entries(formatters)) {
            const doel = document.querySelector(`[data-veld="${sleutel}"]`);
            if (doel && data[sleutel] !== undefined) doel.textContent = formatteer(data[sleutel]);
        }
        const waarschuwing = document.getElementById('waarschuwing');
        if (waarschuwing) waarschuwing.hidden = !data.afwijking;

        gereden.setLatLngs(data.gereden_punten);
        if (data.positie) {
            if (!marker) marker = L.circleMarker(data.positie, { radius: 8, color: '#dc2626', fillOpacity: 1 }).addTo(map);
            else marker.setLatLng(data.positie);
        }
    }

    function ophalen() {
        return fetch(cfg.statusUrl, { headers: { Accept: 'application/json' } })
            .then((r) => r.json())
            .then(render)
            .catch(() => {});
    }

    function stuurPositie(lat, lng) {
        return fetch(cfg.locatieUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify({ lat, lng }),
        })
            .then((r) => r.json())
            .then((d) => { if (d.status) render(d); })
            .catch(() => {});
    }

    ophalen();
    if (cfg.status !== 'onderweg') return;

    // App en in-taxi scherm halen dezelfde gegevens op bij dezelfde backend
    setInterval(ophalen, 3000);

    // Alleen de reiziger-app stuurt GPS-posities
    let demoActief = false;
    let laatsteVerzonden = 0;
    if (cfg.modus === 'reiziger' && 'geolocation' in navigator) {
        navigator.geolocation.watchPosition(
            (p) => {
                const nu = Date.now();
                if (demoActief || nu - laatsteVerzonden < 5000) return;
                laatsteVerzonden = nu;
                stuurPositie(p.coords.latitude, p.coords.longitude);
            },
            () => {},
            { enableHighAccuracy: true }
        );
    }

    // Demo: rijdt de geplande route na (handig zonder echte GPS)
    const demoKnop = document.getElementById('demo-start');
    if (demoKnop) {
        demoKnop.addEventListener('click', () => {
            demoActief = true;
            demoKnop.disabled = true;

            const totaal = cfg.gepland.length;
            const stap = Math.max(1, Math.floor(totaal / 25));
            const indices = [];
            for (let k = 0; k < totaal; k += stap) indices.push(k);
            if (indices[indices.length - 1] !== totaal - 1) indices.push(totaal - 1);

            let i = 0;
            const timer = setInterval(() => {
                if (i >= indices.length) { clearInterval(timer); return; }
                const [lat, lng] = cfg.gepland[indices[i++]];
                const afwijking = document.getElementById('demo-afwijking').checked ? 0.01 : 0;
                stuurPositie(lat + afwijking, lng);
            }, 1500);
        });
    }
})();

// Vult het vertrekpunt in met de GPS-positie van de browser (indien toegestaan).
(() => {
    const lat = document.getElementById('start_lat');
    const lng = document.getElementById('start_lng');
    const info = document.getElementById('locatie-info');
    if (!lat || !('geolocation' in navigator)) return;

    navigator.geolocation.getCurrentPosition(
        (p) => {
            lat.value = p.coords.latitude;
            lng.value = p.coords.longitude;
            if (info) info.textContent = 'Vertrekpunt: je huidige locatie.';
        },
        () => {
            if (info) info.textContent = 'Geen toegang tot je locatie: er wordt een standaard vertrekpunt gebruikt.';
        },
        { enableHighAccuracy: true, timeout: 8000 }
    );
})();

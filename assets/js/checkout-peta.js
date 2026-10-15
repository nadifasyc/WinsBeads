// Peta pemilihan lokasi pengiriman (Leaflet + OpenStreetMap) dan ongkir.
(function () {
  const rp = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');
  const el = (id) => document.getElementById(id);
  const info = el('info-lokasi'), btnSubmit = el('btn-submit');
  const alamat = el('alamat');
  let alamatDiketik = false;
  alamat.addEventListener('input', () => { alamatDiketik = alamat.value.trim() !== ''; });

  if (typeof L === 'undefined') {
    info.textContent = 'Peta gagal dimuat. Periksa koneksi internet lalu muat ulang halaman.';
    return;
  }

  const peta = L.map('peta').setView([TOKO.lat, TOKO.lng], 12);
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
  }).addTo(peta);

  L.circleMarker([TOKO.lat, TOKO.lng], { radius: 8, color: '#9c5b86', fillColor: '#9c5b86', fillOpacity: 1 })
    .addTo(peta).bindTooltip(TOKO.nama);
  L.circle([TOKO.lat, TOKO.lng], { radius: TOKO.radius_m, color: '#9c5b86', weight: 1, fillOpacity: 0.04, interactive: false }).addTo(peta);

  let pin = null, tunggu = null, permintaanKe = 0;

  function setTotal(ongkir, jarak) {
    el('val-ongkir').textContent = ongkir === null ? '–' : rp(ongkir);
    el('ket-jarak').textContent = jarak ? '(' + jarak.toLocaleString('id-ID') + ' km)' : '';
    el('val-total').textContent = rp(SUBTOTAL + (ongkir || 0));
  }

  function tandai(lat, lng, isiAlamat) {
    if (!pin) {
      pin = L.marker([lat, lng], { draggable: true }).addTo(peta);
      pin.on('dragend', () => { const p = pin.getLatLng(); tandai(p.lat, p.lng, true); });
    } else {
      pin.setLatLng([lat, lng]);
    }
    el('lat').value = lat.toFixed(6);
    el('lng').value = lng.toFixed(6);
    hitung(lat, lng);
    if (isiAlamat) isiAlamatDariPeta(lat, lng);
  }

  async function hitung(lat, lng) {
    const ke = ++permintaanKe;
    btnSubmit.disabled = true;
    info.className = 'map-info';
    info.textContent = 'Menghitung ongkir…';
    try {
      const r = await fetch('ongkir-hitung.php?lat=' + lat + '&lng=' + lng, { credentials: 'same-origin' });
      const d = await r.json();
      if (ke !== permintaanKe) return;           // ada klik yang lebih baru
      if (d.ok) {
        setTotal(d.ongkir, d.jarak_km);
        info.className = 'map-info ok';
        info.textContent = 'Jarak ±' + d.jarak_km.toLocaleString('id-ID') + ' km dari toko · ongkir ' + rp(d.ongkir);
        btnSubmit.disabled = false;
      } else {
        setTotal(null, d.jarak_km || 0);
        info.className = 'map-info bad';
        info.textContent = d.pesan || 'Ongkir tidak dapat dihitung.';
      }
    } catch (e) {
      if (ke !== permintaanKe) return;
      setTotal(null, 0);
      info.className = 'map-info bad';
      info.textContent = 'Gagal menghitung ongkir. Coba lagi.';
    }
  }

  // Alamat otomatis dari titik (OSM Nominatim); hanya mengisi jika pembeli belum mengetik sendiri
  function isiAlamatDariPeta(lat, lng) {
    clearTimeout(tunggu);
    tunggu = setTimeout(async () => {
      if (alamatDiketik) return;
      try {
        const r = await fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=id&lat=' + lat + '&lon=' + lng);
        const d = await r.json();
        if (d && d.display_name && !alamatDiketik) alamat.value = d.display_name;
      } catch (e) {
    }, 600);
  }

  peta.on('click', (ev) => tandai(ev.latlng.lat, ev.latlng.lng, true));

  async function cari() {
    const q = el('cari').value.trim();
    if (!q) return;
    info.className = 'map-info';
    info.textContent = 'Mencari…';
    try {
      const r = await fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&countrycodes=id&accept-language=id&q=' + encodeURIComponent(q));
      const d = await r.json();
      if (!d.length) { info.className = 'map-info bad'; info.textContent = 'Tempat tidak ditemukan. Coba kata kunci lain atau klik langsung di peta.'; return; }
      const lat = parseFloat(d[0].lat), lng = parseFloat(d[0].lon);
      peta.setView([lat, lng], 16);
      tandai(lat, lng, true);
    } catch (e) {
      info.className = 'map-info bad';
      info.textContent = 'Pencarian gagal. Klik langsung di peta.';
    }
  }
  el('btn-cari').addEventListener('click', cari);
  el('cari').addEventListener('keydown', (ev) => { if (ev.key === 'Enter') { ev.preventDefault(); cari(); } });

  el('btn-lokasi').addEventListener('click', () => {
    if (!navigator.geolocation) { info.className = 'map-info bad'; info.textContent = 'Browser tidak mendukung deteksi lokasi.'; return; }
    info.className = 'map-info';
    info.textContent = 'Mendeteksi lokasi…';
    navigator.geolocation.getCurrentPosition(
      (p) => { peta.setView([p.coords.latitude, p.coords.longitude], 16); tandai(p.coords.latitude, p.coords.longitude, true); },
      () => { info.className = 'map-info bad'; info.textContent = 'Izin lokasi ditolak. Klik langsung di peta.'; },
      { enableHighAccuracy: true, timeout: 10000 }
    );
  });

  el('form-checkout').addEventListener('submit', (ev) => {
    if (!el('lat').value || !el('lng').value) { ev.preventDefault(); info.className = 'map-info bad'; info.textContent = 'Tandai lokasi pengiriman di peta dulu.'; }
  });
})();

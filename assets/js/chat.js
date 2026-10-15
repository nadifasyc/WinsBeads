(function () {
  const cfg = window.CHAT;
  if (!cfg) return;
  const $ = (id) => document.getElementById(id);
  const log = $('chat-log'), form = $('chat-form'), input = $('chat-input'),
        kirim = $('chat-kirim'), info = $('chat-info'), konteks = $('chat-konteks');
  const saya = cfg.peran;                       // 'user' atau 'admin'
  const bubbles = new Map();                    // id pesan -> elemen
  let terakhirId = 0, terbacaSampai = 0, produkId = cfg.produkId || null, adaError = false;

  const el = (tag, kelas, teks) => {
    const n = document.createElement(tag);
    if (kelas) n.className = kelas;
    if (teks !== undefined) n.textContent = teks;
    return n;
  };

  const daftar = cfg.daftarEl ? $(cfg.daftarEl) : null;
  async function muatDaftar() {
    const r = await fetch(cfg.api + '?aksi=daftar', { credentials: 'same-origin' });
    const d = await r.json();
    if (!d.ok) return;
    daftar.replaceChildren();
    if (!d.daftar.length) { daftar.append(el('div', 'chat-kosong-list', 'Belum ada percakapan.')); return; }
    d.daftar.forEach((c) => {
      const a = el('a', 'konv' + (c.id === cfg.userId ? ' aktif' : ''));
      a.href = 'chat.php?u=' + c.id;
      const atas = el('div', 'konv-atas');
      atas.append(el('strong', '', '@' + c.username), el('span', 'konv-waktu', c.waktu));
      const bawah = el('div', 'konv-bawah');
      bawah.append(el('span', 'konv-isi', (c.saya ? 'Anda: ' : '') + c.terakhir));
      if (c.belum > 0) bawah.append(el('span', 'nav-badge', c.belum));
      a.append(atas, bawah);
      daftar.append(a);
    });
  }
  if (daftar) {
    const putar = () => setTimeout(async () => { try { await muatDaftar(); } catch (e) {} putar(); }, document.hidden ? 15000 : 5000);
    putar();
  }

  if (!log) return;

  function tandaBaca(id) { return id <= terbacaSampai ? 'dibaca' : 'terkirim'; }

  function gambar(m) {
    if (bubbles.has(m.id)) return;
    const milikku = m.dari === saya;
    const b = el('div', 'bubble ' + (milikku ? 'bubble-saya' : 'bubble-lawan'));
    if (m.produk) {
      const a = el('a', 'bubble-produk');
      a.href = (saya === 'admin' ? '../' : '') + m.produk.url;
      a.target = '_blank'; a.rel = 'noopener';
      a.append(el('i', 'fas fa-gem'), document.createTextNode(' ' + m.produk.nama));
      b.append(a);
    }
    b.append(el('div', 'bubble-teks', m.isi));
    const meta = el('div', 'bubble-meta', m.waktu);
    if (milikku) {
      const c = el('span', 'tick ' + tandaBaca(m.id), tandaBaca(m.id) === 'dibaca' ? '✓✓' : '✓');
      c.dataset.id = m.id;
      meta.append(' ', c);
    }
    b.append(meta);
    bubbles.set(m.id, b);
    log.append(b);
    if (m.id > terakhirId) terakhirId = m.id;
  }

  function perbaruiCentang() {
    log.querySelectorAll('.tick').forEach((c) => {
      if (+c.dataset.id <= terbacaSampai && !c.classList.contains('dibaca')) {
        c.className = 'tick dibaca'; c.textContent = '✓✓';
      }
    });
  }

  const dekatBawah = () => log.scrollHeight - log.scrollTop - log.clientHeight < 80;
  const keBawah = () => { log.scrollTop = log.scrollHeight; };

  function kosongkan() {
    const k = $('chat-kosong');
    if (k) k.hidden = bubbles.size > 0;
  }

  function params(ekstra) {
    const p = new URLSearchParams(ekstra || {});
    if (cfg.userId) p.set('user_id', cfg.userId);
    return p;
  }

  async function ambil() {
    const p = params({ after: terakhirId });
    const r = await fetch(cfg.api + '?' + p.toString(), { credentials: 'same-origin' });
    if (r.status === 401) { location.reload(); return; }
    const d = await r.json();
    if (!d.ok) throw new Error(d.galat || 'Gagal memuat pesan');
    const turun = dekatBawah() || terakhirId === 0;
    d.pesan.forEach(gambar);
    terbacaSampai = Math.max(terbacaSampai, d.terbaca_sampai || 0);
    perbaruiCentang();
    kosongkan();
    if (d.pesan.length && turun) keBawah();
    if (adaError) { adaError = false; info.textContent = ''; info.className = 'chat-info'; }
  }

  function jadwal() {
    // Tab tidak terlihat: polling dilambatkan supaya hemat server
    const jeda = document.hidden ? 15000 : (adaError ? 8000 : 3000);
    setTimeout(async () => {
      try { await ambil(); } catch (e) {
        adaError = true; info.className = 'chat-info bad'; info.textContent = 'Koneksi terputus, mencoba lagi…';
      }
      jadwal();
    }, jeda);
  }

  form.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    const isi = input.value.trim();
    if (!isi || kirim.disabled) return;
    kirim.disabled = true;
    const fd = new FormData();
    fd.set('csrf_token', cfg.csrf);
    fd.set('isi', isi);
    if (produkId) fd.set('produk_id', produkId);
    if (cfg.userId) fd.set('user_id', cfg.userId);
    try {
      const r = await fetch(cfg.api, { method: 'POST', body: fd, credentials: 'same-origin' });
      const d = await r.json().catch(() => ({ ok: false, galat: 'Sesi berakhir. Muat ulang halaman.' }));
      if (!d.ok) throw new Error(d.galat || 'Gagal mengirim');
      gambar(d.pesan);
      kosongkan();
      keBawah();
      input.value = '';
      input.style.height = '';
      info.textContent = ''; info.className = 'chat-info';
      if (produkId) { produkId = null; if (konteks) konteks.hidden = true; }
    } catch (e) {
      info.className = 'chat-info bad';
      info.textContent = e.message;
    } finally {
      kirim.disabled = false;
      input.focus();
    }
  });

  input.addEventListener('keydown', (ev) => {
    if (ev.key === 'Enter' && !ev.shiftKey && !ev.isComposing) { ev.preventDefault(); form.requestSubmit(); }
  });
  input.addEventListener('input', () => {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 140) + 'px';
  });

  const batal = $('chat-konteks-batal');
  if (batal) batal.addEventListener('click', () => { produkId = null; konteks.hidden = true; });

  ambil().catch(() => { info.className = 'chat-info bad'; info.textContent = 'Gagal memuat percakapan. Muat ulang halaman.'; })
         .finally(() => { kosongkan(); keBawah(); jadwal(); });
})();

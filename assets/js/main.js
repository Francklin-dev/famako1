/* assets/js/main.js */
document.addEventListener('DOMContentLoaded', function () {

  // ── LANGUE ──────────────────────────────────────────────
  const savedLang = localStorage.getItem('famako-lang') || 'fr';
  function setLang(lang) {
    document.getElementById('htmlRoot')?.setAttribute('lang', lang);
    document.querySelectorAll('.lang-btn').forEach(btn => {
      btn.classList.toggle('active',
        (btn.id === 'langFr' && lang === 'fr') ||
        (btn.id === 'langEn' && lang === 'en'));
    });
    localStorage.setItem('famako-lang', lang);
  }
  document.getElementById('langFr')?.addEventListener('click', () => setLang('fr'));
  document.getElementById('langEn')?.addEventListener('click', () => setLang('en'));
  setLang(savedLang);

  // ── CHAT ────────────────────────────────────────────────
  const chatBtn  = document.getElementById('chatButton');
  const chatBox  = document.getElementById('chatBox');
  const closeBtn = document.getElementById('closeChat');
  const sendBtn  = document.getElementById('sendChat');
  const input    = document.getElementById('chatInput');
  const msgs     = document.getElementById('chatMessages');

  function addMsg(text, who) {
    if (!msgs) return;
    const d = document.createElement('div');
    d.className = 'chat-msg ' + who;
    d.textContent = text;
    msgs.appendChild(d);
    msgs.scrollTop = msgs.scrollHeight;
  }

  const lang = localStorage.getItem('famako-lang') || 'fr';
  addMsg(lang === 'fr'
    ? 'Bonjour ! Comment puis-je vous aider ?'
    : 'Hello! How can I help you?', 'bot');

  chatBtn?.addEventListener('click', () => chatBox?.classList.toggle('open'));
  closeBtn?.addEventListener('click', () => chatBox?.classList.remove('open'));

  function sendMsg() {
    const val = input?.value.trim();
    if (!val) return;
    addMsg(val, 'user');
    input.value = '';
    setTimeout(() => addMsg(
      lang === 'fr'
        ? 'Merci ! Un conseiller vous répondra bientôt.'
        : 'Thanks! An advisor will reply soon.', 'bot'), 700);
  }
  sendBtn?.addEventListener('click', sendMsg);
  input?.addEventListener('keydown', e => { if (e.key === 'Enter') sendMsg(); });

  // ── SCROLL TOP ──────────────────────────────────────────
  const scrollBtn = document.getElementById('scrollTopBtn');
  window.addEventListener('scroll', () => {
    scrollBtn?.classList.toggle('visible', window.scrollY > 400);
  });
  scrollBtn?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

  // ── FILE UPLOAD ZONES ────────────────────────────────────
  document.querySelectorAll('.file-upload-zone').forEach(zone => {
    const input = zone.querySelector('input[type="file"]');
    const label = zone.querySelector('.upload-filename');
    zone.addEventListener('click', () => input?.click());
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', e => {
      e.preventDefault();
      zone.classList.remove('dragover');
      if (input && e.dataTransfer.files.length) {
        input.files = e.dataTransfer.files;
        if (label) label.textContent = e.dataTransfer.files[0].name;
      }
    });
    input?.addEventListener('change', () => {
      if (label && input.files.length) label.textContent = input.files[0].name;
    });
  });

  // ── ADMIN SIDEBAR TOGGLE (mobile) ───────────────────────
  document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    document.getElementById('adminSidebar')?.classList.toggle('open');
  });

  // ── COUNTER ANIMATION ───────────────────────────────────
  const counters = document.querySelectorAll('[data-count]');
  if (counters.length) {
    const obs = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        const target = parseInt(el.dataset.count);
        const duration = 1600;
        const start = performance.now();
        function tick(now) {
          const progress = Math.min((now - start) / duration, 1);
          el.textContent = Math.floor(progress * target);
          if (progress < 1) requestAnimationFrame(tick);
          else el.textContent = target;
        }
        requestAnimationFrame(tick);
        obs.unobserve(el);
      });
    }, { threshold: 0.5 });
    counters.forEach(c => obs.observe(c));
  }

});

/* C Beauty - Shared Navigation & Footer */
(function () {
  const pages = [
    { label: 'الرئيسية', href: 'index.html' },
    { label: 'من نحن', href: 'about.html' },
    { label: 'خدماتنا', href: 'services.html' },
    { label: 'منتجاتنا', href: 'products.html' },
    { label: 'المدونة', href: 'blog.html' },
    { label: 'برنامج السفراء', href: 'ambassador.html' },
    { label: 'تواصل معنا', href: 'contact.html' },
  ];

  function getCurrentPage() {
    const path = window.location.pathname.split('/').pop() || 'index.html';
    return path;
  }

  function isActive(href) {
    const current = getCurrentPage();
    const target = href.replace('/', '');
    if (current === '' || current === 'index.html') return target === 'index.html';
    return current === target;
  }

  /* ── NAV ── */
  function renderNav() {
    const placeholder = document.getElementById('nav-placeholder');
    if (!placeholder) return;

    const linksHTML = pages.map(p =>
      `<li><a href="${p.href}" class="${isActive(p.href) ? 'active' : ''}">${p.label}</a></li>`
    ).join('');

    const mobileLinksHTML = pages.map(p =>
      `<a href="${p.href}" class="${isActive(p.href) ? 'active' : ''}">${p.label}</a>`
    ).join('');

    placeholder.innerHTML = `
      <nav id="cb-nav">
        <a href="index.html" class="cb-nav-logo">
          <span>✦</span> C Beauty
        </a>
        <ul class="cb-nav-links">
          ${linksHTML}
          <li><a href="booking.html" class="cb-nav-cta">احجزي موعدك ✨</a></li>
        </ul>
        <div class="cb-hamburger" id="cb-hamburger" onclick="cbToggleMenu()">
          <span></span><span></span><span></span>
        </div>
      </nav>
      <div class="cb-mobile-menu" id="cb-mobile-menu">
        ${mobileLinksHTML}
        <a href="booking.html" style="background:linear-gradient(135deg,#D4A843,#C8722A);color:#fff;font-weight:700;text-align:center;border-radius:12px;margin-top:0.5rem;">احجزي موعدك ✨</a>
      </div>
    `;

    window.cbToggleMenu = function () {
      document.getElementById('cb-mobile-menu').classList.toggle('open');
    };

    window.addEventListener('scroll', () => {
      const nav = document.getElementById('cb-nav');
      if (nav) nav.classList.toggle('scrolled', window.scrollY > 50);
    });
  }

  /* ── FOOTER ── */
  function renderFooter() {
    const placeholder = document.getElementById('footer-placeholder');
    if (!placeholder) return;

    placeholder.innerHTML = `
      <footer id="cb-footer">
        <div class="cb-footer-grid">
          <div class="cb-footer-brand">
            <h2>✦ C Beauty</h2>
            <p>سي بيوتي — وجهتك الأولى للعناية بالبشرة والجمال. نجمع بين أحدث علوم البشرة والمكونات الطبيعية لنقدم لكِ نتائج حقيقية وملموسة.</p>
            <div class="cb-social-links" style="margin-top:1.5rem;">
              <a href="https://instagram.com/cbeautysd" target="_blank" title="Instagram">📸</a>
              <a href="https://tiktok.com/@cbeautysd" target="_blank" title="TikTok">🎵</a>
              <a href="https://snapchat.com/add/cbeautysd" target="_blank" title="Snapchat">👻</a>
              <a href="https://facebook.com/cbeautysd" target="_blank" title="Facebook">📘</a>
              <a href="https://wa.me/249123456789" target="_blank" title="WhatsApp">💬</a>
            </div>
          </div>
          <div class="cb-footer-col">
            <h4>روابط سريعة</h4>
            <ul>
              <li><a href="index.html">الرئيسية</a></li>
              <li><a href="about.html">من نحن</a></li>
              <li><a href="services.html">خدماتنا</a></li>
              <li><a href="products.html">منتجاتنا</a></li>
              <li><a href="blog.html">المدونة</a></li>
              <li><a href="ambassador.html">برنامج السفراء</a></li>
            </ul>
          </div>
          <div class="cb-footer-col">
            <h4>خدماتنا</h4>
            <ul>
              <li><a href="services.html">تحليل البشرة</a></li>
              <li><a href="services.html">علاج حب الشباب</a></li>
              <li><a href="services.html">علاج التصبغات</a></li>
              <li><a href="services.html">برامج مكافحة الشيخوخة</a></li>
              <li><a href="services.html">استشارة أونلاين</a></li>
              <li><a href="booking.html">احجزي موعدك</a></li>
            </ul>
          </div>
          <div class="cb-footer-col cb-footer-contact">
            <h4>تواصلي معنا</h4>
            <p><span>📱</span> <a href="https://wa.me/249123456789" style="color:rgba(255,255,255,0.7);text-decoration:none;">+249 123 456 789</a></p>
            <p><span>📧</span> <a href="mailto:hello@cbeautysd.com" style="color:rgba(255,255,255,0.7);text-decoration:none;">hello@cbeautysd.com</a></p>
            <p><span>📍</span> السودان والإمارات العربية المتحدة</p>
            <p><span>🕐</span> السبت – الخميس: 9 ص – 9 م</p>
          </div>
        </div>
        <div class="cb-footer-bottom">
          <span>© 2025 C Beauty — جميع الحقوق محفوظة</span>
          <span>صُنع بـ ❤️ لكل امرأة تستحق الجمال</span>
        </div>
      </footer>
      <a href="https://wa.me/249123456789" class="cb-whatsapp-float" target="_blank" title="تواصل عبر واتساب">💬</a>
    `;
  }

  /* ── ANIMATIONS ── */
  function initAnimations() {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.1 });
    document.querySelectorAll('.cb-fade-in').forEach(el => io.observe(el));
  }

  /* ── INIT ── */
  document.addEventListener('DOMContentLoaded', () => {
    renderNav();
    renderFooter();
    initAnimations();
  });
})();

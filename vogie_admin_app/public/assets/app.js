document.addEventListener('DOMContentLoaded', () => {
  const body = document.body;
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const btnOpen = document.getElementById('sidebarOpenBtn');
  const btnToggle = document.getElementById('sidebarToggle');

  window.toggleSidebar = function toggleSidebar() {
    body.classList.toggle('sidebar-collapsed');
    try {
      localStorage.setItem('sidebarCollapsed', body.classList.contains('sidebar-collapsed') ? '1' : '0');
    } catch (_) {}
  };

  window.hideSidebar = function hideSidebar() {
    const isHidden = body.classList.toggle('sidebar-hidden');
    try { localStorage.setItem('sidebarHidden', isHidden ? '1' : '0'); } catch (_) {}
    if (!isHidden) {

      overlay && overlay.classList.remove('show');
      sidebar && sidebar.classList.remove('open');
    }
  };

  body.style.opacity = '0';
  setTimeout(() => {
    body.style.transition = 'opacity 0.5s ease';
    body.style.opacity = '1';
  }, 100);

  try {
    const saved = localStorage.getItem('sidebarCollapsed');
    if (saved === '1') {
      body.classList.add('sidebar-collapsed');
    }
    const savedHidden = localStorage.getItem('sidebarHidden');
    if (savedHidden === '1') {
      body.classList.add('sidebar-hidden');
    }
  } catch (_) {}

  function openSidebar() {
    if (!sidebar) return;
    sidebar.classList.add('open');
    overlay && overlay.classList.add('show');

    sidebar.style.animation = 'slideInLeft 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
  }

  function closeSidebar() {
    if (!sidebar) return;
    sidebar.style.animation = 'slideOutLeft 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
    setTimeout(() => {
      sidebar.classList.remove('open');
      overlay && overlay.classList.remove('show');
    }, 250);
  }

  btnOpen && btnOpen.addEventListener('click', openSidebar);
  overlay && overlay.addEventListener('click', closeSidebar);

  btnToggle && btnToggle.addEventListener('click', () => {
    body.classList.toggle('sidebar-collapsed');

    btnToggle.style.animation = 'pulse 0.3s ease';
    setTimeout(() => btnToggle.style.animation = '', 300);

    try {
      localStorage.setItem('sidebarCollapsed', body.classList.contains('sidebar-collapsed') ? '1' : '0');
    } catch (_) {}
  });

  const params = new URLSearchParams(location.search);
  const page = params.get('page') || 'dashboard';
  document.querySelectorAll('.sidebar .menu a').forEach(a => {
    const href = new URL(a.getAttribute('href'), location.origin);
    const p = href.searchParams.get('page') || 'dashboard';
    if (p === page) {
      a.classList.add('active');
      a.style.boxShadow = '0 0 20px rgba(255,255,255,0.3)';
    }
  });

  const els = document.querySelectorAll('.stats-card, .modern-card, .card, .table, h1, h2, h3, h4, h5');
  els.forEach((el, index) => {
    el.classList.add('reveal');
    el.style.animationDelay = `${index * 0.1}s`;
  });

  const io = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        setTimeout(() => {
          e.target.classList.add('show');
        }, 50);
        io.unobserve(e.target);
      }
    });
  }, { rootMargin: '0px 0px -10% 0px' });

  els.forEach(el => io.observe(el));

  document.querySelectorAll('.btn').forEach(btn => {
    btn.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-2px) scale(1.02)';
    });

    btn.addEventListener('mouseleave', function() {
      this.style.transform = 'translateY(0) scale(1)';
    });

    btn.addEventListener('click', function() {
      this.style.animation = 'buttonClick 0.3s ease';
      setTimeout(() => this.style.animation = '', 300);
    });
  });

  document.querySelectorAll('.table tbody tr').forEach((row, index) => {
    row.style.animationDelay = `${index * 0.05}s`;
    row.classList.add('reveal');

    row.addEventListener('mouseenter', function() {
      this.style.transform = 'scale(1.02)';
      this.style.boxShadow = '0 4px 20px rgba(255,255,255,0.1)';
    });

    row.addEventListener('mouseleave', function() {
      this.style.transform = 'scale(1)';
      this.style.boxShadow = 'none';
    });
  });

  document.querySelectorAll('.form-control, .form-select').forEach(input => {
    input.addEventListener('focus', function() {
      this.style.transform = 'scale(1.02)';
      this.style.boxShadow = '0 0 20px rgba(255,255,255,0.2)';
    });

    input.addEventListener('blur', function() {
      this.style.transform = 'scale(1)';
      this.style.boxShadow = 'none';
    });
  });

  document.querySelectorAll('.stats-number').forEach(stat => {
    const finalValue = stat.textContent.replace(/[^\d.]/g, '');
    if (finalValue && !isNaN(finalValue)) {
      const duration = 2000;
      const start = 0;
      const end = parseFloat(finalValue);
      const startTime = performance.now();

      function animate(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const current = start + (end - start) * easeOutQuart(progress);

        if (stat.textContent.includes('MAD')) {
          stat.textContent = `MAD ${current.toLocaleString('fr-FR', {maximumFractionDigits: 2})}`;
        } else {
          stat.textContent = Math.floor(current).toLocaleString();
        }

        if (progress < 1) {
          requestAnimationFrame(animate);
        }
      }

      function easeOutQuart(t) {
        return 1 - (--t) * t * t * t;
      }

      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            requestAnimationFrame(animate);
            observer.unobserve(entry.target);
          }
        });
      });

      observer.observe(stat);
    }
  });

  function wireStripeToggle() {
    document.querySelectorAll('form input[name="stripe_payment_id"]').forEach(input => {
      const form = input.closest('form');
      const mode = form && form.querySelector('select[name="mode"]');
      if (!mode) return;

      const update = () => {
        const isStripe = mode.value === 'stripe';
        if (isStripe) {
          input.style.opacity = '0';
          input.style.transform = 'translateX(-20px)';
          input.disabled = false;
          input.classList.remove('d-none');
          setTimeout(() => {
            input.style.transition = 'all 0.3s ease';
            input.style.opacity = '1';
            input.style.transform = 'translateX(0)';
          }, 50);
        } else {
          input.style.transition = 'all 0.3s ease';
          input.style.opacity = '0';
          input.style.transform = 'translateX(-20px)';
          setTimeout(() => {
            input.disabled = true;
            input.classList.add('d-none');
          }, 300);
        }
      };

      mode.addEventListener('change', update);
      update();
    });
  }
  wireStripeToggle();

  const style = document.createElement('style');
  style.textContent = `
    @keyframes slideInLeft {
      from { transform: translateX(-100%); }
      to { transform: translateX(0); }
    }

    @keyframes slideOutLeft {
      from { transform: translateX(0); }
      to { transform: translateX(-100%); }
    }

    @keyframes buttonClick {
      0% { transform: scale(1); }
      50% { transform: scale(0.95); }
      100% { transform: scale(1); }
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .reveal {
      animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }
  `;
  document.head.appendChild(style);
});
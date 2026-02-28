/**
 * Kvadratyra — Main JS (Vite bundled)
 * Matches current theme templates: header burger, FAQ accordion, reviews slider, quiz calc, TOC, scroll-to-top.
 */

import './main.css';
import IMask from 'imask';
import { calcConfig } from './calc-config.js';

function onReady(fn) {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fn, { once: true });
  } else {
    fn();
  }
}

function initHeaderBurger() {
  const burger = document.getElementById('kv-burger');
  const nav = document.getElementById('kv-nav');
  if (!burger || !nav) return;

  function setOpen(open) {
    burger.classList.toggle('is-active', open);
    nav.classList.toggle('is-open', open);
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    document.body.classList.toggle('menu-open', open);
  }

  burger.addEventListener('click', () => setOpen(!nav.classList.contains('is-open')));
  nav.querySelectorAll('a').forEach((a) => a.addEventListener('click', () => setOpen(false)));
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') setOpen(false);
  });
}

function initScrollTop() {
  const btn = document.getElementById('kv-scroll-top');
  if (!btn) return;

  const onScroll = () => {
    btn.classList.toggle('is-visible', window.scrollY > 400);
  };

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

function initCountupStats() {
  const nodes = Array.from(document.querySelectorAll('[data-countup]'));
  if (!nodes.length) return;
  const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function animateNode(el) {
    if (!el || el.dataset.counted === '1') return;
    const target = Number(el.getAttribute('data-countup') || '0');
    const suffix = el.getAttribute('data-suffix') || '';
    if (!Number.isFinite(target)) return;
    if (reduce) {
      el.textContent = `${target}${suffix}`;
      el.dataset.counted = '1';
      return;
    }
    const duration = 700;
    const start = performance.now();
    const tick = (now) => {
      const p = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - p, 3);
      const val = Math.round(target * eased);
      el.textContent = `${val}${suffix}`;
      if (p < 1) requestAnimationFrame(tick);
      else el.dataset.counted = '1';
    };
    requestAnimationFrame(tick);
  }

  if (!('IntersectionObserver' in window)) {
    nodes.forEach(animateNode);
    return;
  }
  const io = new IntersectionObserver((entries, obs) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      animateNode(entry.target);
      obs.unobserve(entry.target);
    });
  }, { threshold: 0.4 });
  nodes.forEach((node) => io.observe(node));
}

function kvMetrikaGoal(goal, params = {}) {
  const id = window.kvData && window.kvData.metrikaId ? Number(window.kvData.metrikaId) : 0;
  if (!id || typeof window.ym !== 'function') return;
  try {
    window.ym(id, 'reachGoal', goal, params);
  } catch (e) {
    // no-op
  }
}

function initMetrikaEvents() {
  // Prevent duplicate goal bindings if another script (theme inline fallback) already bound them.
  if (window.__kvMetrikaBound) return;
  window.__kvMetrikaBound = true;

  // Phone clicks
  document.querySelectorAll('a[href^="tel:"]').forEach((a) => {
    a.addEventListener('click', () => kvMetrikaGoal('phone_click'));
  });

  // Telegram clicks (any t.me link)
  document.querySelectorAll('a[href*="t.me/"]').forEach((a) => {
    a.addEventListener('click', () => kvMetrikaGoal('tg_click'));
  });

  // Primary CTA in header
  document.querySelectorAll('.kv-header-cta').forEach((a) => {
    a.addEventListener('click', () => kvMetrikaGoal('cta_header_call'));
  });

  // Calculator wizard submit — goal is fired inside initWizard()/initMaterialCalcs()

  // FAQ opens (track only when expanding)
  document.querySelectorAll('[data-faq-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
      const item = toggle.closest('.faq-item');
      const willOpen = item && !item.classList.contains('is-open');
      if (!willOpen) return;
      const title = (toggle.textContent || '').trim().slice(0, 120);
      kvMetrikaGoal('faq_open', { q: title });
    });
  });

  // Section views (first time)
  const ids = ['features', 'services', 'how-it-works', 'faq', 'reviews', 'blog', 'calculator', 'geography', 'expert', 'answer-first'];
  const seen = new Set();
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((e) => {
          if (!e.isIntersecting) return;
          const id = e.target && e.target.id ? e.target.id : '';
          if (!id || seen.has(id)) return;
          seen.add(id);
          kvMetrikaGoal('view_' + id);
        });
      },
      { threshold: 0.25 }
    );
    ids.forEach((id) => {
      const el = document.getElementById(id);
      if (el) io.observe(el);
    });
  }
}

function initFaqAccordion() {
  const toggles = document.querySelectorAll('[data-faq-toggle]');
  if (!toggles.length) return;

  toggles.forEach((toggle) => {
    const item = toggle.closest('.faq-item');
    const answer = item ? item.querySelector('.faq-a') : null;
    if (!item || !answer) return;

    // initial collapsed state
    answer.style.maxHeight = '0px';

    function close() {
      item.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
      answer.style.maxHeight = '0px';
    }

    function open() {
      // close other items (accordion behavior)
      document.querySelectorAll('.faq-item.is-open').forEach((other) => {
        if (other === item) return;
        const otherToggle = other.querySelector('[data-faq-toggle]');
        const otherAnswer = other.querySelector('.faq-a');
        other.classList.remove('is-open');
        if (otherToggle) otherToggle.setAttribute('aria-expanded', 'false');
        if (otherAnswer) otherAnswer.style.maxHeight = '0px';
      });

      item.classList.add('is-open');
      toggle.setAttribute('aria-expanded', 'true');
      answer.style.maxHeight = `${answer.scrollHeight}px`;
    }

    toggle.addEventListener('click', (e) => {
      e.preventDefault();
      item.classList.contains('is-open') ? close() : open();
    });

    toggle.addEventListener('keydown', (e) => {
      if (e.key !== 'Enter' && e.key !== ' ') return;
      e.preventDefault();
      item.classList.contains('is-open') ? close() : open();
    });
  });
}

function initReviewsSlider() {
  const root = document.querySelector('.reviews-slider');
  if (!root) return;

  const track = root.querySelector('.reviews-slider__track');
  const cards = track ? track.querySelectorAll('.review-card') : [];
  const prev = root.querySelector('[data-slider-prev]');
  const next = root.querySelector('[data-slider-next]');
  const dots = root.querySelectorAll('[data-dot]');

  if (!track || !cards.length) return;

  let current = 0;
  let timer = null;

  function apply(i) {
    const card = cards[i];
    if (!card) return;
    const left = card.offsetLeft - track.parentElement.offsetLeft;
    track.style.transform = `translateX(-${left}px)`;
    current = i;
    dots.forEach((d, idx) => d.classList.toggle('is-active', idx === i));
  }

  function nextSlide() {
    apply((current + 1) % cards.length);
  }

  function prevSlide() {
    apply((current - 1 + cards.length) % cards.length);
  }

  function start() {
    stop();
    timer = window.setInterval(nextSlide, 5000);
  }

  function stop() {
    if (timer) window.clearInterval(timer);
    timer = null;
  }

  dots.forEach((d) => {
    d.addEventListener('click', () => {
      const idx = parseInt(d.getAttribute('data-dot') || '0', 10);
      apply(Number.isFinite(idx) ? idx : 0);
      start();
    });
  });

  if (prev) prev.addEventListener('click', () => { prevSlide(); start(); });
  if (next) next.addEventListener('click', () => { nextSlide(); start(); });

  // pause on hover
  const viewport = track.parentElement;
  if (viewport) {
    viewport.addEventListener('mouseenter', stop);
    viewport.addEventListener('mouseleave', start);
  }

  // keep alignment on resize
  window.addEventListener('resize', () => apply(current));

  apply(0);
  start();
}

/* ═══════════════════════════════════════════════════
   CALCULATOR — multi-step wizard + material calcs
   ═══════════════════════════════════════════════════ */

function initQuizCalculator() {
  initCalcTabs();
  initWizard();
  initMaterialCalcs();
}

/* ── Tab switching (order / materials) ── */
function initCalcTabs() {
  const btns = document.querySelectorAll('[data-calc-tab]');
  const panels = document.querySelectorAll('[data-calc-panel]');
  if (!btns.length) return;

  btns.forEach((btn) => {
    btn.addEventListener('click', () => {
      const key = btn.getAttribute('data-calc-tab');
      btns.forEach((b) => { b.classList.toggle('is-active', b === btn); b.setAttribute('aria-selected', b === btn ? 'true' : 'false'); });
      panels.forEach((p) => p.classList.toggle('is-active', p.getAttribute('data-calc-panel') === key));
    });
  });

  // Sub-tabs (material calcs)
  const subBtns = document.querySelectorAll('[data-mat-tab]');
  const subPanels = document.querySelectorAll('[data-mat-panel]');
  subBtns.forEach((btn) => {
    btn.addEventListener('click', () => {
      const key = btn.getAttribute('data-mat-tab');
      subBtns.forEach((b) => b.classList.toggle('is-active', b === btn));
      subPanels.forEach((p) => p.classList.toggle('is-active', p.getAttribute('data-mat-panel') === key));
    });
  });
}

/* ── Multi-step wizard (Profi.ru pattern) ── */
function initWizard() {
  const wiz = document.querySelector('[data-wizard]');
  if (!wiz) return;

  const steps = wiz.querySelectorAll('[data-wizard-step]');
  const bar = wiz.querySelector('[data-wizard-bar]');
  const label = wiz.querySelector('[data-wizard-label]');
  const prevBtn = wiz.querySelector('[data-wz-prev]');
  const nextBtn = wiz.querySelector('[data-wz-next]');
  const submitBtn = wiz.querySelector('[data-wz-submit]');
  const priceEl = wiz.querySelector('[data-wz-price]');
  const includesEl = wiz.querySelector('[data-wz-includes]');
  const successEl = wiz.querySelector('[data-wz-success]');
  const pointsEl = wiz.querySelector('[data-wz-points]');
  const packagesEl = wiz.querySelector('[data-wz-packages]');
  const consentEl = wiz.querySelector('[data-wz="consent"]');
  const defaultService = (wiz.closest('[data-default-service]')?.getAttribute('data-default-service') || '').trim();

  let current = 1;
  let selectedService = '';
  const total = 4;
  let points = 0;
  let calcOpenTracked = false;
  let resultTracked = false;

  // Step rendering
  function showStep(n) {
    current = n;
    steps.forEach((s) => s.classList.toggle('is-active', parseInt(s.getAttribute('data-wizard-step')) === n));
    if (bar) bar.style.width = `${(n / total) * 100}%`;
    if (label) label.textContent = `Шаг ${n} из ${total}`;

    prevBtn.style.display = n > 1 ? '' : 'none';
    nextBtn.style.display = n < total ? '' : 'none';
    submitBtn.style.display = n === total ? '' : 'none';

    // Step 2: show relevant fields
    if (n === 2) {
      wiz.querySelectorAll('[data-fields-for]').forEach((f) => {
        f.style.display = f.getAttribute('data-fields-for') === selectedService ? '' : 'none';
      });
    }

    // Step 3: calculate estimate
    if (n === 3) {
      calcEstimate();
      if (!resultTracked && selectedService) {
        resultTracked = true;
        kvMetrikaGoal('calc_result_view', { service: selectedService });
      }
    }
  }

  function addPoints(amount) {
    points = Math.max(0, points + amount);
    if (pointsEl) pointsEl.textContent = String(points);
  }

  function setService(service) {
    if (!service) return;
    selectedService = service;
    wiz.querySelectorAll('.wizard__card').forEach((card) => {
      const input = card.querySelector('input[name="wz_service"]');
      const checked = !!(input && input.value === service);
      card.classList.toggle('is-selected', checked);
      if (input) input.checked = checked;
    });
  }

  // Service card selection
  wiz.querySelectorAll('.wizard__card').forEach((card) => {
    card.addEventListener('click', () => {
      wiz.querySelectorAll('.wizard__card').forEach((c) => c.classList.remove('is-selected'));
      card.classList.add('is-selected');
      const input = card.querySelector('input[name="wz_service"]');
      if (input) {
        input.checked = true;
        selectedService = input.value;
      }
      addPoints(10);
    });
  });

  wiz.querySelectorAll('[data-wz-preset]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const preset = (btn.getAttribute('data-wz-preset') || '').trim();
      if (!preset) return;
      setService(preset);
      showStep(2);
      addPoints(15);
      kvMetrikaGoal('calc_step_complete', { step: 1, service: preset, source: 'preset' });
    });
  });

  // Sticky widget click listener
  const stickyWidget = document.querySelector('.sticky-engineer-widget');
  if (stickyWidget) {
    stickyWidget.addEventListener('click', (e) => {
      e.preventDefault();
      // Switch to order tab if not active
      const orderTab = document.querySelector('[data-calc-tab="order"]');
      if (orderTab && !orderTab.classList.contains('is-active')) {
        orderTab.click();
      }
      
      // Select default service if none
      if (!selectedService) setService('roof');
      
      // Jump to step 4
      showStep(4);
      
      const calcEl = document.getElementById('calculator');
      if (calcEl) calcEl.scrollIntoView({ behavior: 'smooth' });
    });
  }

  // Navigation
  nextBtn.addEventListener('click', () => {
    if (!validateStep(current)) return;
    if (current < total) {
      kvMetrikaGoal('calc_step_complete', { step: current, service: selectedService || '' });
      addPoints(15);
      showStep(current + 1);
    }
  });

  prevBtn.addEventListener('click', () => {
    if (current > 1) showStep(current - 1);
  });

  submitBtn.addEventListener('click', () => {
    if (!validateStep(current)) return;
    kvMetrikaGoal('calc_step_complete', { step: current, service: selectedService || '' });
    submitLead();
  });

  function validateStep(n) {
    if (n === 1 && !selectedService) {
      shakeCards();
      return false;
    }
    if (n === 2) {
      const requirePositive = (key) => {
        const el = wiz.querySelector(`[data-wz="${key}"]`);
        const val = el ? parseFloat(el.value || '0') : 0;
        if (val > 0) return true;
        if (el) {
          el.classList.add('is-invalid');
          el.focus();
          setTimeout(() => el.classList.remove('is-invalid'), 2000);
        }
        return false;
      };

      if (selectedService === 'roof') return requirePositive('roof_area');
      if (selectedService === 'facade') return requirePositive('facade_area');
      if (selectedService === 'fence') return requirePositive('fence_length');
    }
    if (n === 4) {
      const hasConsent = !!(consentEl && consentEl.checked);
      if (!hasConsent) {
        if (consentEl) consentEl.focus();
        return false;
      }
    }
    return true;
  }

  function shakeCards() {
    const cards = wiz.querySelector('.wizard__cards');
    if (!cards) return;
    cards.style.animation = 'none';
    void cards.offsetHeight;
    cards.style.animation = 'shake 0.4s ease';
    setTimeout(() => cards.style.animation = '', 500);
  }

  // Price calculations
  const serviceLabels = { roof: 'Кровля', facade: 'Фасад', fence: 'Забор' };

  function getWzVal(name) {
    const el = wiz.querySelector(`[data-wz="${name}"]`);
    return el ? el.value : '';
  }

  function getWzOptText(name) {
    const el = wiz.querySelector(`[data-wz="${name}"]`);
    if (!el) return '';
    if (el.tagName === 'SELECT' && el.selectedOptions && el.selectedOptions[0]) {
      return (el.selectedOptions[0].textContent || '').trim();
    }
    return (el.value || '').trim();
  }

  function calcEstimate() {
    let total = 0;
    let details = [];

    if (selectedService === 'roof') {
      const type = getWzVal('roof_type');
      const mat = getWzVal('roof_material');
      const area = parseFloat(getWzVal('roof_area')) || 0;
      const floors = getWzVal('roof_floors');
      const insulation = getWzVal('roof_insulation');
      const extras = getWzVal('roof_extras');

      const matData = calcConfig.roof.materials[mat] || calcConfig.roof.materials['metallocherepitsa_gl'];
      const matPrice = matData.price;
      const matType = matData.type;
      
      const typeCoef = calcConfig.roof.installation.typeCoef[type] || 1.0;
      const floorsCoef = calcConfig.roof.installation.floorCoef[floors] || 1.0;
      const installRate = matType === 'soft' || matType === 'premium' ? calcConfig.roof.installation.baseWarm : calcConfig.roof.installation.baseCold;

      let extrasPrice = 0;
      if (insulation === 'insulation_200') extrasPrice += calcConfig.roof.extras.insulation_200.price;
      if (extras === 'snow_guards' || extras === 'both') {
         extrasPrice += (4 * Math.sqrt(area)) * calcConfig.roof.extras.snow_guards.price / area || 0;
      }
      if (extras === 'gutters' || extras === 'both') {
         extrasPrice += (2 * Math.sqrt(area)) * calcConfig.roof.extras.gutters.price / area || 0;
      }
      
      const mPrice = matPrice * (1 + calcConfig.roof.dobornieRatio) + extrasPrice;
      const iPrice = installRate * typeCoef * floorsCoef;
      
      total = Math.round((mPrice + iPrice) * area) + calcConfig.delivery.basePrice;

      details = [
        `Покрытие: ${getWzOptText('roof_material') || '—'}`,
        'Монтаж покрытия + доборные элементы',
        'Гидроизоляция и вентиляция',
        'Доставка материалов',
        'Гарантия на работы',
      ];
      if (insulation !== 'none') details.push('Утепление 200мм');
      if (extras !== 'none') details.push('Снегозадержатели / водосток');
    } else if (selectedService === 'facade') {
      const mat = getWzVal('facade_material');
      const area = parseFloat(getWzVal('facade_area')) || 0;
      const openings = parseFloat(getWzVal('facade_openings')) || 0;
      const ins = getWzVal('facade_insulation');
      const sub = getWzVal('facade_subsystem');

      const netArea = Math.max(area - openings * 2.2, area * 0.6);
      const matData = calcConfig.facade.materials[mat] || calcConfig.facade.materials['siding_gl'];
      const matPrice = matData.price;
      
      let baseRate = calcConfig.facade.installation.baseSiding;
      if (mat.includes('panels')) baseRate = calcConfig.facade.installation.basePanels;
      if (mat.includes('fibro')) baseRate = calcConfig.facade.installation.baseFibro;
      if (mat.includes('plaster')) baseRate = calcConfig.facade.installation.basePlaster;

      let insPrice = 0;
      if (ins === 'insulation_50') insPrice = calcConfig.facade.extras.insulation_50.price;
      if (ins === 'insulation_100') insPrice = calcConfig.facade.extras.insulation_100.price;
      
      let subPrice = sub === 'metal' ? calcConfig.facade.extras.subsystem_metal.price : calcConfig.facade.extras.subsystem_wood.price;

      const mPrice = matPrice * (1 + calcConfig.facade.dobornieRatio) + insPrice + subPrice + calcConfig.facade.extras.membrane.price;
      const iPrice = baseRate;

      total = Math.round((mPrice + iPrice) * netArea) + calcConfig.delivery.basePrice;
      
      details = [
        `Материал: ${getWzOptText('facade_material') || '—'}`,
        `Подсистема: ${getWzOptText('facade_subsystem') || '—'}`,
        `Утепление: ${getWzOptText('facade_insulation') || '—'}`,
        'Доборные элементы',
        'Монтажные работы',
        'Доставка',
      ];
    } else if (selectedService === 'fence') {
      const type = getWzVal('fence_type');
      const length = parseFloat(getWzVal('fence_length')) || 0;
      const gates = getWzVal('fence_gates');
      const height = parseFloat(getWzVal('fence_height')) || 2.0;

      const matData = calcConfig.fence.materials[type] || calcConfig.fence.materials['profnastil_gl'];
      const matPrice = matData.price;

      let baseRate = calcConfig.fence.installation.baseProfnastil;
      if (type.includes('shtaketnik')) baseRate = calcConfig.fence.installation.baseShtaketnik;
      if (type.includes('3d')) baseRate = calcConfig.fence.installation.base3D;
      if (type.includes('jalousie') || type.includes('rancho')) baseRate = calcConfig.fence.installation.baseJalousie;

      const heightCoef = Math.max(0.85, Math.min(1.35, height / 2.0));
      
      const mPricePm = (matPrice * height) + calcConfig.fence.extras.lags.price * 2 + calcConfig.fence.extras.screws.price;
      const iPricePm = baseRate * heightCoef;

      total = Math.round((mPricePm + iPricePm) * length);
      
      if (gates === 'swing') total += calcConfig.fence.extras.gate_swing.price;
      if (gates === 'sliding') total += calcConfig.fence.extras.gate_sliding.price;

      if (length > 0) total += calcConfig.delivery.basePrice;

      details = [
        `Материал: ${getWzOptText('fence_type') || '—'}`,
        `Высота: ${height.toFixed(1).replace('.', ',')} м`,
        'Столбы + лаги + крепёж',
        'Бетонирование столбов',
        'Монтажные работы',
        'Гарантия на работы',
      ];
      if (gates !== 'none') details.push(`Ворота/калитки: ${getWzOptText('fence_gates')}`);
    }

    // CountUp animation
    animateNumber(priceEl, total);

    if (includesEl) {
      includesEl.innerHTML = details.map((d) => `<span style="display:block;padding:2px 0;">✓ ${d}</span>`).join('');
    }

    if (packagesEl && total > 0) {
      const eco = Math.round(total * 0.92);
      const opt = total;
      const premium = Math.round(total * 1.18);
      packagesEl.innerHTML = [
        `<div class="wizard__package"><div class="wizard__package-name">Эконом</div><div class="wizard__package-price">${eco.toLocaleString('ru-RU')} ₽</div><div class="wizard__package-note">Базовая комплектация и стандартный срок.</div></div>`,
        `<div class="wizard__package is-recommended"><div class="wizard__package-name">Оптимум</div><div class="wizard__package-price">${opt.toLocaleString('ru-RU')} ₽</div><div class="wizard__package-note">Баланс цены, ресурса и скорости работ.</div></div>`,
        `<div class="wizard__package"><div class="wizard__package-name">Премиум</div><div class="wizard__package-price">${premium.toLocaleString('ru-RU')} ₽</div><div class="wizard__package-note">Расширенная гарантия и приоритет логистики.</div></div>`,
      ].join('');
    }
  }

  function animateNumber(el, target) {
    if (!el) return;
    const duration = 600;
    const start = performance.now();
    const from = 0;

    function tick(now) {
      const elapsed = now - start;
      const progress = Math.min(elapsed / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      const val = Math.round(from + (target - from) * eased);
      el.textContent = val.toLocaleString('ru-RU');
      if (progress < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }

  function baseTelegramLink() {
    return document.querySelector('a[href*="t.me/"]')?.getAttribute('href') || '';
  }

  function buildTelegramUrl(baseLink, text, pageUrl) {
    const safeBase = (baseLink || '').trim();
    const url = String(pageUrl || '').trim();
    const msg = String(text || '').trim();

    const enc = (s) => encodeURIComponent(String(s || ''));
    const share = `https://t.me/share/url?url=${enc(url)}&text=${enc(msg)}`;

    if (!safeBase) return share;

    // If base link is a direct t.me chat link, try to append ?text=
    if (/t\.me\/(?!share\/url)/i.test(safeBase)) {
      const join = safeBase.includes('?') ? '&' : '?';
      return `${safeBase}${join}text=${enc(`${msg}\n\n${url}`)}`;
    }

    return share;
  }

  function submitLead() {
    submitBtn.disabled = true;
    submitBtn.textContent = 'Открываем Telegram...';

    // Collect details
    let det = [];
    if (selectedService === 'roof') {
      det = [
        `Покрытие: ${getWzOptText('roof_material')}`,
        `Тип крыши: ${getWzOptText('roof_type')}`,
        `Площадь: ${getWzVal('roof_area')} м²`,
        `Этажей: ${getWzOptText('roof_floors')}`,
      ];
    }
    if (selectedService === 'facade') {
      det = [
        `Материал: ${getWzOptText('facade_material')}`,
        `Площадь: ${getWzVal('facade_area')} м²`,
        `Проёмов: ${getWzVal('facade_openings')}`,
        `Утепление: ${getWzOptText('facade_insulation')}`,
      ];
    }
    if (selectedService === 'fence') {
      det = [
        `Материал: ${getWzOptText('fence_type')}`,
        `Длина: ${getWzVal('fence_length')} пог.м`,
        `Высота: ${getWzOptText('fence_height')}`,
        `Ворот/калиток: ${getWzVal('fence_gates')}`,
      ];
    }

    const city = (getWzVal('city') || '').trim();
    const comment = (getWzVal('comment') || '').trim();
    const estimate = priceEl ? `${(priceEl.textContent || '').trim()} ₽` : '';
    const service = serviceLabels[selectedService] || selectedService || '';
    const pageUrl = window.location.href;

    const lines = [
      'Здравствуйте! Нужна точная смета.',
      service ? `Услуга: ${service}` : '',
      city ? `Город/район: ${city}` : '',
      det.length ? `Параметры: ${det.join(', ')}` : '',
      estimate ? `Оценка калькулятора: ${estimate}` : '',
      comment ? `Комментарий: ${comment}` : '',
    ].filter(Boolean);

    const tgUrl = buildTelegramUrl(baseTelegramLink(), lines.join('\n'), pageUrl);

    kvMetrikaGoal('tg_click');
    kvMetrikaGoal('calc_lead', { service: selectedService });
    kvMetrikaGoal('lead_submit', { source: 'calculator_chat', service: selectedService || '' });

    try {
      window.open(tgUrl, '_blank', 'noopener');
    } catch (e) {
      window.location.href = tgUrl;
    }

    if (successEl) {
      successEl.style.display = '';
      const fields = wiz.querySelector('[data-wizard-step="4"] .wizard__fields');
      if (fields) fields.style.display = 'none';
    }
    submitBtn.style.display = 'none';
    prevBtn.style.display = 'none';
  }

  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries, obs) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting || calcOpenTracked) return;
        calcOpenTracked = true;
        kvMetrikaGoal('calc_open', { context: wiz.closest('[data-calc-context]')?.getAttribute('data-calc-context') || 'default' });
        obs.unobserve(wiz);
      });
    }, { threshold: 0.35 });
    io.observe(wiz);
  }

  if (defaultService) {
    setService(defaultService);
  }
  showStep(1);
}

/* ── Material calculators (Grand Line pattern) ── */
function initMaterialCalcs() {
  const calcBtns = document.querySelectorAll('[data-mc-calc]');
  if (!calcBtns.length) return;

  calcBtns.forEach((btn) => {
    btn.addEventListener('click', () => {
      const type = btn.getAttribute('data-mc-calc');
      if (type === 'roof') calcRoofMaterials();
      if (type === 'facade') calcFacadeMaterials();
      if (type === 'fence') calcFenceMaterials();
      kvMetrikaGoal('calc_materials', { type });
    });
  });

  function mcVal(name) {
    const el = document.querySelector(`[data-mc="${name}"]`);
    return el ? el.value : '';
  }

  function mcOpt(name) {
    const el = document.querySelector(`[data-mc="${name}"]`);
    return el && el.selectedOptions ? el.selectedOptions[0] : null;
  }

  function showResult(type, rows) {
    const container = document.querySelector(`[data-mc-result="${type}"]`);
    if (!container) return;

    let html = '<table><thead><tr><th>Материал</th><th>Кол-во</th><th>Ед.</th></tr></thead><tbody>';
    rows.forEach((r) => {
      html += `<tr><td>${r[0]}</td><td>${r[1]}</td><td>${r[2]}</td></tr>`;
    });
    html += '</tbody></table>';
    container.innerHTML = html;
    container.style.display = '';

    const cta = container.parentElement.querySelector('[data-mc-cta]');
    if (cta) cta.style.display = '';
  }

  function calcRoofMaterials() {
    const len = parseFloat(mcVal('r_length')) || 0;
    const wid = parseFloat(mcVal('r_width')) || 0;
    const type = mcVal('r_type');
    const coef = calcConfig.roof.installation.typeCoef[type] || 1.0;
    const matVal = mcVal('r_material');
    const matData = calcConfig.roof.materials[matVal] || calcConfig.roof.materials['metallocherepitsa_gl'];
    const matName = matData.name;
    const matPrice = matData.price;

    if (len <= 0 || wid <= 0) { window.alert('Укажите длину и ширину ската.'); return; }

    const baseArea = len * wid * coef;
    const area = Math.ceil(baseArea * 1.1); // +10% запас
    const ridgeLen = Math.ceil(len * 1.05);
    const eaveLen = Math.ceil(len * 2);
    const gableLen = Math.ceil(wid * 2);
    const hydro = Math.ceil(baseArea * 1.15);
    const screws = Math.ceil(baseArea * 8);
    const materialsCost = matPrice > 0 ? Math.round(area * matPrice) : 0;

    showResult('roof', [
      [`${matName} (запас 10%)`, area, 'м²'],
      ['Конёк', ridgeLen, 'пог.м'],
      ['Карнизная планка', eaveLen, 'пог.м'],
      ['Торцевая планка', gableLen, 'пог.м'],
      ['Гидроизоляция', hydro, 'м²'],
      ['Саморезы кровельные', screws, 'шт'],
      ...(materialsCost ? [['Ориентир по материалам', materialsCost.toLocaleString('ru-RU'), '₽']] : []),
    ]);
  }

  function calcFacadeMaterials() {
    const area = parseFloat(mcVal('f_area')) || 0;
    const openings = parseFloat(mcVal('f_openings')) || 0;
    
    const matVal = mcVal('f_material');
    const matData = calcConfig.facade.materials[matVal] || calcConfig.facade.materials['siding_gl'];
    const matName = matData.name;
    const matPrice = matData.price;

    const insVal = mcVal('f_insulation');
    const insData = insVal !== 'none' ? calcConfig.facade.extras[insVal] : null;
    const insName = insData ? insData.name : '';
    const insPrice = insData ? insData.price : 0;

    if (area <= 0) { window.alert('Укажите площадь стен.'); return; }

    const netArea = Math.max(area - openings * 2.5, area * 0.5);
    const panels = Math.ceil(netArea * 1.1);
    const starterLen = Math.ceil(Math.sqrt(area) * 4 * 1.1);
    const jProfile = Math.ceil(openings * 5);
    const corners = Math.ceil(Math.sqrt(area) * 0.4 * 4);
    const insArea = insPrice > 0 ? Math.ceil(netArea * 1.05) : 0;
    const fasteners = Math.ceil(netArea * 6);
    const materialsCost = (matPrice > 0 ? Math.round(panels * matPrice) : 0) + (insPrice > 0 ? Math.round(insArea * insPrice) : 0);

    const rows = [
      [`${matName} (запас 10%)`, panels, 'м²'],
      ['Стартовая планка', starterLen, 'пог.м'],
      ['J-профиль', jProfile, 'пог.м'],
      ['Наружный угол', corners, 'пог.м'],
    ];
    if (insArea > 0) rows.push([`Утепление: ${insName || '—'}`, insArea, 'м²']);
    rows.push(['Крепёж (дюбели/саморезы)', fasteners, 'шт']);
    if (materialsCost > 0) rows.push(['Ориентир по материалам', materialsCost.toLocaleString('ru-RU'), '₽']);

    showResult('facade', rows);
  }

  function calcFenceMaterials() {
    const length = parseFloat(mcVal('z_length')) || 0;
    const height = parseFloat(mcVal('z_height')) || 2;
    const gates = parseFloat(mcVal('z_gates')) || 0;
    
    const matVal = mcVal('z_type');
    const matData = calcConfig.fence.materials[matVal] || calcConfig.fence.materials['profnastil_gl'];
    const matName = matData.name;
    const matPrice = matData.price;

    if (length <= 0) { window.alert('Укажите длину забора.'); return; }

    const span = 2.5;
    const posts = Math.ceil(length / span) + 1;
    const lagCount = Math.ceil(length / span) * (height > 2 ? 3 : 2);
    const lagLen = Math.ceil(lagCount * span * 1.05);

    const fillArea = Math.ceil(length * height * 1.1);
    const screws = Math.ceil(fillArea * 10);
    const materialsCost = matPrice > 0 ? Math.round(fillArea * matPrice) : 0;

    const rows = [
      [`${matName} (запас 10%)`, fillArea, 'м²'],
      ['Столбы 60×60', posts, 'шт'],
      ['Лаги (профтруба 40×20)', lagLen, 'пог.м'],
      ['Саморезы/заклёпки', screws, 'шт'],
      ['Заглушки на столбы', posts, 'шт'],
    ];
    if (gates > 0) rows.push([`Ворота/калитки`, gates, 'шт']);
    if (materialsCost > 0) rows.push(['Ориентир по материалам', materialsCost.toLocaleString('ru-RU'), '₽']);

    showResult('fence', rows);
  }
}

/* ═══════════════════════════════════════════════════
   PARTNER POINTS (GEO) — booking intercept dialog
   ═══════════════════════════════════════════════════ */

function initPartnerPointsBooking() {
  const dlg = document.getElementById('kv-partner-dialog');
  if (!dlg || typeof dlg.showModal !== 'function') return;

  const form = dlg.querySelector('#kv-partner-form');
  const closeBtn = dlg.querySelector('[data-kv-partner-close]');
  const closeBtn2 = dlg.querySelector('[data-kv-partner-close2]');
  const submitBtn = dlg.querySelector('[data-kv-partner-submit]');
  const fieldsWrap = dlg.querySelector('[data-kv-partner-fields]');
  const actionsWrap = dlg.querySelector('[data-kv-partner-actions]');
  const successWrap = dlg.querySelector('[data-kv-partner-success]');

  const subEl = dlg.querySelector('[data-kv-partner-sub]');
  const hRouteUrl = dlg.querySelector('[data-kv-partner-route-url]');
  const hPointId = dlg.querySelector('[data-kv-partner-point-id]');
  const hCity = dlg.querySelector('[data-kv-partner-city]');
  const hCitySlug = dlg.querySelector('[data-kv-partner-city-slug]');
  const hRegionSlug = dlg.querySelector('[data-kv-partner-region-slug]');
  const hServiceSlug = dlg.querySelector('[data-kv-partner-service-slug]');
  const hAreaHint = dlg.querySelector('[data-kv-partner-area-hint]');

  const dateEl = dlg.querySelector('[data-kv-partner-date]');
  const timeEl = dlg.querySelector('[data-kv-partner-time]');
  const commentEl = dlg.querySelector('[data-kv-partner-comment]');
  const consentEl = dlg.querySelector('[data-kv-partner-consent]');

  const openAfterLink = dlg.querySelector('[data-kv-partner-open-after]');
  const defaultTg = document.querySelector('a[href*="t.me/"]')?.getAttribute('href') || '';

  function geoParams(extra = {}) {
    const base = {
      city_slug: (hCitySlug && hCitySlug.value ? hCitySlug.value : '').trim(),
      region_slug: (hRegionSlug && hRegionSlug.value ? hRegionSlug.value : '').trim(),
      service_slug: (hServiceSlug && hServiceSlug.value ? hServiceSlug.value : '').trim(),
      point_id: (hPointId && hPointId.value ? hPointId.value : '').trim(),
      ui_variant: 'modal',
      source_cta: 'order_point_block',
    };
    return Object.assign(base, extra || {});
  }

  if (document.querySelector('.geo-page')) {
    kvMetrikaGoal('geo_page_view', geoParams());
  }

  function buildTelegramUrl(baseLink, text, pageUrl) {
    const safeBase = (baseLink || '').trim();
    const url = String(pageUrl || '').trim();
    const msg = String(text || '').trim();

    const enc = (s) => encodeURIComponent(String(s || ''));
    const share = `https://t.me/share/url?url=${enc(url)}&text=${enc(msg)}`;
    if (!safeBase) return share;
    if (/t\.me\/(?!share\/url)/i.test(safeBase)) {
      const join = safeBase.includes('?') ? '&' : '?';
      return `${safeBase}${join}text=${enc(`${msg}\n\n${url}`)}`;
    }
    return share;
  }

  function todayISO() {
    const d = new Date();
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  }

  function resetState() {
    if (successWrap) successWrap.hidden = true;
    if (fieldsWrap) fieldsWrap.hidden = false;
    if (actionsWrap) actionsWrap.hidden = false;
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Написать в Telegram';
    }
    if (dateEl) {
      dateEl.min = todayISO();
      if (!dateEl.value) dateEl.value = todayISO();
    }
    if (timeEl) timeEl.value = '';
    if (commentEl) commentEl.value = '';
    if (consentEl) consentEl.checked = false;
    if (openAfterLink) openAfterLink.setAttribute('href', '#');
  }

  function openDialog(ctx) {
    resetState();
    const routeUrl = (ctx.routeUrl || '').trim();
    const pointId = (ctx.pointId || '').trim();
    const city = (ctx.city || '').trim();
    const citySlug = (ctx.citySlug || '').trim();
    const regionSlug = (ctx.regionSlug || '').trim();
    const serviceSlug = (ctx.serviceSlug || '').trim();
    const areaHint = (ctx.areaHint || '').trim();

    if (hRouteUrl) hRouteUrl.value = routeUrl;
    if (hPointId) hPointId.value = pointId;
    if (hCity) hCity.value = city;
    if (hCitySlug) hCitySlug.value = citySlug;
    if (hRegionSlug) hRegionSlug.value = regionSlug;
    if (hServiceSlug) hServiceSlug.value = serviceSlug;
    if (hAreaHint) hAreaHint.value = areaHint;

    if (subEl) {
      const where = areaHint ? `Ориентир: ${areaHint}.` : '';
      subEl.textContent = `${where} Напишите в Telegram — пришлём точный адрес и маршрут, уточним наличие образцов.`;
    }

    try {
      dlg.showModal();
      kvMetrikaGoal('geo_booking_open', geoParams());
    } catch (e) {
      // If showModal fails, let link work normally (no-op here)
    }
  }

  function closeDialog() {
    try {
      dlg.close();
    } catch (e) {
      // no-op
    }
  }

  if (closeBtn) closeBtn.addEventListener('click', closeDialog);
  if (closeBtn2) closeBtn2.addEventListener('click', closeDialog);

  // Close when clicking backdrop area
  dlg.addEventListener('click', (e) => {
    if (e.target === dlg) closeDialog();
  });

  // Intercept clicks on partner booking/open buttons/links (keep href for no-JS)
  document.addEventListener('click', (e) => {
    const el = e.target && e.target.closest ? e.target.closest('[data-kv-partner-book], [data-kv-partner-open]') : null;
    if (!el) return;

    const routeUrl = (el.getAttribute('data-route-url') || el.getAttribute('href') || '').trim();
    if (!routeUrl) return;

    const pointId = el.getAttribute('data-point-id') || '';
    const city = el.getAttribute('data-city') || '';
    const citySlug = el.getAttribute('data-city-slug') || '';
    const regionSlug = el.getAttribute('data-region-slug') || '';
    const serviceSlug = el.getAttribute('data-service-slug') || '';
    const areaHint = el.getAttribute('data-area-hint') || '';

    e.preventDefault();
    openDialog({ routeUrl, pointId, city, citySlug, regionSlug, serviceSlug, areaHint });
  }, { capture: true });

  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const routeUrl = (hRouteUrl && hRouteUrl.value ? hRouteUrl.value : '').trim();
      const city = (hCity && hCity.value ? hCity.value : '').trim();
      const areaHint = (hAreaHint && hAreaHint.value ? hAreaHint.value : '').trim();
      const date = (dateEl && dateEl.value ? dateEl.value : '').trim();
      const time = (timeEl && timeEl.value ? timeEl.value : '').trim();
      const comment = (commentEl && commentEl.value ? commentEl.value : '').trim();
      const consent = !!(consentEl && consentEl.checked);

      if (!date || !time || !consent) {
        if (dateEl && !date) dateEl.focus();
        if (timeEl && !time) timeEl.focus();
        if (consentEl && !consent) consentEl.focus();
        kvMetrikaGoal('geo_booking_error', geoParams({ error: 'validation' }));
        return;
      }

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Открываем Telegram...';
      }

      try {
        const pageUrl = window.location.href;
        const lines = [
          'Здравствуйте! Нужен адрес пункта оформления заказов и просмотр образцов.',
          city ? `Город: ${city}` : '',
          areaHint ? `Ориентир: ${areaHint}` : '',
          date ? `Дата: ${date}` : '',
          time ? `Время: ${time}` : '',
          comment ? `Комментарий: ${comment}` : '',
          routeUrl ? `Маршрут: ${routeUrl}` : '',
        ].filter(Boolean);

        const tgUrl = buildTelegramUrl(defaultTg, lines.join('\n'), pageUrl);
        kvMetrikaGoal('tg_click');
        kvMetrikaGoal('geo_booking_submit', geoParams({ messenger: 'telegram' }));
        kvMetrikaGoal('geo_booking_success', geoParams());
        window.open(tgUrl, '_blank', 'noopener');

        if (fieldsWrap) fieldsWrap.hidden = true;
        if (actionsWrap) actionsWrap.hidden = true;
        if (successWrap) successWrap.hidden = false;
        if (openAfterLink) openAfterLink.setAttribute('href', routeUrl || '#');
      } catch (err) {
        kvMetrikaGoal('geo_booking_error', geoParams({ error: 'submit' }));
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Написать в Telegram';
        }
      }
    });
  }

  if (openAfterLink) {
    openAfterLink.addEventListener('click', () => {
      kvMetrikaGoal('geo_route_click_after_booking', geoParams());
      closeDialog();
    });
  }
}

function initTocActive() {
  const toc = document.querySelector('.toc');
  if (!toc) return;
  const links = toc.querySelectorAll('a[href^="#"]');
  if (!links.length) return;

  const items = [];
  links.forEach((a) => {
    const id = (a.getAttribute('href') || '').slice(1);
    const el = id ? document.getElementById(id) : null;
    if (el) items.push({ el, a });
  });
  if (!items.length) return;

  const obs = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        links.forEach((l) => l.classList.remove('is-active'));
        const found = items.find((x) => x.el === entry.target);
        if (found) found.a.classList.add('is-active');
      });
    },
    { rootMargin: '-20% 0px -70% 0px', threshold: 0 }
  );

  items.forEach((x) => obs.observe(x.el));

  links.forEach((a) => {
    a.addEventListener('click', (e) => {
      const id = (a.getAttribute('href') || '').slice(1);
      const el = id ? document.getElementById(id) : null;
      if (!el) return;
      e.preventDefault();
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      history.replaceState(null, '', `#${id}`);
    });
  });
}

function initRevealAnimations() {
  const nodes = Array.from(document.querySelectorAll('.animate-in'));
  if (!nodes.length) return;

  const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce || !('IntersectionObserver' in window)) {
    nodes.forEach((el) => el.classList.add('is-in'));
    return;
  }

  const io = new IntersectionObserver(
    (entries, obs) => {
      let delay = 0;
      entries.forEach((e) => {
        if (!e.isIntersecting) return;
        const target = e.target;
        if (entries.filter(ent => ent.isIntersecting).length > 1) {
          target.style.animationDelay = `${delay}ms`;
          delay += 100;
        }
        target.classList.add('is-in');
        obs.unobserve(target);
      });
    },
    { threshold: 0.15, rootMargin: '0px 0px -10% 0px' }
  );
  nodes.forEach((el) => io.observe(el));
}

function initLazyload() {
  const els = Array.from(document.querySelectorAll('img.lazyload[data-src], source.lazyload[data-srcset]'));
  if (!els.length) return;

  function loadOne(el) {
    if (!el || el.getAttribute('data-loaded')) return;
    if (el.tagName === 'IMG') {
      const s = el.getAttribute('data-src');
      if (s) el.setAttribute('src', s);
      const ss = el.getAttribute('data-srcset');
      if (ss) el.setAttribute('srcset', ss);
      el.removeAttribute('data-src');
      el.removeAttribute('data-srcset');
      el.setAttribute('loading', 'lazy');
      el.decoding = 'async';
    } else {
      const ss = el.getAttribute('data-srcset');
      if (ss) el.setAttribute('srcset', ss);
      el.removeAttribute('data-srcset');
    }
    el.setAttribute('data-loaded', '1');
    el.classList.remove('lazyload');
  }

  if (!('IntersectionObserver' in window)) {
    els.forEach(loadOne);
    return;
  }

  const io = new IntersectionObserver(
    (entries, obs) => {
      entries.forEach((e) => {
        if (!e.isIntersecting) return;
        loadOne(e.target);
        obs.unobserve(e.target);
      });
    },
    { threshold: 0.01, rootMargin: '200px 0px' }
  );
  els.forEach((el) => io.observe(el));
}

function initMagneticButtons() {
  const btns = document.querySelectorAll('.btn--primary');
  if (!btns.length) return;
  
  const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce) return;

  btns.forEach(btn => {
    btn.addEventListener('mousemove', (e) => {
      const rect = btn.getBoundingClientRect();
      const x = e.clientX - rect.left - rect.width / 2;
      const y = e.clientY - rect.top - rect.height / 2;
      btn.style.transform = `translate(${x * 0.1}px, ${y * 0.15}px)`;
    });
    btn.addEventListener('mouseleave', () => {
      btn.style.transform = '';
    });
  });
}

function initDirectTelegram() {
  const btns = document.querySelectorAll('[data-direct-tg]');
  if (!btns.length) return;

  btns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const text = btn.getAttribute('data-direct-tg') || '';
      const baseLink = document.querySelector('a[href*="t.me/"]')?.getAttribute('href') || '';
      const pageUrl = window.location.href;
      
      const enc = (s) => encodeURIComponent(String(s || ''));
      let tgUrl = `https://t.me/share/url?url=${enc(pageUrl)}&text=${enc(text)}`;
      
      if (baseLink && /t\.me\/(?!share\/url)/i.test(baseLink)) {
        const join = baseLink.includes('?') ? '&' : '?';
        tgUrl = `${baseLink}${join}text=${enc(`${text}\n\n${pageUrl}`)}`;
      }

      kvMetrikaGoal('tg_click');
      kvMetrikaGoal('lead_submit', { source: 'direct_tg_btn' });

      try {
        window.open(tgUrl, '_blank', 'noopener');
      } catch (err) {
        window.location.href = tgUrl;
      }
    });
  });
}

/* ═══════════════════════════════════════════════════
   READING PROGRESS BAR
   ═══════════════════════════════════════════════════ */

function initReadingProgress() {
  const bar = document.querySelector('.reading-progress');
  if (!bar) return;

  const article = document.querySelector('.entry-content') || document.querySelector('.article-layout__main') || document.querySelector('#site-main');
  if (!article) return;

  function updateProgress() {
    const rect = article.getBoundingClientRect();
    const total = rect.height - window.innerHeight;
    if (total <= 0) { bar.style.width = '100%'; return; }
    const scrolled = -rect.top;
    const pct = Math.max(0, Math.min(100, (scrolled / total) * 100));
    bar.style.width = `${pct}%`;
    bar.classList.toggle('has-progress', pct > 0);
  }

  window.addEventListener('scroll', updateProgress, { passive: true });
  updateProgress();
}

/* ═══════════════════════════════════════════════════
   HERO PARALLAX EFFECT
   ═══════════════════════════════════════════════════ */

function initHeroParallax() {
  const hero = document.querySelector('.hero');
  const visual = document.querySelector('.hero__visual');
  if (!hero || !visual) return;

  const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce) return;

  let ticking = false;

  function onScroll() {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => {
      const scrolled = window.scrollY;
      const heroH = hero.offsetHeight;
      if (scrolled < heroH * 1.5) {
        const yOffset = scrolled * 0.15;
        const scale = 1 - (scrolled * 0.0002);
        visual.style.transform = `translateY(${yOffset}px) scale(${Math.max(0.95, scale)})`;
      }
      ticking = false;
    });
  }

  window.addEventListener('scroll', onScroll, { passive: true });
}

/* ═══════════════════════════════════════════════════
   CARD 3D TILT EFFECT
   ═══════════════════════════════════════════════════ */

function initCardTilt() {
  const cards = document.querySelectorAll('.card--tilt, .service-card');
  if (!cards.length) return;

  const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce) return;

  cards.forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = (e.clientX - rect.left) / rect.width;
      const y = (e.clientY - rect.top) / rect.height;
      const rotateX = (0.5 - y) * 8;
      const rotateY = (x - 0.5) * 8;
      card.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-2px)`;
    });

    card.addEventListener('mouseleave', () => {
      card.style.transform = '';
    });
  });
}

/* ═══════════════════════════════════════════════════
   CURSOR GLOW EFFECT
   ═══════════════════════════════════════════════════ */

function initCursorGlow() {
  if (window.matchMedia('(hover: none)').matches) return;
  const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce) return;

  const glow = document.createElement('div');
  glow.classList.add('cursor-glow');
  document.body.appendChild(glow);

  let rAF = null;
  let mx = -500;
  let my = -500;

  document.addEventListener('mousemove', (e) => {
    mx = e.clientX;
    my = e.clientY;
    if (rAF) return;
    rAF = requestAnimationFrame(() => {
      glow.style.left = `${mx}px`;
      glow.style.top = `${my}px`;
      rAF = null;
    });
  });

  document.addEventListener('mouseleave', () => {
    glow.style.opacity = '0';
  });

  document.addEventListener('mouseenter', () => {
    glow.style.opacity = '';
  });
}

/* ═══════════════════════════════════════════════════
   GAMIFICATION — SCROLL DEPTH BADGES
   ═══════════════════════════════════════════════════ */

function initScrollGamification() {
  const badge = document.querySelector('.scroll-badge');
  if (!badge) return;

  const icon = badge.querySelector('.scroll-badge__icon');
  const text = badge.querySelector('.scroll-badge__text');
  if (!icon || !text) return;

  const milestones = [
    { pct: 25, emoji: '\uD83D\uDD25', msg: '\u041D\u0430\u0447\u0430\u043B\u043E \u043F\u043E\u043B\u043E\u0436\u0435\u043D\u043E!', cls: 'scroll-badge--25' },
    { pct: 50, emoji: '\u2B50', msg: '\u041F\u043E\u043B\u043E\u0432\u0438\u043D\u0430 \u043F\u0440\u043E\u0439\u0434\u0435\u043D\u0430!', cls: 'scroll-badge--50' },
    { pct: 75, emoji: '\uD83D\uDE80', msg: '\u041F\u043E\u0447\u0442\u0438 \u0442\u0430\u043C!', cls: 'scroll-badge--75' },
    { pct: 100, emoji: '\uD83C\uDFC6', msg: '\u0412\u0441\u0451 \u0438\u0437\u0443\u0447\u0438\u043B\u0438!', cls: 'scroll-badge--100' },
  ];

  let lastMilestone = -1;
  let hideTimer = null;

  function getScrollPercent() {
    const h = document.documentElement.scrollHeight - window.innerHeight;
    return h > 0 ? Math.round((window.scrollY / h) * 100) : 0;
  }

  function showBadge(m) {
    icon.textContent = m.emoji;
    text.textContent = m.msg;
    badge.className = 'scroll-badge is-visible ' + m.cls;

    clearTimeout(hideTimer);
    hideTimer = setTimeout(() => {
      badge.classList.remove('is-visible');
    }, 3000);
  }

  function onScroll() {
    const pct = getScrollPercent();
    for (let i = milestones.length - 1; i >= 0; i--) {
      if (pct >= milestones[i].pct && i > lastMilestone) {
        lastMilestone = i;
        showBadge(milestones[i]);
        kvMetrikaGoal('scroll_depth', { depth: milestones[i].pct });
        break;
      }
    }
  }

  window.addEventListener('scroll', onScroll, { passive: true });
}

/* ═══════════════════════════════════════════════════
   ENGAGEMENT — TIME ON PAGE
   ═══════════════════════════════════════════════════ */

function initTimeOnPage() {
  const badge = document.querySelector('.time-badge');
  if (!badge) return;

  const textEl = badge.querySelector('.time-badge__text');
  if (!textEl) return;

  let seconds = 0;
  let shown = false;

  const interval = setInterval(() => {
    if (document.hidden) return;
    seconds++;

    if (seconds >= 30 && !shown) {
      badge.classList.add('is-visible');
      shown = true;
    }

    if (shown) {
      const m = Math.floor(seconds / 60);
      const s = seconds % 60;
      textEl.textContent = m > 0 ? `${m} \u043C\u0438\u043D ${s} \u0441\u0435\u043A` : `${s} \u0441\u0435\u043A \u043D\u0430 \u0441\u0430\u0439\u0442\u0435`;
    }
  }, 1000);

  document.addEventListener('visibilitychange', () => {
    if (document.hidden && seconds > 60) {
      kvMetrikaGoal('engaged_time', { seconds });
    }
  });
}

/* ═══════════════════════════════════════════════════
   IMPROVED REVEAL ANIMATIONS WITH VARIANTS
   ═══════════════════════════════════════════════════ */

function initRevealVariants() {
  const nodes = Array.from(document.querySelectorAll('.animate-in--scale, .animate-in--left, .animate-in--right'));
  if (!nodes.length) return;

  const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce || !('IntersectionObserver' in window)) {
    nodes.forEach((el) => el.classList.add('is-in'));
    return;
  }

  const io = new IntersectionObserver(
    (entries, obs) => {
      entries.forEach((e) => {
        if (!e.isIntersecting) return;
        e.target.classList.add('is-in');
        obs.unobserve(e.target);
      });
    },
    { threshold: 0.15, rootMargin: '0px 0px -10% 0px' }
  );
  nodes.forEach((el) => io.observe(el));
}

/* ═══════════════════════════════════════════════════
   SMOOTH HEADER HIDE ON SCROLL DOWN
   ═══════════════════════════════════════════════════ */

function initSmartHeader() {
  const header = document.getElementById('site-header');
  if (!header) return;

  let lastY = 0;
  let ticking = false;

  function onScroll() {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => {
      const y = window.scrollY;
      if (y > 300 && y > lastY + 10) {
        header.style.transform = 'translateY(-100%)';
        header.style.transition = 'transform 0.4s cubic-bezier(0.23, 1, 0.32, 1)';
      } else if (y < lastY - 5 || y < 100) {
        header.style.transform = '';
        header.style.transition = 'transform 0.3s cubic-bezier(0.23, 1, 0.32, 1)';
      }
      lastY = y;
      ticking = false;
    });
  }

  window.addEventListener('scroll', onScroll, { passive: true });
}

onReady(() => {
  initHeaderBurger();
  initScrollTop();
  initCountupStats();
  initMetrikaEvents();
  initFaqAccordion();
  initReviewsSlider();
  initQuizCalculator();
  initPartnerPointsBooking();
  initTocActive();
  initRevealAnimations();
  initRevealVariants();
  initLazyload();
  initMagneticButtons();
  initDirectTelegram();
  initReadingProgress();
  initHeroParallax();
  initCardTilt();
  initCursorGlow();
  initScrollGamification();
  initTimeOnPage();
  initSmartHeader();
});


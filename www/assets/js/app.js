'use strict';

const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

document.addEventListener('DOMContentLoaded', () => {
    initCursor();
    initNav();
    initTypewriter();
    initScrollReveal();
    initCounters();
    initSkillBars();
    initTestimonialsCarousel();
    initProjectFilter();
    initLightbox();
    initCookieBanner();
    initFlashes();
    initFaq();
    initHeaderScroll();
    initMagneticButtons();
    initTocHighlight();
});

/* ─── Custom Cursor ─────────────────────────────────────────────────────────── */
function initCursor() {
    if (window.matchMedia('(hover: none)').matches) return;

    const cursor = $('.cursor');
    if (!cursor) return;

    const dot  = $('.cursor__dot',  cursor);
    const ring = $('.cursor__ring', cursor);
    if (!dot || !ring) return;

    let mx = -100, my = -100;
    let rx = -100, ry = -100;
    let raf;

    document.addEventListener('mousemove', e => {
        mx = e.clientX;
        my = e.clientY;
        dot.style.transform = `translate(${mx}px, ${my}px) translate(-50%, -50%)`;
    }, { passive: true });

    function animateRing() {
        rx += (mx - rx) * 0.12;
        ry += (my - ry) * 0.12;
        ring.style.transform = `translate(${rx}px, ${ry}px) translate(-50%, -50%)`;
        raf = requestAnimationFrame(animateRing);
    }
    animateRing();

    // Hover state
    const hoverTargets = 'a, button, [data-cursor-hover], .service-card, .project-card, .post-card, .testimonial-card, .value-card, .pricing-card, .gallery__item, .faq-item__question, .carousel-btn, .nav-toggle';

    document.addEventListener('mouseover', e => {
        if (e.target.closest(hoverTargets)) {
            document.body.classList.add('cursor-hover');
        }
    });
    document.addEventListener('mouseout', e => {
        if (e.target.closest(hoverTargets)) {
            document.body.classList.remove('cursor-hover');
        }
    });

    document.addEventListener('mousedown', () => {
        dot.style.transform += ' scale(0.7)';
    });
    document.addEventListener('mouseup', () => {
        dot.style.transform = dot.style.transform.replace(' scale(0.7)', '');
    });

    document.addEventListener('mouseleave', () => {
        dot.style.opacity = '0';
        ring.style.opacity = '0';
    });
    document.addEventListener('mouseenter', () => {
        dot.style.opacity = '';
        ring.style.opacity = '';
    });
}

/* ─── Magnetic Buttons ──────────────────────────────────────────────────────── */
function initMagneticButtons() {
    if (window.matchMedia('(hover: none)').matches) return;

    $$('.btn-primary, .btn--large, .nav-link--cta').forEach(btn => {
        btn.addEventListener('mousemove', e => {
            const rect   = btn.getBoundingClientRect();
            const cx     = rect.left + rect.width  / 2;
            const cy     = rect.top  + rect.height / 2;
            const dx     = (e.clientX - cx) * 0.28;
            const dy     = (e.clientY - cy) * 0.28;
            btn.style.transform = `translate(${dx}px, ${dy}px)`;
        });
        btn.addEventListener('mouseleave', () => {
            btn.style.transform = '';
        });
    });
}

/* ─── Header scroll effect ─────────────────────────────────────────────────── */
function initHeaderScroll() {
    const header = $('.site-header');
    if (!header) return;
    const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 40);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
}

/* ─── Mobile Nav ───────────────────────────────────────────────────────────── */
function initNav() {
    const toggle = $('.nav-toggle');
    const nav    = $('.main-nav');
    if (!toggle || !nav) return;

    toggle.addEventListener('click', () => {
        const open = nav.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', String(open));
        document.body.style.overflow = open ? 'hidden' : '';
    });

    document.addEventListener('click', e => {
        if (!nav.contains(e.target) && !toggle.contains(e.target) && nav.classList.contains('is-open')) {
            nav.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }
    });

    $$('.nav-link', nav).forEach(link => {
        link.addEventListener('click', () => {
            nav.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        });
    });
}

/* ─── Typewriter ───────────────────────────────────────────────────────────── */
function initTypewriter() {
    const el = $('.tw-text');
    if (!el) return;

    const phrases = el.dataset.phrases
        ? JSON.parse(el.dataset.phrases)
        : ['PHP & Nette vývoj', 'REST API design', 'Vue.js aplikace', 'E-shop řešení', 'Webový konzultant'];

    let phraseIdx = 0, charIdx = 0, deleting = false, paused = false;

    function tick() {
        const phrase = phrases[phraseIdx];
        if (paused) { paused = false; setTimeout(tick, 1600); return; }

        if (!deleting) {
            el.textContent = phrase.slice(0, ++charIdx);
            if (charIdx === phrase.length) { deleting = true; paused = true; }
        } else {
            el.textContent = phrase.slice(0, --charIdx);
            if (charIdx === 0) {
                deleting  = false;
                phraseIdx = (phraseIdx + 1) % phrases.length;
                paused    = true;
            }
        }

        setTimeout(tick, deleting ? 42 : (paused ? 0 : 78));
    }

    setTimeout(tick, 900);
}

/* ─── Scroll Reveal ─────────────────────────────────────────────────────────── */
function initScrollReveal() {
    if (!('IntersectionObserver' in window)) {
        $$('.reveal').forEach(el => el.classList.add('is-visible'));
        return;
    }

    const obs = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.10, rootMargin: '0px 0px -50px 0px' });

    $$('.reveal').forEach(el => obs.observe(el));
}

/* ─── Animated Counters ─────────────────────────────────────────────────────── */
function initCounters() {
    const nums = $$('[data-count]');
    if (!nums.length) return;

    const obs = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const el     = entry.target;
            const target = +el.dataset.count;
            const suffix = el.dataset.suffix || '';
            const dur    = 1800;
            const start  = performance.now();
            obs.unobserve(el);

            const step = now => {
                const progress = Math.min((now - start) / dur, 1);
                const eased    = 1 - Math.pow(1 - progress, 3);
                el.textContent = Math.round(eased * target) + suffix;
                if (progress < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        });
    }, { threshold: 0.5 });

    nums.forEach(el => obs.observe(el));
}

/* ─── Skill Bars ────────────────────────────────────────────────────────────── */
function initSkillBars() {
    const bars = $$('.skills-list__fill');
    if (!bars.length) return;

    const obs = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const fill   = entry.target;
            const parent = fill.closest('.skills-list__bar');
            const level  = parent ? (parent.title || '0') : '0';
            fill.style.width = level + '%';
            obs.unobserve(fill);
        });
    }, { threshold: 0.3 });

    bars.forEach(b => obs.observe(b));
}

/* ─── Testimonials Carousel ─────────────────────────────────────────────────── */
function initTestimonialsCarousel() {
    const carousel = $('.testimonials-carousel');
    if (!carousel) return;

    const track = $('.testimonials-track', carousel);
    const dots  = $$('.carousel-dot', carousel);
    const prev  = $('.carousel-btn--prev', carousel);
    const next  = $('.carousel-btn--next', carousel);
    if (!track) return;

    const items   = $$('.testimonial-card', track);
    const count   = items.length;
    let current   = 0;
    let autoplay;

    function goTo(idx) {
        current = (idx + count) % count;
        const offset = current * (100 / count);
        track.style.transform = `translateX(-${offset}%)`;
        dots.forEach((d, i) => d.classList.toggle('active', i === current));
    }

    function startAutoplay() { autoplay = setInterval(() => goTo(current + 1), 4800); }
    function resetAutoplay()  { clearInterval(autoplay); startAutoplay(); }

    if (count > 1) {
        track.style.width = (count * 100) + '%';
        items.forEach(item => { item.style.flex = `0 0 ${100 / count}%`; });

        if (prev) prev.addEventListener('click', () => { goTo(current - 1); resetAutoplay(); });
        if (next) next.addEventListener('click', () => { goTo(current + 1); resetAutoplay(); });
        dots.forEach((d, i) => d.addEventListener('click', () => { goTo(i); resetAutoplay(); }));

        let startX = 0;
        track.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
        track.addEventListener('touchend', e => {
            const dx = e.changedTouches[0].clientX - startX;
            if (Math.abs(dx) > 50) { goTo(current + (dx < 0 ? 1 : -1)); resetAutoplay(); }
        });

        goTo(0);
        startAutoplay();
        carousel.addEventListener('mouseenter', () => clearInterval(autoplay));
        carousel.addEventListener('mouseleave', startAutoplay);
    }
}

/* ─── Project Filter ────────────────────────────────────────────────────────── */
function initProjectFilter() {
    const filterLinks = $$('[data-filter]');
    if (!filterLinks.length) return;

    filterLinks.forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const cat = link.dataset.filter;

            filterLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');

            $$('[data-category]').forEach(card => {
                const show = !cat || card.dataset.category === cat;
                if (show) {
                    card.style.display = '';
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(12px)';
                    requestAnimationFrame(() => {
                        card.style.transition = 'opacity 320ms, transform 320ms';
                        card.style.opacity    = '1';
                        card.style.transform  = 'none';
                    });
                } else {
                    card.style.transition = 'opacity 200ms';
                    card.style.opacity    = '0';
                    setTimeout(() => { card.style.display = 'none'; }, 200);
                }
            });

            const url = new URL(link.href || window.location.href);
            window.history.pushState({}, '', url);
        });
    });
}

/* ─── Lightbox ──────────────────────────────────────────────────────────────── */
function initLightbox() {
    const gallery = $('.gallery[data-lightbox]');
    if (!gallery) return;

    const items  = $$('.gallery__item', gallery);
    const images = items.map(a => a.href || a.querySelector('img')?.src || '');
    let current  = 0;

    const overlay = document.createElement('div');
    overlay.className = 'lightbox-overlay';
    overlay.innerHTML = `
        <button class="lightbox-close" aria-label="Zavřít">&times;</button>
        <button class="lightbox-prev" aria-label="Předchozí">&#8249;</button>
        <img src="" alt="Galerie" class="lightbox-img">
        <button class="lightbox-next" aria-label="Další">&#8250;</button>
    `;
    document.body.appendChild(overlay);

    const img = $('.lightbox-img', overlay);

    function show(idx) {
        current = (idx + images.length) % images.length;
        img.src = images[current];
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
    function close() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    items.forEach((item, i) => item.addEventListener('click', e => { e.preventDefault(); show(i); }));
    $('.lightbox-close', overlay).addEventListener('click', close);
    $('.lightbox-prev',  overlay).addEventListener('click', () => show(current - 1));
    $('.lightbox-next',  overlay).addEventListener('click', () => show(current + 1));
    overlay.addEventListener('click', e => { if (e.target === overlay) close(); });
    document.addEventListener('keydown', e => {
        if (!overlay.classList.contains('is-open')) return;
        if (e.key === 'Escape')    close();
        if (e.key === 'ArrowLeft') show(current - 1);
        if (e.key === 'ArrowRight') show(current + 1);
    });
}

/* ─── Cookie consent helpers ────────────────────────────────────────────────── */
function getCookie(name) {
    const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : null;
}
function setCookie(name, value, days) {
    const expires = new Date(Date.now() + days * 864e5).toUTCString();
    document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + expires + '; path=/; SameSite=Lax';
}

/* ─── GA4 loader ────────────────────────────────────────────────────────────── */
function loadGA4() {
    const placeholder = document.getElementById('ga4-placeholder');
    if (!placeholder) return;
    const tag = placeholder.dataset.gaTag;
    if (!tag) return;

    window.dataLayer = window.dataLayer || [];
    function gtag() { window.dataLayer.push(arguments); }
    gtag('js', new Date());
    gtag('config', tag, { anonymize_ip: true });

    const script = document.createElement('script');
    script.async = true;
    script.src = 'https://www.googletagmanager.com/gtag/js?id=' + tag;
    document.head.appendChild(script);
}

/* ─── Cookie Banner ─────────────────────────────────────────────────────────── */
function initCookieBanner() {
    const banner = document.getElementById('cookie-banner');
    if (!banner) return;

    const consent = getCookie('cookie_consent');

    // Load GA4 immediately if already accepted
    if (consent === 'accepted') {
        loadGA4();
    }

    // Hide banner if decision already made
    if (consent) {
        banner.remove();
        return;
    }

    // Show banner (it starts with `hidden` attribute)
    banner.removeAttribute('hidden');

    function dismiss(decision) {
        setCookie('cookie_consent', decision, 365);
        if (decision === 'accepted') loadGA4();
        banner.classList.add('hidden');
        setTimeout(() => banner.remove(), 450);
    }

    const acceptBtn  = $('.cookie-banner__accept',  banner);
    const declineBtn = $('.cookie-banner__decline', banner);
    if (acceptBtn)  acceptBtn.addEventListener('click',  () => dismiss('accepted'));
    if (declineBtn) declineBtn.addEventListener('click', () => dismiss('declined'));
}

/* ─── Flash auto-dismiss ────────────────────────────────────────────────────── */
function initFlashes() {
    $$('.flash').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 400ms, transform 400ms';
            el.style.opacity    = '0';
            el.style.transform  = 'translateY(-8px)';
            setTimeout(() => el.remove(), 420);
        }, 5000);
    });
}

/* ─── FAQ accordion ─────────────────────────────────────────────────────────── */
function initFaq() {
    $$('.faq-item').forEach(item => {
        const summary = item.querySelector('.faq-item__question');
        if (!summary) return;
        summary.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); item.open = !item.open; }
        });
    });
}

/* ─── TOC active highlight ──────────────────────────────────────────────────── */
function initTocHighlight() {
    const toc = $('.toc-list');
    if (!toc) return;

    const links    = $$('a', toc);
    const headings = links.map(a => {
        const id = a.getAttribute('href')?.slice(1);
        return id ? document.getElementById(id) : null;
    }).filter(Boolean);

    if (!headings.length) return;

    const obs = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            links.forEach(l => l.classList.remove('active'));
            const link = toc.querySelector(`a[href="#${entry.target.id}"]`);
            if (link) link.classList.add('active');
        });
    }, { rootMargin: '-20% 0px -70% 0px' });

    headings.forEach(h => obs.observe(h));
}

/* ─── Smooth scroll for anchor links ───────────────────────────────────────── */
document.addEventListener('click', e => {
    const link = e.target.closest('a[href^="#"]');
    if (!link) return;
    const target = document.querySelector(link.getAttribute('href'));
    if (!target) return;
    e.preventDefault();
    const top = target.getBoundingClientRect().top + window.scrollY - 88;
    window.scrollTo({ top, behavior: 'smooth' });
});

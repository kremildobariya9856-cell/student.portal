/* =========================================================
   CHARUSAT Student Hub – script.js
   Adds: theme switcher (localStorage), hamburger menu,
   notification banner, collapsible FAQ, events slider,
   and modal popups on forms.
   No existing HTML/CSS files are modified; every element
   these features need is created here at runtime, and
   any extra styling they need is injected as a <style> tag.
   ========================================================= */

document.addEventListener('DOMContentLoaded', function () {
    injectRuntimeStyles();
    initThemeSwitcher();
    initHamburgerMenu();
    initNotificationBanner();
    initFAQAccordion();
    initEventsSlider();
    initFormModals();
});

/* ---------------------------------------------------------
   0. Runtime styles for the widgets below
   --------------------------------------------------------- */
function injectRuntimeStyles() {
    var css = [
        /* Theme toggle button */
        '#theme-toggle-btn{cursor:pointer;border:1px solid #ffffff;background:transparent;',
        'color:#ffffff;padding:0.4rem 0.75rem;border-radius:4px;font-size:0.9rem;margin-top:0.5rem;}',
        '#theme-toggle-btn:hover{background:rgba(255,255,255,0.15);}',

        /* Dark theme palette */
        'body.dark-theme{background-color:#12161c;color:#e6e6e6;}',
        'body.dark-theme header{background-color:#0b0e13;}',
        'body.dark-theme footer{background-color:#0b0e13;color:#cccccc;}',
        'body.dark-theme .card{background-color:#1c222b;border-color:#333333;}',
        'body.dark-theme input,body.dark-theme textarea{background-color:#1c222b;color:#e6e6e6;border-color:#444444;}',
        'body.dark-theme nav a{color:#e6e6e6;}',
        'body.dark-theme h1,body.dark-theme h2,body.dark-theme h3{color:#8fb4ff;}',
        'body.dark-theme .faq-answer{color:#dcdcdc;}',

        /* Hamburger button (mobile only) */
        '#hamburger-btn{display:none;cursor:pointer;background:transparent;border:1px solid #ffffff;',
        'color:#ffffff;font-size:1.2rem;padding:0.25rem 0.6rem;border-radius:4px;}',
        '@media (max-width:767px){',
        '  #hamburger-btn{display:inline-block;order:-1;align-self:flex-end;margin-bottom:0.5rem;}',
        '  header nav ul.js-collapsible,body > nav > ul.js-collapsible{display:none;}',
        '  header nav ul.js-collapsible.nav-open,body > nav > ul.js-collapsible.nav-open{display:flex;}',
        '}',

        /* Notification banner */
        '#notification-banner{background-color:#e9c46a;color:#1d1d1d;padding:0.75rem 1rem;',
        'display:flex;justify-content:space-between;align-items:center;font-size:0.95rem;}',
        '#notification-banner button{background:transparent;border:none;font-size:1.1rem;cursor:pointer;',
        'color:#1d1d1d;font-weight:bold;}',

        /* FAQ accordion */
        '.faq-question{cursor:pointer;display:flex;justify-content:space-between;align-items:center;',
        'padding:0.6rem 0;border-bottom:1px solid #dddddd;}',
        '.faq-question .faq-icon{font-weight:bold;margin-left:0.75rem;}',
        '.faq-answer{overflow:hidden;max-height:0;transition:max-height 0.25s ease;padding:0 0 0 0;}',
        '.faq-answer.open{max-height:200px;padding:0.5rem 0 1rem 0;}',

        /* Events slider */
        '.slider-wrap{position:relative;max-width:600px;margin-top:1rem;}',
        '.slider-track{border:1px solid #cccccc;border-radius:8px;padding:1.25rem;min-height:3rem;',
        'background-color:#f9f9f9;}',
        '.slider-controls{display:flex;justify-content:space-between;align-items:center;margin-top:0.6rem;}',
        '.slider-controls button{background-color:#1d3557;color:#ffffff;border:none;border-radius:4px;',
        'padding:0.4rem 0.8rem;cursor:pointer;}',
        '.slider-controls button:hover{background-color:#163050;}',
        '.slider-dots{display:flex;gap:0.4rem;}',
        '.slider-dots span{width:8px;height:8px;border-radius:50%;background-color:#cccccc;display:inline-block;}',
        '.slider-dots span.active{background-color:#1d3557;}',

        /* Modal popup */
        '.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.55);display:flex;',
        'align-items:center;justify-content:center;z-index:1000;}',
        '.modal-box{background:#ffffff;color:#333333;border-radius:8px;padding:1.5rem;max-width:360px;',
        'width:90%;text-align:center;box-shadow:0 10px 25px rgba(0,0,0,0.3);}',
        'body.dark-theme .modal-box{background:#1c222b;color:#e6e6e6;}',
        '.modal-box button{margin-top:1rem;background-color:#1d3557;color:#ffffff;border:none;',
        'border-radius:4px;padding:0.5rem 1.25rem;cursor:pointer;}'
    ].join('\n');

    var styleTag = document.createElement('style');
    styleTag.id = 'js-injected-styles';
    styleTag.textContent = css;
    document.head.appendChild(styleTag);
}

/* ---------------------------------------------------------
   1. Theme switcher (light/dark, persisted in localStorage)
   --------------------------------------------------------- */
function initThemeSwitcher() {
    var header = document.querySelector('header');
    if (!header) return;

    var btn = document.createElement('button');
    btn.id = 'theme-toggle-btn';
    btn.type = 'button';

    var applyTheme = function (theme) {
        if (theme === 'dark') {
            document.body.classList.add('dark-theme');
            btn.textContent = '☀️ Light Mode';
        } else {
            document.body.classList.remove('dark-theme');
            btn.textContent = '🌙 Dark Mode';
        } 
    };

    var savedTheme = localStorage.getItem('studenthub-theme') || 'light';
    applyTheme(savedTheme);

    btn.addEventListener('click', function () {
        var next = document.body.classList.contains('dark-theme') ? 'light' : 'dark';
        applyTheme(next);
        localStorage.setItem('studenthub-theme', next);
    });

    header.appendChild(btn);
}

/* ---------------------------------------------------------
   2. Hamburger menu for the nav list on small screens
   --------------------------------------------------------- */
function initHamburgerMenu() {
    var navList = document.querySelector('header nav ul') || document.querySelector('body > nav > ul');
    if (!navList) return;

    navList.classList.add('js-collapsible');

    var toggleBtn = document.createElement('button');
    toggleBtn.id = 'hamburger-btn';
    toggleBtn.type = 'button';
    toggleBtn.setAttribute('aria-label', 'Toggle navigation menu');
    toggleBtn.setAttribute('aria-expanded', 'false');
    toggleBtn.textContent = '☰ Menu';

    toggleBtn.addEventListener('click', function () {
        var isOpen = navList.classList.toggle('nav-open');
        toggleBtn.setAttribute('aria-expanded', String(isOpen));
    });

    navList.parentElement.insertBefore(toggleBtn, navList);
}

/* ---------------------------------------------------------
   3. Notification banner (dismissible, remembered for session)
   --------------------------------------------------------- */
function initNotificationBanner() {
    if (sessionStorage.getItem('studenthub-banner-dismissed') === 'true') return;

    var banner = document.createElement('div');
    banner.id = 'notification-banner';
    banner.setAttribute('role', 'status');

    var text = document.createElement('span');
    text.textContent = '📢 Welcome to CHARUSAT Student Hub! Check the Events page for upcoming activities.';

    var closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.setAttribute('aria-label', 'Dismiss notification');
    closeBtn.textContent = '✕';
    closeBtn.addEventListener('click', function () {
        banner.remove();
        sessionStorage.setItem('studenthub-banner-dismissed', 'true');
    });

    banner.appendChild(text);
    banner.appendChild(closeBtn);

    var header = document.querySelector('header');
    if (header && header.parentElement) {
        header.parentElement.insertBefore(banner, header.nextSibling);
    } else {
        document.body.insertBefore(banner, document.body.firstChild);
    }
}

/* ---------------------------------------------------------
   4. Collapsible FAQ (turns the <dl><dt><dd> pairs into an
      accordion — only runs on pages that have one, e.g. faq.html)
   --------------------------------------------------------- */
function initFAQAccordion() {
    var dl = document.querySelector('main dl');
    if (!dl) return;

    var terms = Array.prototype.slice.call(dl.querySelectorAll('dt'));

    terms.forEach(function (dt) {
        var dd = dt.nextElementSibling;
        if (!dd || dd.tagName !== 'DD') return;

        dt.classList.add('faq-question');
        dd.classList.add('faq-answer');
        dt.setAttribute('tabindex', '0');
        dt.setAttribute('role', 'button');
        dt.setAttribute('aria-expanded', 'false');

        var icon = document.createElement('span');
        icon.className = 'faq-icon';
        icon.textContent = '+';
        dt.appendChild(icon);

        var toggle = function () {
            var isOpen = dd.classList.toggle('open');
            dt.setAttribute('aria-expanded', String(isOpen));
            icon.textContent = isOpen ? '−' : '+';
        };

        dt.addEventListener('click', toggle);
        dt.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggle();
            }
        });
    });
}

/* ---------------------------------------------------------
   5. Events slider (turns the events <ul> into a one-at-a-time
      slider with prev/next controls and dots)
   --------------------------------------------------------- */
function initEventsSlider() {
    var list = document.querySelector('main ul');
    if (!list || !/events/i.test(document.title)) return;

    var items = Array.prototype.slice.call(list.querySelectorAll('li')).map(function (li) {
        return li.textContent.trim();
    });
    if (items.length === 0) return;

    var wrap = document.createElement('div');
    wrap.className = 'slider-wrap';

    var track = document.createElement('div');
    track.className = 'slider-track';
    track.textContent = items[0];

    var controls = document.createElement('div');
    controls.className = 'slider-controls';

    var prevBtn = document.createElement('button');
    prevBtn.type = 'button';
    prevBtn.textContent = '‹ Prev';

    var dots = document.createElement('div');
    dots.className = 'slider-dots';
    items.forEach(function () {
        var dot = document.createElement('span');
        dots.appendChild(dot);
    });

    var nextBtn = document.createElement('button');
    nextBtn.type = 'button';
    nextBtn.textContent = 'Next ›';

    controls.appendChild(prevBtn);
    controls.appendChild(dots);
    controls.appendChild(nextBtn);

    wrap.appendChild(track);
    wrap.appendChild(controls);

    list.parentElement.insertBefore(wrap, list);
    list.style.display = 'none';

    var current = 0;
    var render = function () {
        track.textContent = items[current];
        Array.prototype.forEach.call(dots.children, function (dot, i) {
            dot.classList.toggle('active', i === current);
        });
    };
    render();

    var goTo = function (index) {
        current = (index + items.length) % items.length;
        render();
    };

    prevBtn.addEventListener('click', function () { goTo(current - 1); });
    nextBtn.addEventListener('click', function () { goTo(current + 1); });
    Array.prototype.forEach.call(dots.children, function (dot, i) {
        dot.addEventListener('click', function () { goTo(i); });
    });

    var timer = setInterval(function () { goTo(current + 1); }, 5000);
    wrap.addEventListener('mouseenter', function () { clearInterval(timer); });
}

/* ---------------------------------------------------------
   6. Modal popup on form submission (contact / feedback / login
      / register — any form with action="#")
   --------------------------------------------------------- */
function initFormModals() {
    var forms = document.querySelectorAll('main form[action="#"]');
    if (!forms.length) return;

    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            showModal('Thank you!', 'Your form has been submitted successfully.');
            form.reset();
        });
    });
}

function showModal(title, message) {
    var overlay = document.createElement('div');
    overlay.className = 'modal-overlay';

    var box = document.createElement('div');
    box.className = 'modal-box';

    var h = document.createElement('h3');
    h.textContent = title;

    var p = document.createElement('p');
    p.textContent = message;

    var closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.textContent = 'Close';
    closeBtn.addEventListener('click', function () { overlay.remove(); });

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) overlay.remove();
    });

    box.appendChild(h);
    box.appendChild(p);
    box.appendChild(closeBtn);
    overlay.appendChild(box);
    document.body.appendChild(overlay);
}
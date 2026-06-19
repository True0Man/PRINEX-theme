<?php
/**
 * PRINEX — eksport Code Snippet (mirror do wersjonowania, NIE zrodlo prawdy)
 *
 * ID snippetu (wp_snippets.id): 11
 * Tytul:                       PRINEX — Strona główna (CSS/JS z eksportu Claude Design)
 * Typ:                         PHP (snippet typu code-snippets; jesli echo CSS/JS, zaznaczone nizej)
 * Scope:                       front-end — wykonuje sie WYLACZNIE na froncie (nie w wp-admin)
 * Status:                      AKTYWNY
 *
 * UWAGA: zrodlem prawdy jest baza WP (wtyczka Code Snippets). Ten plik to
 * mirror do wersjonowania/code review. Edycja tego pliku NIE zmienia
 * dzialania strony — trzeba wkleic zmiany z powrotem do wp-admin > Code Snippets,
 * lub zaktualizowac wp_snippets.code (np. przez wp-cli/wp eval).
 */

add_action( 'wp_head', function() {
	if ( ! is_front_page() ) {
		return;
	}
	echo '<style id="prinex-home-css">';
	?>
/* navy/green/bg-light/body/white/line — teraz globalne (generatepress-child/style.css).
   Zostaja tu tylko pastelowe tla kafli, specyficzne dla strony glownej. */
:root{
  --t-gray:#F4F6F7;
  --t-navy:#E1E9F1;
  --t-beige:#F0EBE0;
  --t-gold:#F4EEDD;
  --t-white:#FFFFFF;
  --t-green:#EAF2DD;
}

*{margin:0;padding:0;box-sizing:border-box;}
html{ -webkit-font-smoothing:antialiased; text-rendering:optimizeLegibility; }
/* font-family/color/font-size/line-height body — teraz globalne. Tu zostaje
   tylko tlo #c9cfd4 (i tak nadpisywane przez body.home nizej w tym pliku). */
body{
  background:#c9cfd4;
}
img{display:block;max-width:100%;}
/* WAZNE: zawezone do .prinex-home-section — bare "a{color:inherit}" mialoby
   specificznosc (0,0,1), nizsza niz ".prinex-nav a" (0,1,1), wiec w praktyce
   nie gasilo navy menu (bezpieczne "przez przypadek"), ale zawężamy explicit
   dla poprawnosci-przez-konstrukcje, tak jak na kategorii (patrz audyt). */
.prinex-home-section a{color:inherit;text-decoration:none;}

/* ---------- layout shell ---------- */
.artboard{
  width:1680px;
  margin:0 auto;
  background:var(--bg-light);
}
/* WAZNE: zawezone do .prinex-home-section — bare ".container"/".band" lapaly
   teoretycznie #page (ktory ma klase "container" w "site grid-container
   container hfeed"). Tu bylo bezpieczne dzieki nizszej specyficznosci niz
   GP-owe ".container.grid-container", ale identyczny wzorzec na kategorii
   ZEPSUL strone (rownanie specyficznosci, wygrywal porzadek zaladowania) —
   zawężamy tu też, zeby nie powtorzyc tego budu jesli ktos kiedys podniesie
   specyficznosc przy kopiowaniu wzorca. */
.prinex-home-section .band{ width:1440px; margin:0 auto; }      /* full-width section backgrounds */
.prinex-home-section .container{ width:1400px; margin:0 auto; } /* content container */

/* sig oraz h1-h3 (kolor/weight) — teraz globalne (generatepress-child/style.css) */
.h1{ font-size:52px; font-weight:700; line-height:1.1; }
.h2{ font-size:38px; font-weight:700; line-height:1.12; }
.h3{ font-size:24px; font-weight:600; line-height:1.2; }
.lead{ font-size:20px; font-weight:400; line-height:1.5; color:var(--body); }
.label{ font-size:14px; font-weight:600; letter-spacing:.04em; text-transform:uppercase; }
.eyebrow{ font-size:14px; font-weight:600; letter-spacing:.08em; text-transform:uppercase; color:var(--green); }

/* ---------- buttons ---------- */
.btn{
  display:inline-flex; align-items:center; gap:10px;
  font-family:inherit; font-size:16px; font-weight:600;
  text-transform:uppercase; letter-spacing:.03em;
  padding:15px 28px; border-radius:6px; cursor:pointer;
  border:2px solid transparent; transition:background .2s ease, transform .2s ease, box-shadow .2s ease, color .2s ease;
}
.btn svg{ width:18px; height:18px; transition:transform .2s ease; }
.btn:hover svg{ transform:translateX(4px); }
.btn-primary{ background:var(--green); color:#fff; }
.btn-primary:hover{ background:var(--green-dark); transform:translateY(-2px); box-shadow:0 10px 24px rgba(120,184,51,.34); }

/* CTA button with expanding chevron cube */
.btn-cta{
  position:relative; display:inline-flex; align-items:center; justify-content:flex-start;
  background:var(--green); color:#fff; border-radius:11px;
  font-family:inherit; font-weight:700; font-size:20px; text-transform:uppercase; letter-spacing:.03em;
  padding:16px 70px 16px 28px; cursor:pointer; overflow:hidden; user-select:none;
  transition:transform .5s ease, background .5s ease, box-shadow .5s ease;
}
.btn-cta-label{ transition:opacity .35s ease; white-space:nowrap; }
.btn-cta:hover .btn-cta-label{ opacity:0; }
.btn-cta-cube{
  position:absolute; top:4px; right:4px; bottom:4px; width:50px;
  background:rgba(255,255,255,.22); border-radius:8px;
  display:flex; align-items:center; justify-content:center;
  transition:width .5s ease, background .5s ease;
}
.btn-cta:hover .btn-cta-cube{ width:calc(100% - 8px); background:rgba(255,255,255,.30); }
.btn-cta-cube svg{ width:24px; height:24px; stroke:#fff; stroke-width:2.4; fill:none; }
.btn-cta:hover{ box-shadow:0 10px 24px rgba(120,184,51,.34); }
.btn-cta:active{ transform:scale(.95); }
.btn-outline{ background:transparent; color:var(--navy); border-color:var(--navy); }
.btn-outline:hover{ background:var(--navy); color:#fff; }
.btn-sm{ padding:11px 22px; font-size:14px; }

/* ---------- icons ---------- */
.ic{ stroke:currentColor; stroke-width:1.75; fill:none; stroke-linecap:round; stroke-linejoin:round; }

/* ---------- placeholder imagery ---------- */
.ph{
  background-color:#eef1f3;
  background-image:repeating-linear-gradient(45deg, rgba(11,69,125,.045) 0 11px, rgba(11,69,125,.085) 11px 22px);
  display:flex; align-items:center; justify-content:center;
  color:rgba(11,69,125,.55);
  font-family:'SFMono-Regular',Menlo,Consolas,monospace; font-size:12px;
  letter-spacing:.04em; text-transform:uppercase; text-align:center;
  border-radius:6px;
}

/* ===================================================== TOP BAR */
.topbar{
  background:var(--bg-light); color:var(--navy);
  height:44px; display:flex; align-items:center;
  font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:.02em;
  white-space:nowrap;
}
.topbar .container{ display:flex; align-items:center; justify-content:space-between; }
.topbar .tb-left{ opacity:1; }
.topbar .tb-mid{ display:flex; align-items:center; gap:24px; }
.topbar .tb-item{ display:flex; align-items:center; gap:8px; }
.topbar .tb-item svg{ width:17px; height:17px; color:var(--green); }
.topbar .tb-sep{ color:var(--navy); opacity:.28; }
.topbar .tb-right{ display:flex; align-items:center; gap:20px; }
.topbar .tb-right a{ display:flex; align-items:center; gap:7px; opacity:1; transition:color .15s; }
.topbar .tb-right a svg{ width:15px; height:15px; color:var(--green); }
.topbar .tb-right a:hover{ color:var(--green-dark); }

/* ===================================================== HEADER (stickerapp-style, full-width white bar) */
.header{ background:var(--white); border-bottom:1px solid #e4e8eb; width:100%; position:sticky; top:0; z-index:100; }
.header.stuck{ box-shadow:0 6px 22px rgba(11,69,125,.10); }
.header .container{
  height:108px; display:grid; grid-template-columns:1fr auto 1fr; align-items:center;
}
.nav{ display:flex; align-items:center; gap:30px; margin-left:-14px; }
.nav a{
  font-size:16px; font-weight:500; color:var(--navy); letter-spacing:.005em;
  display:flex; align-items:center; gap:8px; position:relative; padding:9px 14px;
  white-space:nowrap; transition:color .2s ease;
}
.nav a.active{ font-weight:600; }
.nav a::after{
  content:""; position:absolute; left:14px; width:0; bottom:5px; height:2px; background:var(--green); transition:width .2s ease;
}
.nav a.active::after{ width:calc(100% - 28px); }
.nav a svg{ width:12px; height:12px; opacity:.55; }
.nav a:hover{ color:var(--green); }
.nav a:hover::after{ width:calc(100% - 28px); }
.brand{ display:flex; justify-content:center; }
.brand img{ width:230px; height:auto; }
.header-actions{ display:flex; align-items:center; justify-content:flex-end; gap:32px; }
.action{ display:flex; align-items:center; gap:9px; color:var(--navy); font-weight:500; font-size:15px; cursor:pointer; transition:color .15s; }
.action svg{ width:23px; height:23px; }
.action:hover{ color:var(--green-dark); }
.cart{ position:relative; }
.cart .count{
  position:absolute; top:-7px; right:-10px; background:var(--green); color:#fff;
  font-size:11px; font-weight:700; min-width:18px; height:18px; border-radius:9px;
  display:flex; align-items:center; justify-content:center; padding:0 5px;
}

/* ===================================================== HERO */
.hero{ background:var(--bg-light); }
.hero .container{
  display:grid; grid-template-columns:1fr 1fr; gap:60px; align-items:center;
  padding:84px 0 88px;
}
.hero h1{ margin-bottom:22px; }
.hero-rating{ display:flex; align-items:center; gap:12px; margin:-6px 0 30px; }
.stars{ display:inline-flex; gap:3px; }
.stars svg{ width:21px; height:21px; }
.rating-text{ font-size:14px; color:#8a939c; font-weight:500; letter-spacing:.01em; }
.hero .lead{ max-width:480px; margin-bottom:36px; }
.hero-cta{ display:flex; align-items:center; gap:28px; }
.hero-link{
  display:inline-flex; align-items:center; gap:8px;
  font-size:15px; font-weight:600; letter-spacing:.04em; text-transform:uppercase; color:var(--navy);
  transition:color .2s ease;
}
.hero-link span{ position:relative; }
.hero-link span::after{
  content:""; position:absolute; left:0; right:100%; bottom:-3px; height:2px;
  background:var(--green); transition:right .2s ease;
}
.hero-link svg{ width:16px; height:16px; transition:transform .2s ease; }
.hero-link:hover{ color:var(--green); }
.hero-link:hover span::after{ right:0; }
.hero-link:hover svg{ transform:translateX(4px); }
.hero-art{ position:relative; }
.hero-art .ph{ height:420px; }
.hero-art .badge3d{
  position:absolute; top:22px; left:22px; background:var(--navy); color:#fff;
  font-size:13px; font-weight:700; letter-spacing:.05em; text-transform:uppercase;
  padding:8px 14px; border-radius:5px; white-space:nowrap;
}

/* badge bar under hero */
.badgebar{ background:var(--white); }
.badgebar .container{ display:grid; grid-template-columns:repeat(4,1fr); }
.badge-item{
  display:flex; align-items:center; justify-content:center; gap:15px;
  padding:26px 22px; border-right:1px solid #eef1f3; text-align:left;
}
.badge-item:last-child{ border-right:none; }
.badge-item svg{ width:42px; height:42px; color:var(--green); flex-shrink:0; }
.badge-item span{ font-size:15px; font-weight:600; color:var(--navy); line-height:1.3; }

/* ===================================================== PRODUCT TILES */
.products{ background:transparent; }
.products .container{ padding:90px 0 96px; }
.sec-head{ margin-bottom:46px; }
.sec-head .h2{ margin-bottom:0; }
.sec-head .sec-sub{ font-size:18px; font-weight:400; color:#6b7680; margin-top:14px; }
.sec-head.center{ text-align:center; }
.sec-head.center .sec-sub{ max-width:620px; margin-left:auto; margin-right:auto; }

.tile-grid{ display:grid; grid-template-columns:repeat(4,1fr); gap:30px 28px; }
.product{ cursor:pointer; display:flex; flex-direction:column; }
.product-img{
  position:relative; width:100%; aspect-ratio:1/1; border-radius:12px; overflow:hidden;
  box-shadow:0 2px 12px rgba(11,69,125,.06);
  transition:box-shadow .18s ease, transform .18s ease;
}
.product-img .ph{
  position:absolute; inset:0; width:100%; height:100%; border-radius:0;
  transform-origin:center; transition:transform .18s ease;
}
.product-img .ph2{ opacity:0; z-index:2; transition:transform .18s ease, opacity .3s ease; }
.product:hover .product-img .ph2{ opacity:1; }
.product:hover .product-img{ box-shadow:0 22px 46px rgba(11,69,125,.24); transform:translateY(-8px); }
.product:hover .product-img .ph{ transform:scale(1.08); }
.tile-badge{
  position:absolute; top:12px; left:12px; z-index:3;
  font-size:12px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; color:#fff;
  padding:6px 12px; border-radius:6px 6px 6px 2px; box-shadow:0 4px 10px rgba(0,0,0,.18);
}
.tile-badge.new{ background:#e23b3b; }
.tile-badge.popular{ background:#f08a24; }
.product-name{ font-size:19px; font-weight:600; color:var(--navy); margin-top:18px; }
.product-desc{ font-size:14px; font-weight:400; color:#6b7680; margin-top:4px; line-height:1.45; }

/* ===================================================== FAQ (42/58) */
.faq{ background:var(--white); }
.faq .container{ padding:88px 0; display:grid; grid-template-columns:minmax(0,42fr) minmax(0,58fr); gap:60px; align-items:start; }
.faq-left .sec-head{ margin-bottom:0; }
.faq-desc{ font-size:18px; color:#6b7680; margin-top:18px; line-height:1.55; max-width:380px; }
.faq-desc a{ color:var(--green); font-weight:600; transition:color .15s; }
.faq-desc a:hover{ text-decoration:underline; }

.faq-right{ padding-right:8px; }
.acc-item{ border-bottom:1px solid #e4e8eb; border-radius:8px; transition:background .2s ease; }
.acc-item:first-child{ border-top:1px solid #e4e8eb; }
.acc-item:hover{ background:#f3f7ec; }
.acc-q{
  width:100%; background:none; border:none; font-family:inherit; cursor:pointer;
  display:flex; align-items:center; justify-content:space-between; gap:24px;
  padding:22px 4px; font-size:18px; font-weight:600; color:var(--navy); text-align:left;
  transition:color .15s;
}
.acc-q:hover{ color:var(--green-dark); }
.acc-q{ padding-left:16px; padding-right:16px; }
/* FIX: nadpisuje domyślny ciemny skin przycisku GeneratePress (button:hover/button:focus, #3f4047)
   ktory ma wyzszy priorytet niz .acc-q{background:none} bo zawiera selektor elementu */
.acc-q:hover, .acc-q:focus, .acc-q:active{ background:none; outline:none; }
.acc-q:focus{ color:var(--navy); }
.acc-q svg{ width:20px; height:20px; color:var(--green); flex-shrink:0; transition:transform .3s ease; }
.acc-item.open .acc-q svg{ transform:rotate(180deg); }
.acc-panel{
  display:grid; grid-template-rows:0fr; opacity:0;
  transition:grid-template-rows .3s ease, opacity .3s ease;
}
.acc-item.open .acc-panel{ grid-template-rows:1fr; opacity:1; }
.acc-inner{ overflow:hidden; }
.acc-a{ padding:0 40px 24px 16px; font-size:16px; line-height:1.6; color:var(--body); }

.faq-illu{ margin-top:34px; max-width:200px; }
.faq-illu svg{ width:100%; height:auto; display:block; }

.faq-more{ display:inline-flex; align-items:center; gap:8px; margin:26px 0 0 16px;
  font-size:15px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:var(--green); transition:color .15s, gap .2s ease; }
.faq-more svg{ width:16px; height:16px; }
.faq-more:hover{ color:var(--green-dark); gap:12px; }

/* ===================================================== ORDER STEPS */
.ordersec{ background:var(--white); }
.ordersec .container{ padding:84px 0; display:grid; grid-template-columns:1fr 1fr; gap:64px; align-items:center; }
.ordersec .sig{ margin-bottom:0; }
.order-left .h2{ margin-bottom:0; }
.order-lead{ font-size:18px; color:#6b7680; margin-top:18px; line-height:1.55; max-width:420px; }
.order-left .btn-cta{ margin-top:32px; }
.order-eyebrow{ font-size:15px; font-weight:700; color:var(--green); text-transform:uppercase; letter-spacing:.05em; margin-bottom:22px; }
.order-steps{ display:flex; flex-direction:column; gap:0; }
.order-step{ display:flex; align-items:flex-start; gap:18px; position:relative; }
.order-step .step-rail{ display:flex; flex-direction:column; align-items:center; flex-shrink:0; }
.order-step .step-num{
  width:46px; height:46px; border-radius:50%; background:var(--navy); color:#fff;
  display:flex; align-items:center; justify-content:center; font-size:18px; font-weight:700; flex-shrink:0;
}
.order-step .step-line{ width:2px; flex:1; min-height:24px; background:#d6dce0; margin:6px 0; }
.order-step:last-child .step-line{ display:none; }
.order-step .step-body{ padding:9px 0 26px; display:flex; align-items:flex-start; gap:14px; }
.order-step:last-child .step-body{ padding-bottom:0; }
.order-step .step-ic{ width:26px; height:26px; color:var(--green); flex-shrink:0; }
.order-step .step-ic svg{ width:26px; height:26px; }
.order-step h4{ font-size:18px; font-weight:700; color:var(--navy); line-height:1.3; }

/* ===================================================== CLIENTS */
.clients{ background:transparent; }
.clients .container{ padding:88px 0; }
.clients .sec-head{ margin-bottom:44px; max-width:680px; }
.clients .sec-sub{ font-size:18px; font-weight:400; color:#6b7680; margin-top:14px; line-height:1.55; }
.clients-strip{ width:100%; height:96px; }

/* ===================================================== REVIEWS */
.reviews{ background:transparent; }
.reviews .container{ padding:20px 0 96px; }
.reviews-grid{ display:grid; grid-template-columns:repeat(4,1fr); gap:28px; }

/* reviews carousel */
.reviews-carousel{
  overflow:hidden;
  -webkit-mask-image:linear-gradient(90deg,transparent 0,#000 6%,#000 94%,transparent 100%);
          mask-image:linear-gradient(90deg,transparent 0,#000 6%,#000 94%,transparent 100%);
}
.reviews-track{ display:flex; gap:28px; will-change:transform; }
.reviews-track .review-card{ flex:0 0 329px; width:329px; }
.reviews-photo{ height:192px; margin-top:40px; }
.review-card{
  background:var(--white); border-radius:12px; padding:30px 26px;
  box-shadow:0 2px 12px rgba(11,69,125,.06);
  transition:box-shadow .18s ease, transform .18s ease;
  display:flex; flex-direction:column; gap:18px;
}
.review-card:hover{ box-shadow:0 22px 46px rgba(11,69,125,.18); transform:translateY(-8px); }
.review-stars{ display:inline-flex; gap:3px; }
.review-stars svg{ width:18px; height:18px; }
.review-text{ font-size:15px; color:var(--body); line-height:1.6; flex:1; }
.review-author{ font-size:14px; font-weight:700; color:var(--navy); }
.review-author span{ display:block; font-weight:500; color:#8a939c; font-size:13px; margin-top:3px; }
.why .container{ padding:30px 0 96px; }
.why-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:48px; }
.why-col .why-ic{
  width:64px; height:64px; border-radius:12px; background:var(--white);
  box-shadow:0 2px 12px rgba(11,69,125,.06);
  display:flex; align-items:center; justify-content:center; margin-bottom:22px;
}
/* (why styles moved above) */

/* ===================================================== WHY (50/50, image left, content right) */
.why{ background:transparent; }
.why .container{ padding:90px 0; display:grid; grid-template-columns:1fr 1fr; gap:64px; align-items:start; }
.why-art .ph{ height:500px; }
.why-content .sec-head{ margin-bottom:0; }
.why-desc{ font-size:17px; color:var(--body); margin-top:18px; line-height:1.6; max-width:540px; }
.why-features{ display:grid; grid-template-columns:1fr 1fr; gap:30px 38px; margin-top:38px; }
.why-feature{ display:flex; align-items:flex-start; gap:16px; }
.why-feature .wf-ic{
  width:50px; height:50px; border-radius:11px; background:var(--white); flex-shrink:0;
  box-shadow:0 2px 12px rgba(11,69,125,.07);
  display:flex; align-items:center; justify-content:center;
}
.why-feature .wf-ic svg{ width:26px; height:26px; color:var(--green); }
.why-feature .wf-text{ display:flex; flex-direction:column; }
.why-feature h4{ font-size:18px; font-weight:700; color:var(--navy); margin-bottom:6px; }
.why-feature p{ font-size:15px; color:#5a6570; line-height:1.5; }

/* ===================================================== EDU (50/50, image left, text right) */
.edu{ background:var(--bg-light); }
.edu .container{
  padding:88px 0; display:grid; grid-template-columns:1fr 1fr; gap:64px; align-items:center;
}
.edu .edu-text{ max-width:560px; }
.edu .edu-text p{ font-size:17px; color:var(--body); margin-top:18px; }
.edu .edu-text p + p{ margin-top:14px; }
.edu .ph{ height:440px; }

/* ===================================================== TRUST */
.trust{ background:var(--white); }
.trust .container{ padding:54px 0; display:grid; grid-template-columns:repeat(4,1fr); gap:36px; }
.trust-item{ display:flex; align-items:flex-start; gap:16px; }
.trust-item .t-ic{ flex-shrink:0; width:48px; height:48px; border-radius:10px;
  background:var(--bg-light); display:flex; align-items:center; justify-content:center; }
.trust-item .t-ic svg{ color:var(--green); }
.trust-item .t-ic svg{ width:26px; height:26px; color:var(--green); }
.trust-item h4{ font-size:17px; font-weight:700; color:var(--navy); margin-bottom:5px; }
.trust-item p{ font-size:15px; color:#5a6570; line-height:1.5; }

/* ===================================================== FOOTER */
.footer{ background:var(--bg-light); color:var(--navy); border-top:1px solid #d6dce0; }
.footer .container{ padding:64px 0 0; }
.footer-top{ display:grid; grid-template-columns:1.4fr 1fr 1fr 1fr 1fr; gap:40px; padding-bottom:54px; }
.footer-brand img{ width:200px; margin-bottom:24px; }
.footer-brand .sig{ margin-bottom:18px; }
.footer-brand p{ font-size:15px; color:#5a6570; max-width:280px; line-height:1.6; }
.footer-col h5{ font-size:14px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; margin-bottom:18px; color:var(--navy); }
.footer-col a{ display:block; font-size:15px; color:#5a6570; margin-bottom:12px; transition:color .15s; }
.footer-col a:hover{ color:var(--green-dark); }
.footer-bottom{ border-top:1px solid rgba(11,69,125,.16); padding:22px 0; display:flex; justify-content:space-between; align-items:center; }
.footer-bottom p{ font-size:14px; color:#5a6570; }
.footer-bottom .fb-right{ display:flex; gap:22px; }
.footer-bottom .fb-right a{ font-size:14px; color:#5a6570; }
.footer-bottom .fb-right a:hover{ color:var(--green-dark); }

/* PRINEX Claude Code addition — apply the same --bg-light value to the real <body> on the front page (original export used an inline body style for this, not reproducible via theme body tag) */
body.home{ background:var(--bg-light); }

/* GeneratePress (separate-containers) daje .inside-article wlasne biale tlo,
   ktore zasiania body.home i lamie sekcje z background:transparent
   (.products .why .reviews mialy wtedy biale tlo zamiast E8ECEF ze wzorca) */
body.home .inside-article{ background:transparent; }

/* ============================================================
   PRINEX Claude Code — wzmocnienie (hardening) krytycznych
   elementów wizualnych literalnymi wartościami hex + !important.
   Cel: zagwarantować identyczny render niezależnie od ewentualnej
   kolizji kaskady CSS, której nie udało się wykryć przy audycie
   serwerowym (treść i CSS strony są bajt-identyczne ze wzorcem).
   ============================================================ */

/* CTA — hero + "Szybkie zamówienie" + FAQ (wszystkie .btn-cta) */
.prinex-home-section .btn-cta{ background-color:#78B833 !important; color:#ffffff !important; }
.prinex-home-section .btn-cta:hover{ background-color:#62992a !important; }
.prinex-home-section .btn-cta-cube{ background-color:rgba(255,255,255,.22) !important; }
.prinex-home-section .btn-cta:hover .btn-cta-cube{ background-color:rgba(255,255,255,.30) !important; }
.prinex-home-section .btn-cta-cube svg{ stroke:#ffffff !important; fill:none !important; }

/* Kreska sygnaturowa nad nagłówkami sekcji */
.prinex-home-section .sig{ background-color:#78B833 !important; }

/* Badge kafli produktów */
.prinex-home-section .tile-badge.new{ background-color:#e23b3b !important; color:#ffffff !important; }
.prinex-home-section .tile-badge.popular{ background-color:#f08a24 !important; color:#ffffff !important; }

/* Tła sekcji — zgodnie ze wzorcem sekcja po sekcji */
.prinex-home-section .hero{ background-color:#E8ECEF !important; }
.prinex-home-section .badgebar{ background-color:#FFFFFF !important; }
.prinex-home-section .ordersec{ background-color:#FFFFFF !important; }
.prinex-home-section .faq{ background-color:#FFFFFF !important; }
.prinex-home-section .trust{ background-color:#FFFFFF !important; }

/* Siatka kafli — odstępy zgodne ze wzorcem */
.prinex-home-section .tile-grid{ display:grid !important; grid-template-columns:repeat(4,1fr) !important; gap:30px 28px !important; }

/* Nagłówki granat, podtytuły sekcji szare — kolory tekstu */
.prinex-home-section h1.h1, .prinex-home-section h2.h2, .prinex-home-section h3.h3{ color:#0B457D !important; }

/* ============================================================
   PRINEX Claude Code — Responsywność strony głównej
   Projekt (Claude Design) był robiony pod desktop (1680/1440/1400px).
   Te media queries dokładają zachowanie mobilne (~768px, ~480px)
   bez zmiany wygladu desktopowego. Część reguł ma !important, bo
   markup z makiety ma sztywne wartości inline (np. width:800px,
   margin:8px 390px, height:250px), które inaczej wygrywają z klasami.
   ============================================================ */
@media (max-width: 768px){
  .prinex-home-section .band{ width:100%; }
  .prinex-home-section .container{ width:100%; padding-left:20px !important; padding-right:20px !important; }
  /* grid/flex items domyślnie mają min-width:auto i odmawiają zwężenia
     poniżej szerokości własnej treści — to wywoływało poziomy scroll */
  .prinex-home-section .container > *{ min-width:0; }
  .prinex-home-section .acc-q{ min-width:0; }

  /* HERO: kolumny -> stos */
  .prinex-home-section .hero .container{ grid-template-columns:1fr !important; gap:32px; }
  .prinex-home-section .hero-art .ph{ width:100% !important; height:280px; }
  .prinex-home-section .h1{ font-size:34px; }
  .prinex-home-section .hero-cta{ flex-wrap:wrap; gap:18px; }

  /* PASEK ODZNAK: 4 -> 2 */
  .prinex-home-section .badgebar .container{ grid-template-columns:repeat(2,1fr); }
  .prinex-home-section .badge-item{ border-width:0 !important; }

  /* SIATKA KAFLI: 4 -> 2 */
  .prinex-home-section .tile-grid{ grid-template-columns:repeat(2,1fr) !important; gap:24px 20px !important; }
  .prinex-home-section .product-img,
  .prinex-home-section .product-img .ph{ height:auto !important; }
  .prinex-home-section .sec-head.center .sec-sub{ margin:8px auto 0 !important; max-width:90%; }

  /* PROCES ZAMÓWIENIA: 2 kolumny -> stos */
  .prinex-home-section .ordersec .container{ grid-template-columns:1fr; gap:36px; }

  /* DLACZEGO NASZE NAKLEJKI: 2 kolumny -> stos */
  .prinex-home-section .why .container{ grid-template-columns:1fr; gap:32px; }
  .prinex-home-section .why-art .ph{ height:280px; }

  /* FAQ: 42/58 -> stos */
  .prinex-home-section .faq .container{ grid-template-columns:1fr; gap:32px; }
  .prinex-home-section .faq-photo{ height:200px !important; }

  /* OPINIE: węższe karty karuzeli */
  .prinex-home-section .reviews-track .review-card{ flex:0 0 280px; width:280px; }

  /* TRUST BADGES: 4 -> 2 */
  .prinex-home-section .trust .container{ grid-template-columns:repeat(2,1fr); }
}

@media (max-width: 480px){
  .prinex-home-section .container{ padding-left:16px !important; padding-right:16px !important; }
  .prinex-home-section .h1{ font-size:28px; }
  .prinex-home-section .h2{ font-size:28px; }

  .prinex-home-section .hero-cta{ flex-direction:column; align-items:flex-start; }

  /* btn-cta-label ma white-space:nowrap w bazowym CSS — przy dlugim
     tekscie (np. "Zobacz wszystkie pytania") wywoluje poziomy scroll */
  .prinex-home-section .btn-cta-label{ white-space:normal; text-align:left; }

  /* PASEK ODZNAK: 2 -> 1 */
  .prinex-home-section .badgebar .container{ grid-template-columns:1fr; }

  /* SIATKA KAFLI: 2 -> 1 */
  .prinex-home-section .tile-grid{ grid-template-columns:1fr !important; }

  /* DLACZEGO — cechy: 2 -> 1 */
  .prinex-home-section .why-features{ grid-template-columns:1fr; }

  /* FAQ — mniejsze odstępy akordeonu */
  .prinex-home-section .acc-q{ padding-left:12px; padding-right:12px; font-size:16px; }
  .prinex-home-section .acc-a{ padding:0 12px 20px 12px; }
  .prinex-home-section .faq-photo{ height:160px !important; }

  /* TRUST BADGES: 2 -> 1 */
  .prinex-home-section .trust .container{ grid-template-columns:1fr; }
}

	<?php
	echo '</style>';
} );

add_action( 'wp_footer', function() {
	if ( ! is_front_page() ) {
		return;
	}
	echo '<script>';
	?>
  document.querySelectorAll('.acc-q').forEach(function(btn){
    btn.addEventListener('click', function(){
      var item = btn.closest('.acc-item');
      var isOpen = item.classList.contains('open');
      item.closest('.faq-acc').querySelectorAll('.acc-item').forEach(function(i){ i.classList.remove('open'); });
      if(!isOpen){ item.classList.add('open'); }
    });
  });

  // sticky header shadow
  var header = document.querySelector('.header');
  var sentinel = header ? header.offsetTop : 0;
  window.addEventListener('scroll', function(){
    if(header){ header.classList.toggle('stuck', window.scrollY > sentinel + 4); }
  }, { passive:true });

  // reviews carousel — auto slide with edge fade + pause loop
  (function(){
    var track = document.querySelector('.reviews-track');
    if(!track) return;
    var VISIBLE = 4, GAP = 28;
    var originals = Array.prototype.slice.call(track.children);
    var total = originals.length;
    // clone first VISIBLE cards for a seamless loop
    for(var i=0; i<VISIBLE; i++){ track.appendChild(originals[i].cloneNode(true)); }
    var idx = 0;
    function stepWidth(){ return track.children[0].offsetWidth + GAP; }
    function advance(){
      idx++;
      track.style.transition = 'transform .9s cubic-bezier(.45,0,.2,1)';
      track.style.transform = 'translateX(' + (-idx * stepWidth()) + 'px)';
    }
    track.addEventListener('transitionend', function(){
      if(idx >= total){
        track.style.transition = 'none';
        idx = 0;
        track.style.transform = 'translateX(0)';
        void track.offsetWidth; // force reflow
      }
    });
    setInterval(advance, 3200); // ~2.3s pause + .9s slide
  })();

	<?php
	echo '</script>';
} );

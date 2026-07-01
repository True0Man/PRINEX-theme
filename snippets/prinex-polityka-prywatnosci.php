<?php
/**
 * PRINEX — Polityka prywatności: CSS/JS treści prawnej (#33).
 * Scope: front-end. Warunkowane is_page( strona polityki ) — nie wycieka.
 *
 * Treść strony (wp_posts.post_content) to SAMA treść (bez własnej ramy — header/footer/nav
 * pliku odrzucone). Ta warstwa dodaje minimalny CSS czytelności (layout spis-treści|treść,
 * sekcje, notki) w tokenach PRINEX + JS spisu treści. Renderuje się w globalnej ramie PRINEX.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'prinex_is_privacy_page' ) ) {
	function prinex_is_privacy_page() {
		return is_page( (int) get_option( 'wp_page_for_privacy_policy' ) ) || is_page( 'polityka-prywatnosci' );
	}
}

add_action( 'wp_head', function () {
	if ( ! prinex_is_privacy_page() ) {
		return;
	}
	?>
<style>
/* tło + ukrycie dubla tytułu strony (treść ma własny h1) */
/* --pp-offset = JEDNA wartość offsetu sticky nagłówka: używana przez scroll-margin-top klika,
   sticky spisu ORAZ scroll-spy (JS czyta ją z CSS) — klik i podświetlenie zawsze zgodne. */
body.pp-scope{background:#E8ECEF;--pp-offset:120px;}
.pp-scope .entry-header{display:none !important;}
.pp-scope .inside-article{background:transparent !important;padding-top:0 !important;padding-left:0 !important;padding-right:0 !important;}
/* wyzeruj poziomy inset treści, by blok siadł na lewej krawędzi kontenera serwisu (= krawędź nagłówka) */
.pp-scope .entry-content{padding-left:0;padding-right:0;margin-left:0;margin-right:0;}
/* neutralizacja własnego .container z pliku (design 1400) — wypełnia PEŁNĄ szerokość kontenera
   serwisu (1400), BEZ centrowania. Dzięki temu .pp-head (nagłówek) centruje się na osi serwisu
   (= oś logo PRINEX), a nie na kolumnie treści. Blok spis|treść zawężony osobno (na .pp-layout).
   :not(.grid-container) chroni kontener serwisu GP przed kolizją klasy „container". */
.pp-scope .container:not(.grid-container){width:auto;max-width:none;margin:0;padding:8px 0 60px;}

/* breadcrumb (element treści, link → strona główna) */
.pp-scope .breadcrumb{display:flex;align-items:center;gap:9px;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#62992A;margin-bottom:20px;}
.pp-scope .breadcrumb a{color:#62992A;text-decoration:none;transition:color .15s;}
.pp-scope .breadcrumb a:hover{color:#78B833;}
.pp-scope .breadcrumb .sep{color:#c2cad3;}
.pp-scope .breadcrumb .cur{color:#8a939c;}

/* nagłówek treści */
.pp-scope .sig{width:52px;height:2px;background:#78B833;margin-bottom:22px;}
.pp-scope .pp-head{margin-bottom:44px;text-align:center;}
.pp-scope .pp-head .sig{margin:0 auto 20px;}
.pp-scope .pp-head h1{font-size:44px;font-weight:700;color:#0B457D;line-height:1.06;margin:0;}
.pp-scope .pp-head .pp-meta{display:flex;align-items:center;justify-content:center;gap:11px;font-size:16px;color:#5a6570;margin-top:16px;}
.pp-scope .pp-head .pp-meta svg{width:19px;height:19px;stroke:#78B833;fill:none;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round;flex:0 0 auto;}
.pp-scope .pp-head .pp-meta b{color:#0B457D;font-weight:700;}

/* layout: spis (lewa krawędź) | treść (wyśrodkowana na osi strony) | pusta prawa.
   Kolumny boczne 1fr (równe) + równe gapy → treść ma optyczny środek kontenera,
   niezależnie od spisu (świadome odejście od mockupu). */
/* blok jak mockup (288 | treść, gap 56) DOSUNIĘTY DO LEWEJ; prawa strona pusta (OK).
   max-width:1064 (288+56+720) + margin:0 → zawężenie TYLKO tego bloku, nie nagłówka. */
.pp-scope .pp-layout{display:grid;grid-template-columns:288px minmax(0,720px);gap:56px;align-items:start;justify-content:start;max-width:1064px;margin:0;}
/* sticky NA grid-itemie (aside), nie na wewnętrznym .toc — inaczej aside=start jest krótki
   i spis odjeżdża. align-self:start = własna wysokość, sticky w obrębie wysokiego pp-content. */
.pp-scope .pp-layout>aside{position:sticky;top:var(--pp-offset);align-self:start;}
.pp-scope .toc-head{display:flex;align-items:center;justify-content:space-between;width:100%;background:none;border:none;font-family:inherit;text-align:left;cursor:default;padding:0 0 16px;}
.pp-scope .toc-head .toc-title{font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#8a939c;}
.pp-scope .toc-head .toc-chev{display:none;}
.pp-scope .toc-list{list-style:none;margin:0;padding:0;border-left:1px solid #d7dde2;}
.pp-scope .toc-list li{position:relative;margin:0;}
/* flex: numer = osobna kolumna (stała), tytuł zawija się we własnej → druga linia
   wyrównana do tekstu tytułu, nie do numeru (hanging indent). */
.pp-scope .toc-list a{display:flex;align-items:baseline;padding:9px 12px 9px 20px;margin-left:-1px;border-radius:0 8px 8px 0;font-size:14.5px;font-weight:400;line-height:1.4;color:#7a848d;border-left:2px solid transparent;text-decoration:none;transition:color .16s,border-color .16s,background .16s,transform .16s;}
/* numer = kolumna o STAŁEJ szerokości (tabular-nums = równe cyfry), wyśrodkowany;
   dzięki temu tekst tytułu zaczyna się w tym samym miejscu dla 1- i 2-cyfrowych (p9 = p10). */
.pp-scope .toc-list a .n{flex:0 0 22px;text-align:center;font-variant-numeric:tabular-nums;color:#b3bbc3;margin-right:8px;font-weight:600;}
.pp-scope .toc-list a:hover{color:#62992A;background:rgba(120,184,51,.1);border-left-color:#78B833;transform:translateX(2px);}
.pp-scope .toc-list a:hover .n{color:#62992A;}
.pp-scope .toc-list a.active{color:#0B457D;font-weight:700;border-left-color:#78B833;}
.pp-scope .toc-list a.active .n{color:#78B833;}

/* treść */
.pp-scope .pp-content{max-width:720px;}
.pp-scope .pp-intro{background:#fff;border:1px solid #e1e6ea;border-radius:16px;box-shadow:0 2px 16px rgba(11,69,125,.06);padding:28px 32px;margin-bottom:40px;display:flex;align-items:flex-start;gap:16px;}
.pp-scope .pp-intro .pi-ic{flex:0 0 auto;width:44px;height:44px;border-radius:50%;background:rgba(120,184,51,.14);display:flex;align-items:center;justify-content:center;}
.pp-scope .pp-intro .pi-ic svg{width:23px;height:23px;stroke:#62992A;fill:none;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round;}
.pp-scope .pp-intro p{font-size:16px;color:#5a6570;line-height:1.65;margin:0;}
.pp-scope .pp-intro p b{color:#0B457D;font-weight:700;}
.pp-scope .pp-section{scroll-margin-top:var(--pp-offset);padding-bottom:44px;margin-bottom:44px;border-bottom:1px solid #e1e6ea;}
.pp-scope .pp-section:last-child{border-bottom:none;margin-bottom:0;}
.pp-scope .sec-head{display:flex;align-items:center;gap:15px;margin-bottom:18px;}
.pp-scope .pp-section .sec-num{flex:0 0 auto;display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:50%;background:#78B833;color:#fff;font-size:17px;font-weight:700;font-variant-numeric:tabular-nums;}
.pp-scope .pp-section h2{font-size:25px;font-weight:700;color:#0B457D;line-height:1.2;margin:0;}
.pp-scope .pp-section p{color:#333;font-size:16.5px;line-height:1.72;margin:0 0 15px;}
.pp-scope .pp-section p:last-child{margin-bottom:0;}
.pp-scope .pp-section p b,.pp-scope .pp-section li b{color:#0B457D;font-weight:700;}
.pp-scope .pp-section ul{list-style:none;margin:6px 0 15px;padding:0;display:flex;flex-direction:column;gap:11px;}
.pp-scope .pp-section ul li{position:relative;padding-left:26px;font-size:16.5px;line-height:1.65;color:#333;margin:0;}
.pp-scope .pp-section ul li::before{content:"";position:absolute;left:2px;top:11px;width:8px;height:2px;background:#78B833;border-radius:2px;}
.pp-scope .pp-section a.inl,.pp-scope .pp-section a{color:#62992A;font-weight:600;border-bottom:1px solid rgba(98,153,42,.35);text-decoration:none;transition:color .15s,border-color .15s;}
.pp-scope .pp-section a:hover{color:#78B833;border-color:#78B833;}
.pp-scope .pp-note{margin:6px 0 4px;background:#fbfdf7;border:1px solid #d8e8c2;border-left:3px solid #78B833;border-radius:10px;padding:18px 22px;}
.pp-scope .pp-note p{margin:0;font-size:16px;color:#41613a;line-height:1.65;}
.pp-scope .pp-note p b{color:#62992A;}
.pp-scope .pp-callout{margin-top:14px;background:#fff;border:1px solid #e1e6ea;border-radius:12px;box-shadow:inset 3px 0 0 #F39200;padding:20px 24px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;}
.pp-scope .pp-callout .pc-txt{font-size:16px;color:#5a6570;}
.pp-scope .pp-callout .pc-txt b{color:#0B457D;}
.pp-scope .pp-callout a{font-weight:700;color:#0B457D;border-bottom:2px solid #78B833;text-decoration:none;}

/* przycisk „DO GÓRY" — pływający, tylko polityka (#33) */
.pp-scope .pp-totop{position:fixed;right:26px;bottom:26px;z-index:60;width:48px;height:48px;border-radius:50%;background:#78B833;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 20px rgba(11,69,125,.18);opacity:0;visibility:hidden;transform:translateY(10px);transition:opacity .25s,visibility .25s,transform .25s,background .16s;}
.pp-scope .pp-totop.show{opacity:1;visibility:visible;transform:translateY(0);}
.pp-scope .pp-totop:hover{background:#62992A;}
.pp-scope .pp-totop:focus-visible{outline:3px solid rgba(11,69,125,.55);outline-offset:2px;}
.pp-scope .pp-totop svg{width:22px;height:22px;stroke:#fff;fill:none;stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round;}

@media(max-width:960px){
  /* mobile/tablet: przywróć boczny gutter (desktop flush-left zerował padding) */
  .pp-scope .entry-content,.pp-scope .inside-article{padding-left:20px !important;padding-right:20px !important;}
  .pp-scope .pp-layout{grid-template-columns:1fr;gap:0;}
  .pp-scope .pp-layout>aside{position:static;}
  .pp-scope .toc{position:static;margin-bottom:28px;background:#fff;border:1px solid #e1e6ea;border-radius:12px;box-shadow:0 4px 18px rgba(11,69,125,.08);padding:4px 18px;}
  .pp-scope .toc-head{padding:16px 0;cursor:pointer;}
  .pp-scope .toc-head .toc-chev{display:block;width:20px;height:20px;stroke:#0B457D;fill:none;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round;transition:transform .25s;}
  .pp-scope .toc.open .toc-head .toc-chev{transform:rotate(180deg);}
  .pp-scope .toc-list{display:none;padding-bottom:14px;max-height:60vh;overflow:auto;border-left:none;}
  .pp-scope .toc.open .toc-list{display:block;}
  .pp-scope .pp-content{max-width:none;}
  .pp-scope .pp-head h1{font-size:34px;}
}
@media(max-width:640px){
  .pp-scope .pp-head h1{font-size:28px;}
  .pp-scope .pp-section h2{font-size:20px;}
  .pp-scope .pp-totop{right:16px;bottom:16px;width:44px;height:44px;}
}
</style>
	<?php
} );

/* dodaj klasę scope do body (pewny selektor niezależny od page-id) */
add_filter( 'body_class', function ( $classes ) {
	if ( prinex_is_privacy_page() ) {
		$classes[] = 'pp-scope';
	}
	return $classes;
} );

/* Wyłącz wpautop dla strony polityki — treść ma własne <p>, a wpautop wstrzykuje
 * puste <p> jako dzieci .pp-layout, psując grid (spis treści | treść). */
add_action( 'wp', function () {
	if ( prinex_is_privacy_page() ) {
		remove_filter( 'the_content', 'wpautop' );
		remove_filter( 'the_content', 'shortcode_unautop' );
	}
} );

/* Breadcrumb — element TREŚCI (nie ramy), lokalny w #33; link „PRINEX" → strona główna.
 * Wstrzyknięty wewnątrz .container, nad pp-head (jak w mockupie). */
add_filter( 'the_content', function ( $content ) {
	if ( ! prinex_is_privacy_page() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$bc = '<nav class="breadcrumb" aria-label="Ścieżka nawigacji">'
		. '<a href="' . esc_url( home_url( '/' ) ) . '">PRINEX</a>'
		. '<span class="sep">/</span>'
		. '<span class="cur">Polityka prywatności</span>'
		. '</nav>';
	if ( false !== strpos( $content, '<div class="container">' ) ) {
		return preg_replace( '/(<div class="container">)/', '$1' . $bc, $content, 1 );
	}
	return $bc . $content;
}, 20 );

/* JS — spis treści: collapse (mobile) + podświetlanie aktywnej sekcji */
add_action( 'wp_footer', function () {
	if ( ! prinex_is_privacy_page() ) {
		return;
	}
	?>
<button type="button" class="pp-totop" aria-label="Do góry" title="Do góry">
  <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
</button>
<script>
(function(){
  function ready(fn){ if(document.readyState!=='loading'){fn();} else {document.addEventListener('DOMContentLoaded',fn);} }
  function smoothTo(el){ if(el){ el.scrollIntoView({behavior:'smooth',block:'start'}); } } /* respektuje scroll-margin-top:120px sekcji */
  ready(function(){
    var toc=document.querySelector('.pp-scope .toc');
    var links=toc?[].slice.call(toc.querySelectorAll('.toc-list a')):[];
    if(toc){
      var head=toc.querySelector('.toc-head');
      if(head){ head.addEventListener('click',function(){ if(window.matchMedia('(max-width:960px)').matches){ toc.classList.toggle('open'); } }); }
    }
    var sections=[].slice.call(document.querySelectorAll('.pp-scope .pp-section'));
    var scopeEl=document.querySelector('.pp-scope')||document.body;
    var SPY_BUFFER=30;
    // --pp-offset DYNAMICZNY = BASE (nagłówek ramy + gap, zatwierdzone dla gościa: 120)
    //   + realna wysokość paska admina WP GDY przykrywa górę (position:fixed; na mobile bywa
    //   absolute i znika przy scrollu → NIE doliczamy). Zasila scroll-margin-top sekcji,
    //   sticky top spisu ORAZ próg spy — jedna wartość, bez rozjazdu z paskiem admina.
    var BASE=120;
    function fixedAdminBarH(){
      var bar=document.getElementById('wpadminbar');
      if(bar && getComputedStyle(bar).position==='fixed'){ return bar.offsetHeight||0; }
      return 0;
    }
    function applyOffset(){ scopeEl.style.setProperty('--pp-offset',(BASE+fixedAdminBarH())+'px'); }
    applyOffset();
    // admin bar: przelicz na load + kilka opóźnień (późny render) + resize — BEZ MutationObserver
    // (obserwator na body odpalał się przy każdej mutacji żywej strony → szum/wyścigi).
    [0,400,1200].forEach(function(d){ setTimeout(applyOffset,d); });
    function currentOffset(){ return parseInt(getComputedStyle(scopeEl).getPropertyValue('--pp-offset'),10)||BASE; }

    if(toc){ try{ toc.setAttribute('data-ppspy','v8'); }catch(e){} } // znacznik wersji (weryfikacja cache)

    // pinned: po kliku pozycji spisu podświetlenie jest PRZYKLEJONE do klikanej sekcji, a spy
    // NIE przelicza (ani w trakcie animacji, ani na zatrzymaniu) — dopóki użytkownik SAM nie
    // przewinie (wheel/touch/klawisz). Eliminuje „przeskok o jeden wyżej na zatrzymaniu",
    // niezależnie od geometrii ramy: programowy scroll klika niczego nie nadpisuje.
    var pinned=false;
    function highlight(id){ links.forEach(function(a){ a.classList.toggle('active', a.getAttribute('href')==='#'+id); }); }
    function setActive(){
      if(pinned) return;                        // po kliku: nie przeliczaj, trzymaj klikaną
      if(!sections.length||!links.length) return;
      var line=currentOffset()+SPY_BUFFER;      // próg = ta sama dynamiczna wartość co lądowanie + bufor
      var active=sections[0];
      for(var i=0;i<sections.length;i++){
        if(sections[i].getBoundingClientRect().top<=line){ active=sections[i]; }
        else { break; } // sekcje w kolejności — pierwsza poniżej linii kończy pętlę
      }
      if((window.innerHeight+window.pageYOffset)>=(document.documentElement.scrollHeight-2)){
        active=sections[sections.length-1]; // koniec strony → ostatnia sekcja
      }
      highlight(active.id);
    }
    function unpin(){ if(pinned){ pinned=false; setActive(); } } // realny scroll usera → wznów spy

    if(sections.length&&links.length){
      var raf=null;
      var onScroll=function(){ if(raf) return; raf=requestAnimationFrame(function(){ raf=null; setActive(); }); };
      var settleT=null;
      var onSettle=function(){ if(settleT) clearTimeout(settleT); settleT=setTimeout(setActive,120); };
      window.addEventListener('scroll', function(){ onScroll(); onSettle(); }, {passive:true});
      if('onscrollend' in window){ window.addEventListener('scrollend', setActive, {passive:true}); }
      window.addEventListener('resize', function(){ applyOffset(); setActive(); }, {passive:true});
      // WEJŚCIE UŻYTKOWNIKA w scroll (nie programowy klik) → odklej podświetlenie, wznów spy
      window.addEventListener('wheel', unpin, {passive:true});
      window.addEventListener('touchmove', unpin, {passive:true});
      window.addEventListener('keydown', function(e){
        if(['ArrowUp','ArrowDown','PageUp','PageDown','Home','End',' ','Spacebar'].indexOf(e.key)>=0) unpin();
      }, {passive:true});
      setActive();
    }
    // klik spisu: PŁYNNE przewijanie + podświetl klikaną NA STAŁE (aż do własnego scrolla usera)
    links.forEach(function(a){
      a.addEventListener('click',function(e){
        var href=a.getAttribute('href')||'';
        if(href.charAt(0)==='#'){
          var t=document.getElementById(href.slice(1));
          if(t){
            e.preventDefault();
            pinned=true;                       // przyklej podświetlenie do klikanej (spy zamrożony)
            highlight(href.slice(1));          // klikana aktywna od razu i trwale
            smoothTo(t);
            if(history.pushState){ history.pushState(null,'',href); }
          }
        }
        if(window.matchMedia('(max-width:960px)').matches&&toc){ toc.classList.remove('open'); }
      });
    });
    // przycisk „DO GÓRY": pojawia się po przewinięciu > 400px, płynny scroll do góry
    var totop=document.querySelector('.pp-scope .pp-totop');
    if(totop){
      totop.addEventListener('click',function(){ window.scrollTo({top:0,behavior:'smooth'}); });
      var toggleTop=function(){ totop.classList.toggle('show', (window.pageYOffset||document.documentElement.scrollTop)>400); };
      window.addEventListener('scroll', toggleTop, {passive:true}); toggleTop();
    }
  });
})();
</script>
	<?php
} );

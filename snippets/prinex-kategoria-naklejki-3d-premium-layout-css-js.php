<?php
/**
 * PRINEX — eksport Code Snippet (mirror do wersjonowania, NIE zrodlo prawdy)
 *
 * ID snippetu (wp_snippets.id): 12
 * Tytul:                       PRINEX — Kategoria Naklejki 3D Premium (layout + CSS/JS)
 * Typ:                         PHP (snippet typu code-snippets; jesli echo CSS/JS, zaznaczone nizej)
 * Scope:                       front-end — wykonuje sie WYLACZNIE na froncie (nie w wp-admin)
 * Status:                      AKTYWNY
 *
 * UWAGA: zrodlem prawdy jest baza WP (wtyczka Code Snippets). Ten plik to
 * mirror do wersjonowania/code review. Edycja tego pliku NIE zmienia
 * dzialania strony — trzeba wkleic zmiany z powrotem do wp-admin > Code Snippets,
 * lub zaktualizowac wp_snippets.code (np. przez wp-cli/wp eval).
 */

/**
 * PRINEX — Kategoria "Naklejki 3D Premium" (layout + CSS/JS)
 * Dotyczy wylacznie archiwum kategorii product_cat "naklejki-3d-premium".
 * Zrodlo wygladu: 04-mockupy/03-kategoria-sklep/kategoria-sklep-unpacked.html
 */

// Pelna szerokosc strony (jak na stronie glownej) — tu przez filtry,
// bo archiwum taksonomii nie ma postmeta jak zwykla strona.
add_filter( 'generate_sidebar_layout', function( $layout ) {
	if ( is_product_category( 'naklejki-3d-premium' ) ) {
		return 'no-sidebar';
	}
	return $layout;
} );

add_filter( 'body_class', function( $classes ) {
	if ( is_product_category( 'naklejki-3d-premium' ) ) {
		$classes[] = 'full-width-content';
		$classes[] = 'no-sidebar';
		$classes[] = 'prinex-cat-naklejki';
	}
	return $classes;
}, 20 );

add_action( 'wp_head', function() {
	if ( ! is_product_category( 'naklejki-3d-premium' ) ) {
		return;
	}
	echo '<style id="prinex-cat-css">';
	?>
/* navy/green/bg-light/body/white/line, body{font-family}, .sig, h1-h3 —
   teraz globalne (generatepress-child/style.css). Tu zostaje tylko to,
   co jest specyficzne dla tej kategorii. */

/* WAZNE: scope na .inside-article (obszar treści TEJ strony), NIE na body —
   ".prinex-cat-naklejki a" (klasa na body) tez trafialo w globalny header/nav
   (zyje w tym samym body), bijac ".prinex-nav a" specyficznoscia i gasząc
   navy na menu (szare/czarne dziedziczone). .inside-article strukturalnie
   nie obejmuje globalnego headera/topbara/footera (hooki poza nim). */
.prinex-cat-naklejki .inside-article a{ color:inherit; text-decoration:none; }

body.prinex-cat-naklejki{ background:var(--bg-light); }
body.prinex-cat-naklejki .inside-article{ background:transparent; }

.prinex-cat-naklejki .band{ width:1440px; max-width:100%; margin:0 auto; }
/* WAZNE: ".prinex-cat-naklejki .container" (samo, bez scope) trafialo rowniez
   w GeneratePress #page (ma klase "container" w swoim grid-container/container/hfeed) —
   przez co caly #page dostawal width:1400px+padding:20px zamiast pelnej szerokosci,
   a nasze sekcje (.seo/.howto/.trust) nie byly od krawedzi do krawedzi.
   Scope tylko do realnych miejsc uzycia: .cat-wrap (gorna czesc) i .band (sekcje SEO).
   Bez padding na desktopie: .seo/.howto/.trust .container maja WLASNE,
   bardziej specyficzne reguly padding (np. ".seo .container{padding:80px 0 90px}"),
   ktore i tak nadpisywaly te "padding:0 20px" — jedynym realnym konsumentem
   byl ".cat-wrap .container" (brak wlasnego override), co wciskalo breadcrumb/
   naglowek/filtry/siatke kafli o 20px do wewnatrz wzgledem strony glownej.
   Padding mobilny zostaje — dodawany osobno w @media nizej (!important na
   padding-left/right, nie psuje pionowych marginesy sekcji SEO). */
.prinex-cat-naklejki .cat-wrap .container,
.prinex-cat-naklejki .band .container{ width:1400px; max-width:100%; margin:0 auto; }

.prinex-cat-naklejki .ic{ stroke:currentColor; stroke-width:1.75; fill:none; stroke-linecap:round; stroke-linejoin:round; }

.prinex-cat-naklejki .ph{
  background-color:#eef1f3;
  background-image:repeating-linear-gradient(45deg, rgba(11,69,125,.045) 0 11px, rgba(11,69,125,.085) 11px 22px);
  display:flex; align-items:center; justify-content:center;
  color:rgba(11,69,125,.55);
  font-family:'SFMono-Regular',Menlo,Consolas,monospace; font-size:12px;
  letter-spacing:.04em; text-transform:uppercase; text-align:center;
  border-radius:6px;
}

/* CTA button with expanding chevron cube */
.prinex-cat-naklejki .btn-cta{
  position:relative; display:inline-flex; align-items:center; justify-content:flex-start;
  background:var(--green); color:#fff; border-radius:50px;
  font-family:'Figtree',sans-serif; font-weight:700; font-size:19px; text-transform:uppercase; letter-spacing:.03em;
  padding:18px 70px 18px 28px; cursor:pointer; overflow:hidden; user-select:none; border:none;
  transition:transform .5s ease, background .5s ease, box-shadow .5s ease;
}
.prinex-cat-naklejki .btn-cta-label{ transition:opacity .35s ease; white-space:nowrap; position:relative; z-index:1; }
.prinex-cat-naklejki .btn-cta:hover .btn-cta-label{ opacity:0; }
.prinex-cat-naklejki .btn-cta-cube{
  position:absolute; top:4px; right:4px; bottom:4px; width:50px;
  background:rgba(255,255,255,.22); border-radius:50px;
  display:flex; align-items:center; justify-content:center;
  transition:width .5s ease, background .5s ease;
}
.prinex-cat-naklejki .btn-cta:hover .btn-cta-cube{ width:calc(100% - 8px); background:rgba(255,255,255,.30); }
.prinex-cat-naklejki .btn-cta-cube svg{ width:24px; height:24px; stroke:#fff; stroke-width:2.4; fill:none; stroke-linecap:round; stroke-linejoin:round; }
.prinex-cat-naklejki .btn-cta:hover{ box-shadow:0 10px 24px rgba(120,184,51,.34); }
.prinex-cat-naklejki .btn-cta:active{ transform:scale(.98); }

/* ===================================================== CATEGORY HEAD */
.prinex-cat-naklejki .cat-wrap{ padding:30px 0 64px; }
.prinex-cat-naklejki .breadcrumb{ display:flex; align-items:center; gap:9px; font-size:13px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; color:var(--green-dark); margin-bottom:26px; white-space:nowrap; }
.prinex-cat-naklejki .breadcrumb a{ color:var(--green-dark); transition:color .15s; }
.prinex-cat-naklejki .breadcrumb a:hover{ color:var(--green); }
.prinex-cat-naklejki .breadcrumb .sep{ color:#c4ccd3; }
.prinex-cat-naklejki .breadcrumb .cur{ color:var(--navy); }

.prinex-cat-naklejki .cat-head{ margin-bottom:30px; }
.prinex-cat-naklejki .cat-head .sig{ margin-bottom:18px; }
.prinex-cat-naklejki .cat-head h1{ font-size:46px; margin-bottom:16px; }
.prinex-cat-naklejki .cat-head p{ font-size:18px; color:#5a6570; line-height:1.55; max-width:800px; }

.prinex-cat-naklejki .cat-toolbar{ display:flex; align-items:center; justify-content:flex-start; gap:20px; padding:18px 0; border-top:1px solid var(--line); border-bottom:1px solid var(--line); margin-bottom:38px; }
.prinex-cat-naklejki .cat-filters{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.prinex-cat-naklejki .fchk{ display:inline-flex; align-items:center; gap:9px; cursor:pointer; padding:9px 16px; border:1.5px solid var(--line); border-radius:50px; background:#fff; font-size:13px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:var(--navy); transition:border-color .15s, background .15s, color .15s; user-select:none; white-space:nowrap; }
.prinex-cat-naklejki .fchk:hover{ border-color:var(--green); color:var(--green-dark); }
.prinex-cat-naklejki .fchk .box{ width:18px; height:18px; flex:none; border-radius:5px; border:2px solid #c4ccd3; display:flex; align-items:center; justify-content:center; transition:border-color .15s, background .15s; }
.prinex-cat-naklejki .fchk .box svg{ width:11px; height:11px; stroke:#fff; fill:none; stroke-width:3.2; stroke-linecap:round; stroke-linejoin:round; opacity:0; transition:opacity .15s; }
.prinex-cat-naklejki .fchk.on{ border-color:var(--green); background:#f4f9ec; color:var(--green-dark); }
.prinex-cat-naklejki .fchk.on .box{ border-color:var(--green); background:var(--green); }
.prinex-cat-naklejki .fchk.on .box svg{ opacity:1; }

/* ===================================================== PRODUCT TILES (natywna petla WC) */
.prinex-cat-naklejki .tile-grid{ display:grid; grid-template-columns:repeat(4,1fr); gap:36px 28px; }
.prinex-cat-naklejki .product{ cursor:pointer; display:flex; flex-direction:column; transition:opacity .3s ease, transform .3s ease; }
.prinex-cat-naklejki .product-img{ position:relative; width:100%; aspect-ratio:1/1; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(11,69,125,.06); transition:box-shadow .18s ease, transform .18s ease; background:#eef1f3; }
.prinex-cat-naklejki .product-img img{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; transition:transform .18s ease; }
.prinex-cat-naklejki .product:hover .product-img{ box-shadow:0 22px 46px rgba(11,69,125,.24); transform:translateY(-8px); }
.prinex-cat-naklejki .product:hover .product-img img{ transform:scale(1.08); }
.prinex-cat-naklejki .tile-badge{ position:absolute; top:12px; left:12px; z-index:3; font-size:12px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; color:#fff; padding:6px 12px; border-radius:6px 6px 6px 2px; box-shadow:0 4px 10px rgba(0,0,0,.18); }
.prinex-cat-naklejki .tile-badge.new{ background:#e23b3b; }
.prinex-cat-naklejki .tile-badge.popular{ background:var(--green); }
.prinex-cat-naklejki .product-name{ font-size:20px; font-weight:700; color:var(--navy); margin-top:18px; line-height:1.1; }
.prinex-cat-naklejki .product-desc{ font-size:14px; font-weight:400; color:#6b7680; margin-top:6px; line-height:1.45; }
.prinex-cat-naklejki .product.leaving{ opacity:0; transform:scale(.96); }
.prinex-cat-naklejki .product.hidden{ display:none; }

/* ===================================================== SEO SECTION */
.prinex-cat-naklejki .seo{ background:var(--white); border-top:1px solid var(--line); }
.prinex-cat-naklejki .seo .container{ padding:80px 0 90px; }
.prinex-cat-naklejki .seo-h{ margin-bottom:18px; }
.prinex-cat-naklejki .seo-h .sig{ margin-bottom:14px; }
.prinex-cat-naklejki .seo-h h2{ font-size:32px; color:var(--navy); line-height:1.15; }
.prinex-cat-naklejki .seo-h h3{ font-size:26px; color:var(--navy); line-height:1.2; }
.prinex-cat-naklejki .seo-p{ font-size:17px; color:#5a6570; line-height:1.7; max-width:760px; }
.prinex-cat-naklejki .seo-p + .seo-p{ margin-top:14px; }
.prinex-cat-naklejki .seo-list{ list-style:none; margin-top:20px; display:flex; flex-direction:column; gap:12px; max-width:760px; }
.prinex-cat-naklejki .seo-list li{ display:flex; align-items:flex-start; gap:13px; font-size:16px; color:#3d4752; line-height:1.5; }
.prinex-cat-naklejki .seo-list li svg{ flex:none; width:28px; height:28px; margin-top:0; stroke:var(--green); fill:none; stroke-width:2.2; stroke-linecap:round; stroke-linejoin:round; }
.prinex-cat-naklejki .seo-list li b{ color:var(--navy); font-weight:700; }

.prinex-cat-naklejki .seo-row{ display:grid; grid-template-columns:1fr 1fr; gap:60px; align-items:stretch; margin-bottom:80px; }
.prinex-cat-naklejki .seo-row:last-child{ margin-bottom:0; }
.prinex-cat-naklejki .seo-row.rev .seo-text{ order:2; }
.prinex-cat-naklejki .seo-row.rev .seo-art{ order:1; }
.prinex-cat-naklejki .seo-text{ align-self:center; }
.prinex-cat-naklejki .seo-art{ display:flex; }
.prinex-cat-naklejki .seo-art .ph-illus{ width:100%; min-height:320px; border-radius:16px; background-color:#eef1f3; background-image:repeating-linear-gradient(45deg, rgba(11,69,125,.045) 0 11px, rgba(11,69,125,.085) 11px 22px); display:flex; align-items:center; justify-content:center; text-align:center; font-family:'SFMono-Regular',Menlo,Consolas,monospace; font-size:12px; letter-spacing:.04em; text-transform:uppercase; color:rgba(11,69,125,.55); }

.prinex-cat-naklejki .seo-full{ margin-bottom:80px; }

.prinex-cat-naklejki .ind-grid{ display:grid; grid-template-columns:repeat(2,1fr); gap:52px 80px; margin-top:8px; }
.prinex-cat-naklejki .ind-intro{ grid-column:1 / -1; margin-bottom:4px; }
.prinex-cat-naklejki .ind-intro .seo-h{ margin-bottom:14px; }
.prinex-cat-naklejki .ind-cell{ display:flex; flex-direction:row; align-items:flex-start; gap:22px; }
.prinex-cat-naklejki .ind-cell .ind-ic{ width:54px; height:54px; flex:none; margin-top:2px; }
.prinex-cat-naklejki .ind-cell .ind-ic svg{ width:54px; height:54px; stroke:var(--green); fill:none; stroke-width:1.6; stroke-linecap:round; stroke-linejoin:round; }
.prinex-cat-naklejki .ind-cell .ind-txt{ display:flex; flex-direction:column; gap:7px; }
.prinex-cat-naklejki .ind-cell h4{ font-size:19px; font-weight:700; color:var(--navy); line-height:1.25; }
.prinex-cat-naklejki .ind-cell p{ font-size:15px; color:#5a6570; line-height:1.55; }

.prinex-cat-naklejki .acc{ display:flex; flex-direction:column; border-top:1px solid var(--line); }
.prinex-cat-naklejki .acc-item{ border-bottom:1px solid var(--line); }
.prinex-cat-naklejki .acc-q{ width:100%; background:none; border:none; font-family:'Figtree',sans-serif; cursor:pointer; display:flex; align-items:center; justify-content:space-between; gap:16px; padding:20px 4px; text-align:left; font-size:18px; font-weight:700; color:var(--navy); transition:color .15s; }
.prinex-cat-naklejki .acc-q:hover, .prinex-cat-naklejki .acc-q:focus, .prinex-cat-naklejki .acc-q:active{ background:none; outline:none; }
.prinex-cat-naklejki .acc-q:hover{ color:var(--green-dark); }
.prinex-cat-naklejki .acc-q .chev{ flex:none; width:20px; height:20px; stroke:var(--green); fill:none; stroke-width:2.4; stroke-linecap:round; stroke-linejoin:round; transition:transform .3s ease; }
.prinex-cat-naklejki .acc-item.open .acc-q .chev{ transform:rotate(180deg); }
.prinex-cat-naklejki .acc-a{ overflow:hidden; max-height:0; transition:max-height .35s ease; }
.prinex-cat-naklejki .acc-a-inner{ padding:0 4px 22px; font-size:16px; color:#5a6570; line-height:1.6; max-width:680px; }

.prinex-cat-naklejki .seo-steps{ display:grid; grid-template-columns:repeat(4,1fr); gap:24px; margin-top:34px; margin-bottom:40px; }
.prinex-cat-naklejki .seo-step{ display:flex; flex-direction:column; gap:12px; }
.prinex-cat-naklejki .seo-step .snum{ width:38px; height:38px; border-radius:50px; background:var(--navy); color:#fff; font-size:16px; font-weight:700; display:flex; align-items:center; justify-content:center; }
.prinex-cat-naklejki .seo-step h4{ font-size:18px; font-weight:700; color:var(--navy); }
.prinex-cat-naklejki .seo-step p{ font-size:15px; color:#5a6570; line-height:1.55; }

.prinex-cat-naklejki .howto .container{ padding:72px 0 80px; }

.prinex-cat-naklejki .trust{ background:var(--white); border-radius:16px; box-shadow:0 14px 36px rgba(11,69,125,.08); margin:8px 0 76px; }
.prinex-cat-naklejki .trust .container{ padding:42px 0; display:grid; grid-template-columns:repeat(4,1fr); gap:36px; }
.prinex-cat-naklejki .trust-item{ display:flex; align-items:flex-start; gap:16px; }
.prinex-cat-naklejki .trust-item .t-ic{ flex-shrink:0; width:48px; height:48px; border-radius:50px; background:var(--bg-light); display:flex; align-items:center; justify-content:center; }
.prinex-cat-naklejki .trust-item .t-ic svg{ width:26px; height:26px; color:var(--green); }
.prinex-cat-naklejki .trust-item h4{ font-size:17px; font-weight:700; color:var(--navy); margin-bottom:5px; }
.prinex-cat-naklejki .trust-item p{ font-size:15px; color:#5a6570; line-height:1.5; }

/* ===================================================== RESPONSYWNOSC */
@media (max-width: 768px){
  .prinex-cat-naklejki .band{ width:100%; }
  .prinex-cat-naklejki .cat-wrap .container,
  .prinex-cat-naklejki .band .container{ width:100%; padding-left:20px !important; padding-right:20px !important; }
  .prinex-cat-naklejki .cat-wrap .container > *,
  .prinex-cat-naklejki .band .container > *{ min-width:0; }

  .prinex-cat-naklejki .cat-head h1{ font-size:32px; }
  .prinex-cat-naklejki .tile-grid{ grid-template-columns:repeat(2,1fr); gap:24px 20px; }

  .prinex-cat-naklejki .seo-row{ grid-template-columns:1fr; gap:32px; margin-bottom:56px; }
  .prinex-cat-naklejki .seo-row.rev .seo-text{ order:1; }
  .prinex-cat-naklejki .seo-row.rev .seo-art{ order:2; }
  .prinex-cat-naklejki .seo-art .ph-illus{ min-height:220px; }

  .prinex-cat-naklejki .ind-grid{ grid-template-columns:1fr; gap:32px; }

  .prinex-cat-naklejki .seo-steps{ grid-template-columns:repeat(2,1fr); }

  .prinex-cat-naklejki .trust .container{ grid-template-columns:repeat(2,1fr); }
}

@media (max-width: 480px){
  .prinex-cat-naklejki .cat-wrap .container,
  .prinex-cat-naklejki .band .container{ padding-left:16px !important; padding-right:16px !important; }
  .prinex-cat-naklejki .cat-head h1{ font-size:26px; }
  .prinex-cat-naklejki .cat-head p{ font-size:16px; }

  .prinex-cat-naklejki .tile-grid{ grid-template-columns:1fr; }

  .prinex-cat-naklejki .seo-steps{ grid-template-columns:1fr; }

  .prinex-cat-naklejki .trust .container{ grid-template-columns:1fr; }

  .prinex-cat-naklejki .btn-cta-label{ white-space:normal; text-align:left; }
}
	<?php
	echo '</style>';
} );

add_action( 'wp_footer', function() {
	if ( ! is_product_category( 'naklejki-3d-premium' ) ) {
		return;
	}
	echo '<script>';
	?>
  /* ----- filtrowanie produktow (fade out + reflow) ----- */
  (function(){
    var filters = document.getElementById('catFilters');
    if(!filters) return;
    var chips = Array.prototype.slice.call(filters.querySelectorAll('.fchk'));
    var tiles = Array.prototype.slice.call(document.querySelectorAll('.tile-grid .product'));

    function activeFilters(){
      return chips.filter(function(c){ return c.classList.contains('on'); })
                  .map(function(c){ return c.dataset.filter; });
    }

    function apply(){
      var active = activeFilters();
      var showAll = active.length === 0 || active.indexOf('all') !== -1;
      tiles.forEach(function(t){
        var cats = (t.dataset.cat || '').split(' ').filter(Boolean);
        var match = showAll || active.some(function(f){ return cats.indexOf(f) !== -1; });
        if(match){
          if(t.classList.contains('hidden')){
            t.classList.remove('hidden');
            t.classList.add('leaving');
            void t.offsetWidth;
            t.classList.remove('leaving');
          } else {
            t.classList.remove('leaving');
          }
        } else {
          if(!t.classList.contains('hidden') && !t.classList.contains('leaving')){
            t.classList.add('leaving');
            setTimeout(function(){
              if(t.classList.contains('leaving')){ t.classList.add('hidden'); }
            }, 300);
          }
        }
      });
    }

    chips.forEach(function(chip){
      chip.addEventListener('click', function(){
        if(chip.dataset.filter === 'all'){
          chips.forEach(function(c){ c.classList.toggle('on', c === chip); });
        } else {
          chip.classList.toggle('on');
          var all = filters.querySelector('.fchk[data-filter="all"]');
          if(all){ all.classList.remove('on'); }
          if(activeFilters().length === 0 && all){ all.classList.add('on'); }
        }
        apply();
      });
    });

    apply();
  })();

  /* ----- akordeon "Dlaczego zywica PU" ----- */
  (function(){
    var acc = document.getElementById('accPU');
    if(!acc) return;
    var items = Array.prototype.slice.call(acc.querySelectorAll('.acc-item'));
    function setOpen(item, open){
      var panel = item.querySelector('.acc-a');
      if(open){
        item.classList.add('open');
        panel.style.maxHeight = panel.scrollHeight + 'px';
      } else {
        item.classList.remove('open');
        panel.style.maxHeight = '0px';
      }
    }
    items.forEach(function(item){
      var btn = item.querySelector('.acc-q');
      btn.addEventListener('click', function(){
        var isOpen = item.classList.contains('open');
        items.forEach(function(o){ setOpen(o, false); });
        if(!isOpen){ setOpen(item, true); }
      });
    });
    if(items[0]){ setOpen(items[0], true); }
    window.addEventListener('resize', function(){
      var open = acc.querySelector('.acc-item.open .acc-a');
      if(open){ open.style.maxHeight = open.scrollHeight + 'px'; }
    });
  })();
	<?php
	echo '</script>';
} );

<?php
/**
 * PRINEX — Regulamin sklepu: CSS/JS treści prawnej (#34).
 * Scope: front-end. Warunkowane is_page('regulamin') — nie wycieka.
 *
 * Klon architektury #33 (Polityka) — ta sama mechanika spisu treści (dynamiczny offset,
 * pin-po-kliku, mobile accordion, przycisk DO GÓRY) + style list numerowanych paragrafów,
 * list zagnieżdżonych i bloku Załącznika. Treść w wp_posts.post_content (bez własnej ramy).
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'prinex_is_regulamin_page' ) ) {
	function prinex_is_regulamin_page() {
		return is_page( 'regulamin' );
	}
}

add_action( 'wp_head', function () {
	if ( ! prinex_is_regulamin_page() ) {
		return;
	}
	?>
<style>
body.pp-scope{background:#E8ECEF;--pp-offset:120px;}
.pp-scope .entry-header{display:none !important;}
.pp-scope .inside-article{background:transparent !important;padding-top:0 !important;padding-left:0 !important;padding-right:0 !important;}
.pp-scope .entry-content{padding-left:0;padding-right:0;margin-left:0;margin-right:0;}
.pp-scope .container:not(.grid-container){width:auto;max-width:none;margin:0;padding:8px 0 60px;}

/* breadcrumb */
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

/* layout: spis (lewa) | treść, blok dosunięty do lewej, prawa pusta; nagłówek na osi serwisu */
.pp-scope .pp-layout{display:grid;grid-template-columns:288px minmax(0,720px);gap:56px;align-items:start;justify-content:start;max-width:1064px;margin:0;}
.pp-scope .pp-layout>aside{position:sticky;top:var(--pp-offset);align-self:start;}
.pp-scope .toc-head{display:flex;align-items:center;justify-content:space-between;width:100%;background:none;border:none;font-family:inherit;text-align:left;cursor:default;padding:0 0 16px;}
.pp-scope .toc-head .toc-title{font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#8a939c;}
.pp-scope .toc-head .toc-chev{display:none;}
.pp-scope .toc-list{list-style:none;margin:0;padding:0;border-left:1px solid #d7dde2;}
.pp-scope .toc-list li{position:relative;margin:0;}
.pp-scope .toc-list a{display:flex;align-items:baseline;padding:9px 12px 9px 20px;margin-left:-1px;border-radius:0 8px 8px 0;font-size:14.5px;font-weight:400;line-height:1.4;color:#7a848d;border-left:2px solid transparent;text-decoration:none;transition:color .16s,border-color .16s,background .16s,transform .16s;}
/* numer w kolumnie stałej szerokości (jak Polityka), wyśrodkowany → tekst od tej samej pozycji */
.pp-scope .toc-list a .n{flex:0 0 22px;text-align:center;font-variant-numeric:tabular-nums;color:#b3bbc3;margin-right:8px;font-weight:600;}
.pp-scope .toc-list a:hover{color:#62992A;background:rgba(120,184,51,.1);border-left-color:#78B833;transform:translateX(2px);}
.pp-scope .toc-list a:hover .n{color:#62992A;}
.pp-scope .toc-list a.active{color:#0B457D;font-weight:700;border-left-color:#78B833;}
.pp-scope .toc-list a.active .n{color:#78B833;}
.pp-scope .toc-list a.toc-zal .n{color:#78B833;font-weight:700;}

/* treść */
.pp-scope .pp-content{max-width:720px;}
.pp-scope .pp-section{scroll-margin-top:var(--pp-offset);padding-bottom:44px;margin-bottom:44px;border-bottom:1px solid #e1e6ea;}
.pp-scope .pp-section:last-child{border-bottom:none;margin-bottom:0;}
.pp-scope .sec-head{display:flex;align-items:center;gap:15px;margin-bottom:18px;}
.pp-scope .pp-section .sec-num{flex:0 0 auto;display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:50%;background:#78B833;color:#fff;font-size:17px;font-weight:700;font-variant-numeric:tabular-nums;}
.pp-scope .pp-section h2{font-size:25px;font-weight:700;color:#0B457D;line-height:1.2;margin:0;}
.pp-scope .pp-section p{color:#333;font-size:16.5px;line-height:1.72;margin:0 0 15px;}
.pp-scope .pp-section p:last-child{margin-bottom:0;}
.pp-scope .pp-section p b,.pp-scope .pp-section li b{color:#0B457D;font-weight:700;}
.pp-scope .pp-section a.inl,.pp-scope .pp-section a{color:#62992A;font-weight:600;border-bottom:1px solid rgba(98,153,42,.35);text-decoration:none;transition:color .15s,border-color .15s;}
.pp-scope .pp-section a:hover{color:#78B833;border-color:#78B833;}

/* numerowane punkty paragrafu: granatowy numer WYŚRODKOWANY w kolumnie 38px (środek 19px =
   pod białą cyfrą w kółku sekcji); tekst punktu wcięty do 53px (38 kółko + 15 gap = pod tytułem h2). */
.pp-scope .pp-section ol.lst{list-style:none;counter-reset:it;margin:4px 0 14px;padding:0;display:flex;flex-direction:column;gap:13px;}
.pp-scope .pp-section ol.lst>li{counter-increment:it;position:relative;padding-left:53px;font-size:16.5px;line-height:1.72;color:#333;}
.pp-scope .pp-section ol.lst>li::before{content:counter(it)".";position:absolute;left:0;top:0;width:38px;text-align:center;font-weight:700;color:#0B457D;font-variant-numeric:tabular-nums;}
/* akapity bez numeru (np. §11) — tekst wyrównany do tytułu sekcji (53px) */
.pp-scope .pp-section>p{padding-left:53px;}
.pp-scope .pp-section ol.lst>li>ul.sub{margin:10px 0 2px;}
/* lista zagnieżdżona (tolerancje §8 / organy §10) */
.pp-scope .pp-section ul.sub{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:9px;}
.pp-scope .pp-section ul.sub li{position:relative;padding-left:22px;font-size:16px;line-height:1.6;color:#333;}
.pp-scope .pp-section ul.sub li::before{content:"";position:absolute;left:2px;top:11px;width:8px;height:2px;background:#78B833;border-radius:2px;}

/* placeholdery do uzupełnienia */
.pp-scope .ph{color:#9aa4ad;font-style:italic;font-weight:400;}

/* Załącznik — wyróżniony blok */
.pp-scope .pp-zal .sec-num{background:#eaf3fb;color:#0B457D;}
.pp-scope .zal-box{background:#fbfdf7;border:1px solid #d8e8c2;border-left:3px solid #78B833;border-radius:12px;padding:26px 30px;}
.pp-scope .zal-box .zal-note{font-size:15px;color:#5a6570;font-style:italic;margin:0 0 16px;}
.pp-scope .zal-box .zal-to{font-size:16px;color:#0B457D;font-weight:700;margin:0 0 16px;}
.pp-scope .zal-box p{font-size:16.5px;line-height:1.7;color:#333;margin:0 0 16px;}
.pp-scope .zal-box ul.zal-fields{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:14px;}
.pp-scope .zal-box ul.zal-fields li{position:relative;padding-left:0;font-size:16px;color:#333;}
.pp-scope .zal-box ul.zal-fields li::before{content:none;}
.pp-scope .zal-box ul.zal-fields li b{color:#0B457D;font-weight:600;}
.pp-scope .zal-box .dots{color:#c2cad3;letter-spacing:2px;}

/* przycisk „DO GÓRY" */
.pp-scope .pp-totop{position:fixed;right:26px;bottom:26px;z-index:60;width:48px;height:48px;border-radius:50%;background:#78B833;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 20px rgba(11,69,125,.18);opacity:0;visibility:hidden;transform:translateY(10px);transition:opacity .25s,visibility .25s,transform .25s,background .16s;}
.pp-scope .pp-totop.show{opacity:1;visibility:visible;transform:translateY(0);}
.pp-scope .pp-totop:hover{background:#62992A;}
.pp-scope .pp-totop:focus-visible{outline:3px solid rgba(11,69,125,.55);outline-offset:2px;}
.pp-scope .pp-totop svg{width:22px;height:22px;stroke:#fff;fill:none;stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round;}

@media(max-width:960px){
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

add_filter( 'body_class', function ( $classes ) {
	if ( prinex_is_regulamin_page() ) {
		$classes[] = 'pp-scope';
	}
	return $classes;
} );

/* Wyłącz wpautop — treść ma własne <p>, wpautop wstrzykuje puste <p> psując grid. */
add_action( 'wp', function () {
	if ( prinex_is_regulamin_page() ) {
		remove_filter( 'the_content', 'wpautop' );
		remove_filter( 'the_content', 'shortcode_unautop' );
	}
} );

/* Breadcrumb — element treści; „PRINEX" → strona główna (spójnie z Polityką). */
add_filter( 'the_content', function ( $content ) {
	if ( ! prinex_is_regulamin_page() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$bc = '<nav class="breadcrumb" aria-label="Ścieżka nawigacji">'
		. '<a href="' . esc_url( home_url( '/' ) ) . '">PRINEX</a>'
		. '<span class="sep">/</span>'
		. '<span class="cur">Regulamin</span>'
		. '</nav>';
	if ( false !== strpos( $content, '<div class="container">' ) ) {
		return preg_replace( '/(<div class="container">)/', '$1' . $bc, $content, 1 );
	}
	return $bc . $content;
}, 20 );

/* JS — spis treści: mobile collapse + scroll-spy (pin po kliku, dynamiczny offset). */
add_action( 'wp_footer', function () {
	if ( ! prinex_is_regulamin_page() ) {
		return;
	}
	?>
<button type="button" class="pp-totop" aria-label="Do góry" title="Do góry">
  <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
</button>
<script>
(function(){
  function ready(fn){ if(document.readyState!=='loading'){fn();} else {document.addEventListener('DOMContentLoaded',fn);} }
  function smoothTo(el){ if(el){ el.scrollIntoView({behavior:'smooth',block:'start'}); } }
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
    var BASE=120;
    function fixedAdminBarH(){
      var bar=document.getElementById('wpadminbar');
      if(bar && getComputedStyle(bar).position==='fixed'){ return bar.offsetHeight||0; }
      return 0;
    }
    function applyOffset(){ scopeEl.style.setProperty('--pp-offset',(BASE+fixedAdminBarH())+'px'); }
    applyOffset();
    [0,400,1200].forEach(function(d){ setTimeout(applyOffset,d); });
    function currentOffset(){ return parseInt(getComputedStyle(scopeEl).getPropertyValue('--pp-offset'),10)||BASE; }

    if(toc){ try{ toc.setAttribute('data-ppspy','reg-v1'); }catch(e){} }

    var pinned=false;
    function highlight(id){ links.forEach(function(a){ a.classList.toggle('active', a.getAttribute('href')==='#'+id); }); }
    function setActive(){
      if(pinned) return;
      if(!sections.length||!links.length) return;
      var line=currentOffset()+SPY_BUFFER;
      var active=sections[0];
      for(var i=0;i<sections.length;i++){
        if(sections[i].getBoundingClientRect().top<=line){ active=sections[i]; }
        else { break; }
      }
      if((window.innerHeight+window.pageYOffset)>=(document.documentElement.scrollHeight-2)){
        active=sections[sections.length-1];
      }
      highlight(active.id);
    }
    function unpin(){ if(pinned){ pinned=false; setActive(); } }

    if(sections.length&&links.length){
      var raf=null;
      var onScroll=function(){ if(raf) return; raf=requestAnimationFrame(function(){ raf=null; setActive(); }); };
      var settleT=null;
      var onSettle=function(){ if(settleT) clearTimeout(settleT); settleT=setTimeout(setActive,120); };
      window.addEventListener('scroll', function(){ onScroll(); onSettle(); }, {passive:true});
      if('onscrollend' in window){ window.addEventListener('scrollend', setActive, {passive:true}); }
      window.addEventListener('resize', function(){ applyOffset(); setActive(); }, {passive:true});
      window.addEventListener('wheel', unpin, {passive:true});
      window.addEventListener('touchmove', unpin, {passive:true});
      window.addEventListener('keydown', function(e){
        if(['ArrowUp','ArrowDown','PageUp','PageDown','Home','End',' ','Spacebar'].indexOf(e.key)>=0) unpin();
      }, {passive:true});
      setActive();
    }
    links.forEach(function(a){
      a.addEventListener('click',function(e){
        var href=a.getAttribute('href')||'';
        if(href.charAt(0)==='#'){
          var t=document.getElementById(href.slice(1));
          if(t){
            e.preventDefault();
            pinned=true;
            highlight(href.slice(1));
            smoothTo(t);
            if(history.pushState){ history.pushState(null,'',href); }
          }
        }
        if(window.matchMedia('(max-width:960px)').matches&&toc){ toc.classList.remove('open'); }
      });
    });
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

<?php
/**
 * PRINEX — Complianz cookie banner: branding (#35).
 * Scope: front-end. Nakłada tokeny PRINEX (Figtree, granat #0B457D / zieleń #78B833)
 * na baner Complianz przez override klas (!important). NIE edytuje rdzenia wtyczki.
 * Baner pojawia się dopiero po dokończeniu kreatora Complianz (GUI) — CSS jest wtedy gotowy.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', function () {
	?>
<style id="prinex-cmplz-branding">
/* baner — biały box, akcent granat, tekst #333, font Figtree */
.cmplz-cookiebanner,#cmplz-cookiebanner-container .cmplz-cookiebanner{
  font-family:'Figtree',sans-serif !important;
  background:#fff !important;color:#333 !important;
  border:1px solid #e1e6ea !important;border-top:3px solid #0B457D !important;
  border-radius:14px !important;box-shadow:0 12px 40px rgba(11,69,125,.18) !important;
}
.cmplz-cookiebanner *{font-family:'Figtree',sans-serif !important;}
.cmplz-cookiebanner .cmplz-title{color:#0B457D !important;font-weight:700 !important;}
.cmplz-cookiebanner .cmplz-body,.cmplz-cookiebanner .cmplz-message,.cmplz-cookiebanner .cmplz-message *{color:#333 !important;}
.cmplz-cookiebanner a{color:#62992A !important;text-decoration:underline;}
.cmplz-cookiebanner a:hover{color:#78B833 !important;}
/* przyciski: akceptacja = zieleń, pozostałe = kontur granat */
.cmplz-cookiebanner .cmplz-btn{font-weight:600 !important;border-radius:8px !important;}
.cmplz-cookiebanner .cmplz-accept{background:#78B833 !important;border-color:#78B833 !important;color:#fff !important;}
.cmplz-cookiebanner .cmplz-accept:hover{background:#62992A !important;border-color:#62992A !important;color:#fff !important;}
.cmplz-cookiebanner .cmplz-deny,
.cmplz-cookiebanner .cmplz-view-preferences,
.cmplz-cookiebanner .cmplz-save-preferences{background:#fff !important;color:#0B457D !important;border:1px solid #0B457D !important;}
.cmplz-cookiebanner .cmplz-deny:hover,
.cmplz-cookiebanner .cmplz-view-preferences:hover,
.cmplz-cookiebanner .cmplz-save-preferences:hover{background:#0B457D !important;color:#fff !important;}
/* kategorie (gdy włączone w przyszłości) — akcent granat */
.cmplz-cookiebanner .cmplz-categories .cmplz-category,.cmplz-cookiebanner .cmplz-category{border-color:#e1e6ea !important;}
.cmplz-cookiebanner .cmplz-category .cmplz-title{color:#0B457D !important;}
</style>
	<?php
} );

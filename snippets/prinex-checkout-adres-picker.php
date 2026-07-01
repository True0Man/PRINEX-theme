<?php
/**
 * PRINEX — Checkout: picker zapisanego adresu (Warstwa 2c-int).
 * Scope: front-end.
 *
 * ŻELAZNA DYSCYPLINA #28 (nietykalna warstwa):
 *  - to DODATEK OBOK rdzenia — NIE modyfikuje form-billing.php / form-checkout.php /
 *    filtra pól / walidacji / zapisu #28.
 *  - działa WYŁĄCZNIE po stronie klienta: wybór zapisanego adresu → PREFILL istniejących
 *    inputów billing (#billing_*). Sposób zbierania/walidacji/zapisu danych przez #28 = BEZ ZMIAN.
 *  - efekt końcowy checkoutu identyczny: pola startują wypełnione (adres domyślny) zamiast puste.
 *  - GOŚĆ (niezalogowany) → picker się NIE renderuje → checkout bez zmian.
 *
 * Renderowany przez hook woocommerce_checkout_before_customer_details (odpalany przez
 * form-checkout.php PRZED kartą "Dane odbiorcy"). Dane = własne adresy klienta (#30,
 * prinex_get_addresses) osadzone server-side (bez AJAX).
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

add_action( 'woocommerce_checkout_before_customer_details', function () {
	if ( ! is_user_logged_in() || ! function_exists( 'prinex_get_addresses' ) ) {
		return;
	}
	$addrs = prinex_get_addresses( get_current_user_id() );
	if ( empty( $addrs ) ) {
		return;
	}
	$default_id = '';
	foreach ( $addrs as $a ) {
		if ( ! empty( $a['is_default'] ) ) {
			$default_id = $a['id'];
			break;
		}
	}
	if ( '' === $default_id ) {
		$default_id = $addrs[0]['id'];
	}
	?>
	<div class="pxc-addrpick" id="pxc-addrpick">
		<label class="pxc-addrpick-lbl" for="pxc-addrpick-sel">Wybierz zapisany adres</label>
		<div class="pxc-addrpick-wrap">
			<select class="pxc-addrpick-sel" id="pxc-addrpick-sel">
				<?php foreach ( $addrs as $a ) :
					$who   = ( 'firma' === $a['type'] && $a['company'] ) ? $a['company'] : trim( $a['first_name'] . ' ' . $a['last_name'] );
					$label = $who . ' — ' . $a['address_1'] . ', ' . $a['postcode'] . ' ' . $a['city'] . ( ! empty( $a['is_default'] ) ? ' (domyślny)' : '' );
				?>
					<option value="<?php echo esc_attr( $a['id'] ); ?>" <?php selected( $a['id'], $default_id ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
				<option value="">— wpisz ręcznie —</option>
			</select>
		</div>
	</div>
	<script type="application/json" id="pxc-addrpick-data"><?php echo wp_json_encode( array_column( $addrs, null, 'id' ) ); // phpcs:ignore ?></script>
	<?php
} );

/* CSS — scoped body.woocommerce-checkout */
add_action( 'wp_head', function () {
	if ( ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}
	?>
<style>
body.woocommerce-checkout .pxc-addrpick{background:#fff;border:1px solid #e1e6ea;border-radius:16px;padding:18px 22px;box-shadow:0 2px 14px rgba(11,69,125,.06);margin-bottom:20px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;}
body.woocommerce-checkout .pxc-addrpick-lbl{font-size:13px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#0B457D;margin:0;flex:none;}
body.woocommerce-checkout .pxc-addrpick-wrap{flex:1;min-width:220px;}
body.woocommerce-checkout .pxc-addrpick-sel{width:100%;border:1px solid #d0d5db;border-radius:8px;padding:11px 14px;font-size:15px;font-family:inherit;color:#333;background:#fff;}
body.woocommerce-checkout .pxc-addrpick-sel:focus{outline:none;border-color:#78B833;box-shadow:0 0 0 2px rgba(120,184,51,.16);}
</style>
	<?php
} );

/* JS — prefill (client-side); nie dotyka mechaniki #28 */
add_action( 'wp_footer', function () {
	if ( ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}
	?>
<script>
(function(){
  function ready(fn){ if(document.readyState!=='loading'){fn();} else {document.addEventListener('DOMContentLoaded',fn);} }
  ready(function(){
    var sel=document.getElementById('pxc-addrpick-sel'), dataEl=document.getElementById('pxc-addrpick-data');
    if(!sel||!dataEl) return;
    var data={}; try{ data=JSON.parse(dataEl.textContent)||{}; }catch(e){ return; }

    function setInput(id,val){ var f=document.getElementById(id); if(!f) return; f.value=(val==null?'':val); }
    function expandForm(){
      var f=document.getElementById('pxc-recipient-form');
      if(f && f.hasAttribute('hidden')){
        f.removeAttribute('hidden');
        var s=document.getElementById('pxc-recipient-summary'); if(s) s.setAttribute('hidden','');
        var el=document.getElementById('pxc-edit-link'); if(el) el.textContent='Zwiń';
      }
    }
    function fill(id){
      var a=data[id]; if(!a) return;
      // typ Osoba/Firma — klik pigułki (uruchamia natywną logikę #28: hidden input + firma-only)
      var pill=document.querySelector('.pxc-type-pill[data-type="'+(a.type==='firma'?'firma':'osoba')+'"]');
      if(pill) pill.click();
      setInput('billing_first_name',a.first_name);
      setInput('billing_last_name',a.last_name);
      setInput('billing_company',a.company);
      setInput('billing_nip',a.nip);
      setInput('billing_address_1',a.address_1);
      setInput('billing_address_2',a.address_2);
      setInput('billing_postcode',a.postcode);
      setInput('billing_city',a.city);
      setInput('billing_phone',a.phone);
      var cc=document.getElementById('billing_country');
      if(cc){ cc.value=a.country||'PL'; try{cc.dispatchEvent(new Event('change',{bubbles:true}));}catch(e){} }
      expandForm();
    }

    sel.addEventListener('change', function(){ if(sel.value) fill(sel.value); });
    // start: prefill adresem domyślnym (pola startują wypełnione)
    if(sel.value) fill(sel.value);
  });
})();
</script>
	<?php
} );

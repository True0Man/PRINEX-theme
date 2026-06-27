/**
 * PRINEX — Panel admina Cennik (#24)
 *
 * Podstrona wp-admin „Cennik PRINEX" (menu pod WooCommerce).
 * Zapis do opcji `prinex_cennik` (JSON) przez prinex_sanitize_cennik().
 * Meta box na produkcie: _prinex_folia, _prinex_rodzaj, _prinex_optymalny.
 *
 * Wymaga aktywnego snippetu #23 (silnik cennika).
 */

/* ============================================================
   MENU + STRONA ADMIN
   ============================================================ */

add_action( 'admin_menu', function () {
	add_submenu_page(
		'woocommerce',
		'Cennik PRINEX',
		'Cennik PRINEX',
		'manage_woocommerce',
		'prinex-cennik',
		'prinex_cennik_admin_page'
	);
} );

function prinex_cennik_admin_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( 'Brak uprawnień.' );
	}

	/* Zapis */
	if ( isset( $_POST['prinex_cennik_nonce'] ) &&
		wp_verify_nonce( $_POST['prinex_cennik_nonce'], 'prinex_cennik_save' ) ) {

		$raw  = isset( $_POST['cennik'] ) ? wp_unslash( $_POST['cennik'] ) : array();
		$data = prinex_sanitize_cennik( $raw );
		update_option( 'prinex_cennik', wp_json_encode( $data ), false );
		// flush static cache
		if ( function_exists( 'prinex_cennik' ) ) {
			// reinitialize by calling with reset
			add_filter( 'prinex_cennik_flush', '__return_true' );
		}
		echo '<div class="notice notice-success is-dismissible"><p>Cennik zapisany.</p></div>';
	}

	$cennik  = function_exists( 'prinex_cennik' ) ? prinex_cennik() : array( 'global' => array(), 'folie' => array() );
	$global  = isset( $cennik['global'] ) ? $cennik['global'] : array();
	$folie   = isset( $cennik['folie'] ) && is_array( $cennik['folie'] ) ? $cennik['folie'] : array();

	$g = function ( $key, $default = 0 ) use ( $global ) {
		return isset( $global[ $key ] ) ? esc_attr( $global[ $key ] ) : $default;
	};

	// Nazwy folii z atrybutu WC (do select w meta boxie)
	$wc_folie_terms = get_terms( array( 'taxonomy' => 'pa_folia', 'hide_empty' => false ) );

	?>
	<div class="wrap" id="prinex-cennik-wrap">
	<style>
	#prinex-cennik-wrap{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;max-width:1100px;}
	#prinex-cennik-wrap h1{color:#0B457D;display:flex;align-items:center;gap:10px;}
	#prinex-cennik-wrap h1::before{content:"";display:inline-block;width:36px;height:3px;background:#78B833;border-radius:2px;}
	.pc-section{background:#fff;border:1px solid #dde1e5;border-radius:12px;padding:20px 24px;margin-bottom:20px;}
	.pc-section h2{font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#6b7783;margin:0 0 16px;display:flex;align-items:center;gap:8px;}
	.pc-section h2::before{content:"";width:20px;height:2px;background:#78B833;border-radius:2px;flex:0 0 auto;}
	.pc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;}
	.pc-field{display:flex;flex-direction:column;gap:5px;}
	.pc-field label{font-size:12px;font-weight:600;color:#0B457D;}
	.pc-field .unit{font-size:11px;color:#6b7783;font-weight:400;}
	.pc-field input[type=number],.pc-field input[type=text],.pc-field select{padding:7px 9px;border:1.5px solid #dde1e5;border-radius:8px;font-size:13px;font-weight:600;color:#0B457D;width:100%;}
	.pc-field input:focus,.pc-field select:focus{outline:none;border-color:#78B833;box-shadow:0 0 0 3px rgba(120,184,51,.15);}
	.foil-card{border:1.5px solid #dde1e5;border-radius:12px;padding:16px;margin-bottom:12px;background:#fafbfc;}
	.foil-card.active{border-color:#78B833;background:rgba(120,184,51,.04);}
	.foil-head{display:flex;align-items:center;gap:10px;margin-bottom:14px;cursor:pointer;}
	.foil-head .foil-name-display{font-size:14px;font-weight:700;color:#0B457D;flex:1;}
	.foil-toggle{font-size:12px;color:#6b7783;background:#f0f1f3;border:none;border-radius:6px;padding:4px 10px;cursor:pointer;}
	.foil-body{display:none;}
	.foil-card.open .foil-body{display:block;}
	.foil-del{background:none;border:none;color:#c0392b;font-size:18px;cursor:pointer;line-height:1;opacity:.6;margin-left:auto;}
	.foil-del:hover{opacity:1;}
	.brk-header,.brk-row-wrap{display:grid;grid-template-columns:1fr 1fr 1.2fr 30px;gap:8px;align-items:center;}
	.brk-header{font-size:10.5px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#6b7783;margin:6px 0 4px;}
	.brk-row-wrap{margin-bottom:8px;}
	.brk-row-wrap input{padding:6px 8px;border:1.5px solid #dde1e5;border-radius:7px;font-size:13px;width:100%;}
	.brk-del{background:none;border:none;color:#c0392b;font-size:17px;cursor:pointer;line-height:1;opacity:.6;}
	.brk-del:hover{opacity:1;}
	.pc-btn-add{background:#fff;color:#62992A;border:1.5px dashed #78B833;padding:7px 12px;font-size:12px;font-weight:600;border-radius:8px;cursor:pointer;letter-spacing:.02em;}
	.pc-btn-add:hover{background:rgba(120,184,51,.08);}
	.pc-btn-save{background:#78B833;color:#fff;border:none;padding:11px 26px;font-size:14px;font-weight:700;border-radius:9px;cursor:pointer;letter-spacing:.02em;}
	.pc-btn-save:hover{background:#62992A;}
	.pc-hint{font-size:11.5px;color:#6b7783;margin:6px 0 0;line-height:1.5;}
	.pc-note-plaska{font-size:12px;color:#6b7783;font-style:italic;padding:10px 0;}
	</style>

	<h1>Cennik PRINEX</h1>
	<p style="color:#6b7783;font-size:13px;margin:0 0 20px;">Stawki sprzedaży per folia. Zmiany obowiązują natychmiast po zapisie i przeliczają cały sklep.</p>

	<form method="post" action="">
	<?php wp_nonce_field( 'prinex_cennik_save', 'prinex_cennik_nonce' ); ?>

	<!-- STAWKI GLOBALNE -->
	<div class="pc-section">
		<h2>Stawki globalne <span style="text-transform:none;letter-spacing:0;font-weight:400;">(wspólne dla wszystkich folii)</span></h2>
		<div class="pc-grid">
			<div class="pc-field">
				<label>Przygotowanie <span class="unit">zł (raz na zlecenie)</span></label>
				<input type="number" name="cennik[global][setup]" value="<?php echo $g( 'setup', 149 ); ?>" min="0" step="1">
			</div>
			<div class="pc-field">
				<label>Koszt druku <span class="unit">zł/m²</span></label>
				<input type="number" name="cennik[global][druk]" value="<?php echo $g( 'druk', 20 ); ?>" min="0" step="1">
			</div>
		</div>

		<div style="margin-top:16px;">
			<details>
				<summary style="font-size:12px;font-weight:600;color:#0B457D;cursor:pointer;margin-bottom:10px;">Żywica + arkusz (podgląd marży — nie wchodzi do ceny klienta) ›</summary>
				<div class="pc-grid" style="margin-top:12px;">
					<div class="pc-field">
						<label>Zużycie żywicy <span class="unit">g/cm²</span></label>
						<input type="number" name="cennik[global][zywica_uzycie]" value="<?php echo $g( 'zywica_uzycie', 0.175 ); ?>" min="0" step="0.001">
					</div>
					<div class="pc-field">
						<label>Partia żywicy <span class="unit">kg</span></label>
						<input type="number" name="cennik[global][zywica_partia_kg]" value="<?php echo $g( 'zywica_partia_kg', 120 ); ?>" min="0" step="1">
					</div>
					<div class="pc-field">
						<label>Cena partii żywicy <span class="unit">zł</span></label>
						<input type="number" name="cennik[global][zywica_partia_cena]" value="<?php echo $g( 'zywica_partia_cena', 9000 ); ?>" min="0" step="1">
					</div>
					<div class="pc-field">
						<label>Arkusz szer. <span class="unit">mm</span></label>
						<input type="number" name="cennik[global][arkusz_w]" value="<?php echo $g( 'arkusz_w', 210 ); ?>" min="0" step="1">
					</div>
					<div class="pc-field">
						<label>Arkusz wys. <span class="unit">mm</span></label>
						<input type="number" name="cennik[global][arkusz_h]" value="<?php echo $g( 'arkusz_h', 295 ); ?>" min="0" step="1">
					</div>
					<div class="pc-field">
						<label>Pole rob. szer. <span class="unit">mm</span></label>
						<input type="number" name="cennik[global][pole_w]" value="<?php echo $g( 'pole_w', 190 ); ?>" min="0" step="1">
					</div>
					<div class="pc-field">
						<label>Pole rob. wys. <span class="unit">mm</span></label>
						<input type="number" name="cennik[global][pole_h]" value="<?php echo $g( 'pole_h', 265 ); ?>" min="0" step="1">
					</div>
					<div class="pc-field">
						<label>Zapas prodk. <span class="unit">%</span></label>
						<input type="number" name="cennik[global][zapas]" value="<?php echo $g( 'zapas', 5 ); ?>" min="0" max="100" step="1">
					</div>
				</div>
			</details>
		</div>
	</div>

	<!-- FOLIE -->
	<div class="pc-section">
		<h2>Folie — stawki sprzedaży</h2>
		<p class="pc-hint" style="margin:0 0 16px;">Karta 3D: stawka bazowa + przedziały nakładu (nadpisują bazową w danym zakresie). Karta Płaska = pusty slot (Etap 2+).</p>

		<div id="prinex-foils-container">
		<?php
		foreach ( $folie as $fi => $foil ) :
			$fid   = esc_attr( $foil['id'] ?? '' );
			$fname = esc_attr( $foil['name'] ?? '' );
			$fcost = esc_attr( $foil['cost_per_m2'] ?? 0 );
			$k3d   = isset( $foil['karta_3d'] ) && is_array( $foil['karta_3d'] ) ? $foil['karta_3d'] : array( 'sale_base' => 0.12, 'brackets' => array() );
			$base3 = esc_attr( $k3d['sale_base'] ?? 0.12 );
			$brks  = isset( $k3d['brackets'] ) && is_array( $k3d['brackets'] ) ? $k3d['brackets'] : array();
			?>
			<div class="foil-card open" data-fi="<?php echo $fi; ?>">
				<div class="foil-head" onclick="prxToggleFoil(this)">
					<span class="foil-name-display"><?php echo esc_html( $foil['name'] ?? 'Folia' ); ?></span>
					<button type="button" class="foil-toggle">▾</button>
					<button type="button" class="foil-del" onclick="event.stopPropagation();prxDelFoil(this)" title="Usuń folię">×</button>
				</div>
				<div class="foil-body">
					<input type="hidden" name="cennik[folie][<?php echo $fi; ?>][id]" value="<?php echo $fid; ?>" class="foil-id-input">
					<div class="pc-grid" style="margin-bottom:14px;">
						<div class="pc-field">
							<label>Nazwa folii</label>
							<input type="text" name="cennik[folie][<?php echo $fi; ?>][name]" value="<?php echo $fname; ?>"
								oninput="this.closest('.foil-card').querySelector('.foil-name-display').textContent=this.value||'Folia'">
						</div>
						<div class="pc-field">
							<label>Koszt folii <span class="unit">zł/m² (marża)</span></label>
							<input type="number" name="cennik[folie][<?php echo $fi; ?>][cost_per_m2]" value="<?php echo $fcost; ?>" min="0" step="1">
						</div>
						<div class="pc-field">
							<label>Stawka bazowa 3D <span class="unit">zł/cm²</span></label>
							<input type="number" name="cennik[folie][<?php echo $fi; ?>][karta_3d][sale_base]" value="<?php echo $base3; ?>" min="0" step="0.001">
						</div>
					</div>

					<div style="margin-bottom:8px;font-size:12px;font-weight:700;color:#0B457D;">Przedziały nakładu 3D</div>
					<div class="brk-header">
						<span>Od (szt.)</span><span>Do (szt.)</span><span>Stawka zł/cm²</span><span></span>
					</div>
					<div class="brk-list" id="brk-list-<?php echo $fi; ?>">
					<?php foreach ( $brks as $bi => $b ) : ?>
						<div class="brk-row-wrap">
							<input type="number" name="cennik[folie][<?php echo $fi; ?>][karta_3d][brackets][<?php echo $bi; ?>][od]"
								value="<?php echo esc_attr( $b['od'] ?? '' ); ?>" min="1" step="1" placeholder="np. 500">
							<input type="number" name="cennik[folie][<?php echo $fi; ?>][karta_3d][brackets][<?php echo $bi; ?>][do]"
								value="<?php echo esc_attr( $b['do'] ?? '' ); ?>" min="1" step="1" placeholder="i więcej">
							<input type="number" name="cennik[folie][<?php echo $fi; ?>][karta_3d][brackets][<?php echo $bi; ?>][rate]"
								value="<?php echo esc_attr( $b['rate'] ?? '' ); ?>" min="0" step="0.001">
							<button type="button" class="brk-del" onclick="prxDelBrk(this)">×</button>
						</div>
					<?php endforeach; ?>
					</div>
					<button type="button" class="pc-btn-add" onclick="prxAddBrk(<?php echo $fi; ?>)">+ DODAJ PRZEDZIAŁ</button>
					<p class="pc-hint">Ostatni przedział z pustym DO = „i więcej". Nakład poza przedziałami → stawka bazowa.</p>

					<details style="margin-top:12px;">
						<summary style="font-size:11.5px;color:#6b7783;cursor:pointer;">Karta PŁASKA (Etap 2+) ›</summary>
						<p class="pc-note-plaska">Pusty slot — cennik naklejek płaskich wdrożony będzie w Etapie 2+.</p>
					</details>
				</div>
			</div>
		<?php endforeach; ?>
		</div>

		<button type="button" class="pc-btn-add" id="prinex-add-foil" style="margin-top:8px;">+ DODAJ FOLIĘ</button>
	</div>

	<div style="display:flex;gap:12px;align-items:center;">
		<button type="submit" class="pc-btn-save">Zapisz cennik</button>
		<span style="font-size:12px;color:#6b7783;">Zapis nadpisuje całą opcję <code>prinex_cennik</code>.</span>
	</div>

	</form>
	</div>

	<script>
	(function(){
		/* Global counters (wyżej niż max indeks z PHP) */
		var foilIdx  = <?php echo max( count( $folie ), 1 ); ?>;
		var brkIdx   = {};  /* per foilIdx */

		/* Inicjuj liczniki przedziałów dla istniejących folii */
		<?php foreach ( $folie as $fi => $foil ) : ?>
		brkIdx[<?php echo $fi; ?>] = <?php echo count( isset( $foil['karta_3d']['brackets'] ) ? $foil['karta_3d']['brackets'] : array() ); ?>;
		<?php endforeach; ?>

		window.prxToggleFoil = function(head) {
			var card = head.closest('.foil-card');
			card.classList.toggle('open');
		};

		window.prxDelFoil = function(btn) {
			if (!confirm('Usunąć tę folię z cennika?')) return;
			btn.closest('.foil-card').remove();
			prxReindex();
		};

		window.prxDelBrk = function(btn) {
			btn.closest('.brk-row-wrap').remove();
		};

		window.prxAddBrk = function(fi) {
			if (!(fi in brkIdx)) brkIdx[fi] = 0;
			var bi = brkIdx[fi]++;
			var list = document.getElementById('brk-list-' + fi);
			if (!list) return;
			var row = document.createElement('div');
			row.className = 'brk-row-wrap';
			row.innerHTML =
				'<input type="number" name="cennik[folie][' + fi + '][karta_3d][brackets][' + bi + '][od]" min="1" step="1" placeholder="np. 500">' +
				'<input type="number" name="cennik[folie][' + fi + '][karta_3d][brackets][' + bi + '][do]" min="1" step="1" placeholder="i więcej">' +
				'<input type="number" name="cennik[folie][' + fi + '][karta_3d][brackets][' + bi + '][rate]" min="0" step="0.001">' +
				'<button type="button" class="brk-del" onclick="prxDelBrk(this)">×</button>';
			list.appendChild(row);
		};

		document.getElementById('prinex-add-foil').addEventListener('click', function() {
			var fi = foilIdx++;
			brkIdx[fi] = 0;
			/* Generuj unikalny id slug z timestampa */
			var slug = 'folia-' + Date.now();
			var card = document.createElement('div');
			card.className = 'foil-card open';
			card.dataset.fi = fi;
			card.innerHTML =
				'<div class="foil-head" onclick="prxToggleFoil(this)">' +
					'<span class="foil-name-display">Nowa folia</span>' +
					'<button type="button" class="foil-toggle">▾</button>' +
					'<button type="button" class="foil-del" onclick="event.stopPropagation();prxDelFoil(this)" title="Usuń folię">×</button>' +
				'</div>' +
				'<div class="foil-body">' +
					'<input type="hidden" name="cennik[folie][' + fi + '][id]" value="' + slug + '" class="foil-id-input">' +
					'<div class="pc-grid" style="margin-bottom:14px;">' +
						'<div class="pc-field"><label>Nazwa folii</label>' +
							'<input type="text" name="cennik[folie][' + fi + '][name]" value="Nowa folia" ' +
								'oninput="this.closest(\'.foil-card\').querySelector(\'.foil-name-display\').textContent=this.value||\'Folia\'"></div>' +
						'<div class="pc-field"><label>Koszt folii <span class="unit">zł/m²</span></label>' +
							'<input type="number" name="cennik[folie][' + fi + '][cost_per_m2]" value="30" min="0" step="1"></div>' +
						'<div class="pc-field"><label>Stawka bazowa 3D <span class="unit">zł/cm²</span></label>' +
							'<input type="number" name="cennik[folie][' + fi + '][karta_3d][sale_base]" value="0.12" min="0" step="0.001"></div>' +
					'</div>' +
					'<div style="margin-bottom:8px;font-size:12px;font-weight:700;color:#0B457D;">Przedziały nakładu 3D</div>' +
					'<div class="brk-header"><span>Od (szt.)</span><span>Do (szt.)</span><span>Stawka zł/cm²</span><span></span></div>' +
					'<div class="brk-list" id="brk-list-' + fi + '"></div>' +
					'<button type="button" class="pc-btn-add" onclick="prxAddBrk(' + fi + ')">+ DODAJ PRZEDZIAŁ</button>' +
					'<p class="pc-hint">Ostatni przedział z pustym DO = „i więcej".</p>' +
				'</div>';
			document.getElementById('prinex-foils-container').appendChild(card);
		});

		/* Reindexuje folie po usunięciu — naprawia name="" atrybuty */
		function prxReindex() {
			var cards = document.querySelectorAll('#prinex-foils-container .foil-card');
			cards.forEach(function(card, fi) {
				card.dataset.fi = fi;
				card.querySelectorAll('[name]').forEach(function(el) {
					el.name = el.name.replace(/cennik\[folie\]\[\d+\]/, 'cennik[folie][' + fi + ']');
				});
				/* Aktualizuj id brk-list */
				var blist = card.querySelector('.brk-list');
				if (blist) blist.id = 'brk-list-' + fi;
			});
			foilIdx = cards.length;
		}
	})();
	</script>
	<?php
}

/* ============================================================
   META BOX NA PRODUKCIE
   ============================================================ */

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'prinex_cennik_meta',
		'Cennik PRINEX — przypisanie',
		'prinex_cennik_meta_box_html',
		'product',
		'side',
		'default'
	);
} );

function prinex_cennik_meta_box_html( $post ) {
	$cennik  = function_exists( 'prinex_cennik' ) ? prinex_cennik() : array( 'folie' => array() );
	$folie   = isset( $cennik['folie'] ) && is_array( $cennik['folie'] ) ? $cennik['folie'] : array();

	$cur_folia   = get_post_meta( $post->ID, '_prinex_folia', true );
	$cur_rodzaj  = get_post_meta( $post->ID, '_prinex_rodzaj', true ) ?: '3d';
	$cur_optimal = get_post_meta( $post->ID, '_prinex_optymalny', true );

	// Wartości atrybutu Nakład dla tego produktu
	$naklad_terms = array();
	$product = wc_get_product( $post->ID );
	if ( $product && $product->is_type( 'variable' ) ) {
		$terms = wc_get_product_terms( $post->ID, 'pa_naklad', array( 'fields' => 'all' ) );
		foreach ( $terms as $t ) {
			$naklad_terms[] = $t;
		}
	}

	wp_nonce_field( 'prinex_cennik_meta_save', 'prinex_cennik_meta_nonce' );
	?>
	<style>
	.prinex-mb label{font-size:12px;font-weight:600;color:#0B457D;display:block;margin-bottom:4px;}
	.prinex-mb select,.prinex-mb input{width:100%;margin-bottom:12px;padding:5px 7px;border:1.5px solid #dde1e5;border-radius:7px;font-size:12px;}
	.prinex-mb select:focus,.prinex-mb input:focus{outline:none;border-color:#78B833;}
	.prinex-mb .pm-hint{font-size:11px;color:#6b7783;margin:-8px 0 10px;}
	</style>
	<div class="prinex-mb">
		<label for="_prinex_folia">Folia</label>
		<select name="_prinex_folia" id="_prinex_folia">
			<option value="">— brak przypisania —</option>
			<?php foreach ( $folie as $f ) : ?>
				<option value="<?php echo esc_attr( $f['id'] ); ?>" <?php selected( $cur_folia, $f['id'] ); ?>>
					<?php echo esc_html( $f['name'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<label>Rodzaj karty cenowej</label>
		<select name="_prinex_rodzaj">
			<option value="3d" <?php selected( $cur_rodzaj, '3d' ); ?>>Naklejki 3D</option>
			<option value="plaska" <?php selected( $cur_rodzaj, 'plaska' ); ?>>Płaskie (Etap 2+)</option>
		</select>

		<label for="_prinex_optymalny">OPTYMALNY nakład</label>
		<select name="_prinex_optymalny" id="_prinex_optymalny">
			<option value="">— brak —</option>
			<?php foreach ( $naklad_terms as $t ) : ?>
				<option value="<?php echo esc_attr( $t->slug ); ?>" <?php selected( $cur_optimal, $t->slug ); ?>>
					<?php echo esc_html( $t->name ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="pm-hint">Jeden nakład oznaczony jako OPTYMALNY — wyświetli zielony badge na swatchu.</p>
	</div>
	<?php
}

add_action( 'save_post_product', function ( $post_id ) {
	if ( ! isset( $_POST['prinex_cennik_meta_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( $_POST['prinex_cennik_meta_nonce'], 'prinex_cennik_meta_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$folia   = isset( $_POST['_prinex_folia'] ) ? sanitize_key( $_POST['_prinex_folia'] ) : '';
	$rodzaj  = isset( $_POST['_prinex_rodzaj'] ) && in_array( $_POST['_prinex_rodzaj'], array( '3d', 'plaska' ), true )
		? $_POST['_prinex_rodzaj'] : '3d';
	$opt     = isset( $_POST['_prinex_optymalny'] ) ? sanitize_text_field( $_POST['_prinex_optymalny'] ) : '';

	update_post_meta( $post_id, '_prinex_folia', $folia );
	update_post_meta( $post_id, '_prinex_rodzaj', $rodzaj );
	update_post_meta( $post_id, '_prinex_optymalny', $opt );
} );

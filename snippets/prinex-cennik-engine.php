/**
 * PRINEX — Silnik cennika (Etap 1, II podejście)
 *
 * Funkcje kalkulacji cen. NIE zawiera filtrów WooCommerce ani JS —
 * to osobny snippet #25 (Etap 2, po akceptacji). Zakres: global.
 *
 * Dane cennika: opcja WP `prinex_cennik` (JSON).
 * Struktura: { global: {...}, folie: [{id, name, cost_per_m2, karta_3d, karta_plaska}] }
 */

if ( ! function_exists( 'prinex_cennik_default' ) ) :

function prinex_cennik_default() {
	return array(
		'global' => array(
			'druk'                => 20.0,
			'setup'               => 149.0,
			'zywica_uzycie'       => 0.175,
			'zywica_partia_kg'    => 120.0,
			'zywica_partia_cena'  => 9000.0,
			'arkusz_w'            => 210.0,
			'arkusz_h'            => 295.0,
			'pole_w'              => 190.0,
			'pole_h'              => 265.0,
			'zapas'               => 5.0,
		),
		'folie' => array(),
	);
}

endif;

if ( ! function_exists( 'prinex_cennik' ) ) :

/**
 * Zwraca tablicę konfiguracji cennika z opcji WP `prinex_cennik`.
 * Wynik jest cache'owany w statycznej zmiennej (raz na request).
 */
function prinex_cennik() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$json = get_option( 'prinex_cennik', '' );
	if ( empty( $json ) ) {
		$cache = prinex_cennik_default();
		return $cache;
	}
	$data  = json_decode( $json, true );
	$cache = is_array( $data ) ? $data : prinex_cennik_default();
	return $cache;
}

endif;

if ( ! function_exists( 'prinex_get_foil' ) ) :

/**
 * Zwraca dane folii po jej `id` lub null jeśli nie znaleziono.
 */
function prinex_get_foil( $foil_id ) {
	$cennik = prinex_cennik();
	if ( empty( $cennik['folie'] ) || ! is_array( $cennik['folie'] ) ) {
		return null;
	}
	foreach ( $cennik['folie'] as $f ) {
		if ( isset( $f['id'] ) && $f['id'] === $foil_id ) {
			return $f;
		}
	}
	return null;
}

endif;

if ( ! function_exists( 'prinex_sale_rate' ) ) :

/**
 * Zwraca stawkę sprzedaży (zł/cm²) dla danej folii, rodzaju karty i nakładu.
 * Przedziały nadpisują stawkę bazową jeśli nakład mieści się w przedziale.
 *
 * @param string $foil_id  ID folii (np. 'srebrna-szlifowana')
 * @param string $rodzaj   Rodzaj karty: '3d' lub 'plaska'
 * @param int    $qty      Nakład (szt.)
 * @return float           Stawka zł/cm²
 */
function prinex_sale_rate( $foil_id, $rodzaj, $qty ) {
	$foil = prinex_get_foil( $foil_id );
	if ( ! $foil ) {
		return 0.0;
	}
	$karta_key = 'karta_' . $rodzaj;
	if ( ! isset( $foil[ $karta_key ] ) || ! is_array( $foil[ $karta_key ] ) ) {
		return 0.0;
	}
	$karta    = $foil[ $karta_key ];
	$base     = isset( $karta['sale_base'] ) ? (float) $karta['sale_base'] : 0.0;
	$brackets = isset( $karta['brackets'] ) && is_array( $karta['brackets'] ) ? $karta['brackets'] : array();
	$qty      = (int) $qty;

	foreach ( $brackets as $b ) {
		$od = isset( $b['od'] ) && $b['od'] !== null && $b['od'] !== '' ? (int) $b['od'] : null;
		$do = isset( $b['do'] ) && $b['do'] !== null && $b['do'] !== '' ? (int) $b['do'] : null;
		if ( null === $od ) {
			continue;
		}
		if ( $qty >= $od && ( null === $do || $qty <= $do ) ) {
			return (float) $b['rate'];
		}
	}

	return $base;
}

endif;

if ( ! function_exists( 'prinex_sale_net' ) ) :

/**
 * Oblicza cenę netto dla wariantu.
 *
 * Formuła (§6 CLAUDE.md):
 *   spad = +3 mm na bok: cw = w+3, ch = h+3
 *   pole_cm2 = (cw/10) * (ch/10)
 *   stawka   = prinex_sale_rate(...)
 *   NETTO    = nakład * pole_cm2 * stawka + przygotowanie
 *
 * Wynik zaokrąglony do 2 miejsc (zachowanie walutowe).
 *
 * @param string    $foil_id  ID folii
 * @param string    $rodzaj   '3d' lub 'plaska'
 * @param float|int $w        Szerokość naklejki (mm, bez spadu)
 * @param float|int $h        Wysokość naklejki (mm, bez spadu)
 * @param int       $qty      Nakład (szt.)
 * @return float              Cena netto (zł), zaokrąglona do gr
 */
function prinex_sale_net( $foil_id, $rodzaj, $w, $h, $qty ) {
	$cennik   = prinex_cennik();
	$setup    = isset( $cennik['global']['setup'] ) ? (float) $cennik['global']['setup'] : 0.0;
	$cw       = (float) $w + 3.0;
	$ch       = (float) $h + 3.0;
	$pole_cm2 = ( $cw / 10.0 ) * ( $ch / 10.0 );
	$rate     = prinex_sale_rate( $foil_id, $rodzaj, (int) $qty );
	$net      = (int) $qty * $pole_cm2 * $rate + $setup;
	return round( $net, 2 );
}

endif;

if ( ! function_exists( 'prinex_rozmiar_to_dims' ) ) :

/**
 * Parsuje nazwę lub slug atrybutu pa_rozmiar do par wymiarów (mm).
 * Obsługiwane formaty: "50 × 80", "50x80", "50-x-80", "50×80".
 *
 * @param  string $term  Nazwa lub slug atrybutu pa_rozmiar
 * @return array         [float $w, float $h] w mm, lub [0, 0] przy błędzie
 */
function prinex_rozmiar_to_dims( $term ) {
	$str   = str_replace( array( ' ', "\xc3\x97", 'x' ), array( '', 'X', 'X' ), (string) $term );
	$str   = str_replace( array( '-X-', '-x-', '--' ), 'X', $str );
	$parts = explode( 'X', strtoupper( $str ), 2 );
	if ( count( $parts ) !== 2 ) {
		return array( 0.0, 0.0 );
	}
	return array( (float) $parts[0], (float) $parts[1] );
}

endif;

if ( ! function_exists( 'prinex_zywica_rate' ) ) :

/**
 * Zwraca stawkę kosztu żywicy (zł/cm²) — tylko do podglądu marży w adminie.
 * Nie wchodzi do ceny klienta.
 */
function prinex_zywica_rate() {
	$cennik = prinex_cennik();
	$g      = isset( $cennik['global'] ) ? $cennik['global'] : array();
	$uzycie = isset( $g['zywica_uzycie'] ) ? (float) $g['zywica_uzycie'] : 0.0;
	$kg     = isset( $g['zywica_partia_kg'] ) ? (float) $g['zywica_partia_kg'] : 0.0;
	$cena   = isset( $g['zywica_partia_cena'] ) ? (float) $g['zywica_partia_cena'] : 0.0;
	if ( $uzycie <= 0 ) {
		return 0.0;
	}
	$ile_cm2 = ( $kg * 1000.0 ) / $uzycie;
	return $ile_cm2 > 0 ? $cena / $ile_cm2 : 0.0;
}

endif;

if ( ! function_exists( 'prinex_sanitize_cennik' ) ) :

/**
 * Waliduje i sanityzuje tablicę danych cennika przed zapisem do opcji WP.
 *
 * @param  mixed $data  Dane wejściowe (tablica z formularza lub JSON)
 * @return array        Sanityzowana tablica gotowa do json_encode
 */
function prinex_sanitize_cennik( $data ) {
	if ( ! is_array( $data ) ) {
		return prinex_cennik_default();
	}

	$out = prinex_cennik_default();

	// Global
	if ( isset( $data['global'] ) && is_array( $data['global'] ) ) {
		$global_keys = array( 'druk', 'setup', 'zywica_uzycie', 'zywica_partia_kg',
			'zywica_partia_cena', 'arkusz_w', 'arkusz_h', 'pole_w', 'pole_h', 'zapas' );
		foreach ( $global_keys as $key ) {
			if ( isset( $data['global'][ $key ] ) ) {
				$out['global'][ $key ] = (float) $data['global'][ $key ];
			}
		}
	}

	// Folie
	if ( isset( $data['folie'] ) && is_array( $data['folie'] ) ) {
		$out['folie'] = array();
		foreach ( $data['folie'] as $f ) {
			if ( ! is_array( $f ) || empty( $f['id'] ) ) {
				continue;
			}
			$foil = array(
				'id'          => sanitize_key( $f['id'] ),
				'name'        => isset( $f['name'] ) ? sanitize_text_field( $f['name'] ) : '',
				'cost_per_m2' => isset( $f['cost_per_m2'] ) ? (float) $f['cost_per_m2'] : 0.0,
				'karta_3d'    => null,
				'karta_plaska' => null,
			);

			foreach ( array( 'karta_3d', 'karta_plaska' ) as $karta_key ) {
				if ( ! isset( $f[ $karta_key ] ) || ! is_array( $f[ $karta_key ] ) ) {
					continue;
				}
				$k = $f[ $karta_key ];
				$foil[ $karta_key ] = array(
					'sale_base' => (float) ( isset( $k['sale_base'] ) ? $k['sale_base'] : 0.0 ),
					'brackets'  => array(),
				);
				if ( ! empty( $k['brackets'] ) && is_array( $k['brackets'] ) ) {
					foreach ( $k['brackets'] as $b ) {
						$od = ( isset( $b['od'] ) && $b['od'] !== '' ) ? (int) $b['od'] : null;
						$do = ( isset( $b['do'] ) && $b['do'] !== '' ) ? (int) $b['do'] : null;
						$foil[ $karta_key ]['brackets'][] = array(
							'od'   => $od,
							'do'   => $do,
							'rate' => (float) ( isset( $b['rate'] ) ? $b['rate'] : 0.0 ),
						);
					}
				}
			}

			$out['folie'][] = $foil;
		}
	}

	return $out;
}

endif;

<?php
/**
 * PRINEX — Wspólna logika Osoba/Firma + NIP (single source).
 *
 * Wyniesione z snippetu #28 (checkout), reużywane przez checkout ORAZ książkę adresową
 * (Warstwa 2c). ŹRÓDŁO definicji pól + walidacji NIP w jednym miejscu.
 *
 * WAŻNE — zachowanie #28 NIEZMIENIONE: checkout używa walidacji DŁUGOŚCI (10 cyfr),
 * dokładnie jak dotąd. Walidacja sumy kontrolnej (prinex_nip_checksum_valid) jest
 * dostępna dla NOWYCH formularzy adresów (2c); #28 jej NIE używa, dopóki nie zostanie
 * to świadomie zatwierdzone (nietykalna warstwa).
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

/* ── NIP: utilsy ── */

/** Tylko cyfry. */
function prinex_nip_normalize( $raw ) {
	return preg_replace( '/[^0-9]/', '', (string) wp_unslash( $raw ) );
}

/** Walidacja DŁUGOŚCI — 10 cyfr (zachowanie checkoutu #28, bez zmian). */
function prinex_nip_length_valid( $nip ) {
	return 10 === strlen( prinex_nip_normalize( $nip ) );
}

/** Walidacja SUMY KONTROLNEJ NIP (dla nowych formularzy 2c). */
function prinex_nip_checksum_valid( $nip ) {
	$nip = prinex_nip_normalize( $nip );
	if ( 10 !== strlen( $nip ) ) {
		return false;
	}
	$weights = array( 6, 5, 7, 2, 3, 4, 5, 6, 7 );
	$sum     = 0;
	for ( $i = 0; $i < 9; $i++ ) {
		$sum += $weights[ $i ] * (int) $nip[ $i ];
	}
	$check = $sum % 11;
	if ( 10 === $check ) {
		return false;
	}
	return $check === (int) $nip[9];
}

/* ── Definicje pól: Nazwa firmy + NIP (firma-only) ── */

/**
 * Args pola "Nazwa firmy" (zapewnia obecność, klasa pxc-firma-only).
 * @param array $existing Istniejąca definicja pola (jeśli jest) — by nie gubić ustawień WC.
 */
function prinex_company_field_args( $existing = array() ) {
	$company             = is_array( $existing ) ? $existing : array();
	$company['label']    = 'Nazwa firmy';
	$company['required'] = false;
	$company['priority'] = 32;
	$company['class']    = array( 'form-row-wide', 'pxc-firma-only' );
	return $company;
}

/** Args pola "NIP" (nowe pole, firma-only). */
function prinex_nip_field_args() {
	return array(
		'label'       => 'NIP',
		'required'    => false,
		'class'       => array( 'form-row-wide', 'pxc-firma-only' ),
		'priority'    => 34,
		'maxlength'   => 15,
		'placeholder' => 'np. 1234563218',
	);
}

/**
 * Wstrzyknij pola firma+NIP do tablicy pól (grupa np. 'billing' / 'shipping').
 * Reużywane przez woocommerce_checkout_fields (#28) i formularze adresów (2c).
 */
function prinex_add_company_nip_fields( $fields, $group = 'billing' ) {
	if ( ! isset( $fields[ $group ] ) || ! is_array( $fields[ $group ] ) ) {
		return $fields;
	}
	$existing = $fields[ $group ][ $group . '_company' ] ?? array();
	$fields[ $group ][ $group . '_company' ] = prinex_company_field_args( $existing );
	$fields[ $group ][ $group . '_nip' ]     = prinex_nip_field_args();
	return $fields;
}

/* ── Walidacja firma+NIP ── */

/**
 * Walidacja firma+NIP wg trybu długości — IDENTYCZNA jak dotychczas w #28.
 * Używana przez checkout (nietykalna warstwa: bez zmiany zachowania).
 *
 * @param string         $type        'osoba' | 'firma'
 * @param string         $company     wartość nazwy firmy (surowa)
 * @param string         $nip         wartość NIP (surowa)
 * @param WP_Error|null  $errors      obiekt błędów (->add) lub null
 * @param array          $keys        klucze błędów ['nip'=>..., 'company'=>...]
 * @return array         lista komunikatów błędów (też zwracana, gdy $errors null)
 */
function prinex_validate_company_nip( $type, $company, $nip, $errors = null, $keys = array() ) {
	$nip_key     = $keys['nip'] ?? 'billing_nip';
	$company_key = $keys['company'] ?? 'billing_company';
	$msgs        = array();
	if ( 'firma' !== $type ) {
		return $msgs;
	}
	$nip_n = prinex_nip_normalize( $nip );
	if ( '' === $nip_n ) {
		$msgs[ $nip_key ] = 'Podaj NIP firmy.';
	} elseif ( ! prinex_nip_length_valid( $nip_n ) ) {
		$msgs[ $nip_key ] = 'NIP powinien mieć 10 cyfr.';
	}
	if ( '' === trim( (string) $company ) ) {
		$msgs[ $company_key ] = 'Podaj nazwę firmy.';
	}
	if ( $errors instanceof WP_Error ) {
		foreach ( $msgs as $code => $msg ) {
			$errors->add( $code, $msg );
		}
	}
	return $msgs;
}

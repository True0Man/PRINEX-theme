<?php
/**
 * PRINEX — eksport Code Snippet (mirror do wersjonowania, NIE zrodlo prawdy)
 *
 * ID snippetu (wp_snippets.id): 10
 * Tytul:                       PRINEX — SVG upload support
 * Typ:                         PHP (snippet typu code-snippets; jesli echo CSS/JS, zaznaczone nizej)
 * Scope:                       global — wykonuje sie WSZEDZIE (front-end + wp-admin)
 * Status:                      AKTYWNY
 *
 * UWAGA: zrodlem prawdy jest baza WP (wtyczka Code Snippets). Ten plik to
 * mirror do wersjonowania/code review. Edycja tego pliku NIE zmienia
 * dzialania strony — trzeba wkleic zmiany z powrotem do wp-admin > Code Snippets,
 * lub zaktualizowac wp_snippets.code (np. przez wp-cli/wp eval).
 */

// Zezwól na przesyłanie SVG/SVGZ do biblioteki mediów
add_filter( 'upload_mimes', function( $mimes ) {
    $mimes['svg']  = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
} );

// Napraw weryfikację typu pliku dla SVG (WP 5.1+)
add_filter( 'wp_check_filetype_and_ext', function( $data, $file, $filename, $mimes ) {
    $filetype = wp_check_filetype( $filename, $mimes );
    return array(
        'ext'             => $filetype['ext'],
        'type'            => $filetype['type'],
        'proper_filename' => $data['proper_filename'],
    );
}, 10, 4 );

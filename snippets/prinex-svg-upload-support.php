<?php
/**
 * PRINEX — eksport Code Snippet (mirror do wersjonowania, NIE zrodlo prawdy)
 *
 * ID snippetu (wp_snippets.id): 10
 * Tytul:                       PRINEX — SVG upload support
 * Typ:                         PHP (snippet typu code-snippets; jesli echo CSS/JS, zaznaczone nizej)
 * Scope:                       front-end — wykonuje sie WYLACZNIE na froncie (nie w wp-admin)
 * Status:                      AKTYWNY
 *
 * UWAGA: zrodlem prawdy jest baza WP (wtyczka Code Snippets). Ten plik to
 * mirror do wersjonowania/code review. Edycja tego pliku NIE zmienia
 * dzialania strony — trzeba wkleic zmiany z powrotem do wp-admin > Code Snippets,
 * lub zaktualizowac wp_snippets.code (np. przez wp-cli/wp eval).
 */

// Zezwól na przesyłanie SVG do biblioteki mediów WP
add_filter( "upload_mimes", function( \ ) {
    \["svg"]  = "image/svg+xml";
    \["svgz"] = "image/svg+xml";
    return \;
} );

// Napraw weryfikację SVG (WP 5.1+)
add_filter( "wp_check_filetype_and_ext", function( \, \, \, \ ) {
    \ = wp_check_filetype( \, \ );
    return [
        "ext"             => \["ext"],
        "type"            => \["type"],
        "proper_filename" => \["proper_filename"],
    ];
}, 10, 4 );
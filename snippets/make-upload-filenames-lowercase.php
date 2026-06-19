<?php
/**
 * PRINEX — eksport Code Snippet (mirror do wersjonowania, NIE zrodlo prawdy)
 *
 * ID snippetu (wp_snippets.id): 1
 * Tytul:                       Make upload filenames lowercase
 * Typ:                         PHP (snippet typu code-snippets; jesli echo CSS/JS, zaznaczone nizej)
 * Scope:                       global — wykonuje sie WSZEDZIE (front-end + wp-admin)
 * Status:                      NIEAKTYWNY
 *
 * UWAGA: zrodlem prawdy jest baza WP (wtyczka Code Snippets). Ten plik to
 * mirror do wersjonowania/code review. Edycja tego pliku NIE zmienia
 * dzialania strony — trzeba wkleic zmiany z powrotem do wp-admin > Code Snippets,
 * lub zaktualizowac wp_snippets.code (np. przez wp-cli/wp eval).
 */

add_filter( 'sanitize_file_name', 'mb_strtolower' );
<?php
/**
 * PRINEX — eksport Code Snippet (mirror do wersjonowania, NIE zrodlo prawdy)
 *
 * ID snippetu (wp_snippets.id): 7
 * Tytul:                       PRINEX - Dodaj cenę i rabat do swatchów Nakładu
 * Typ:                         PHP (snippet typu code-snippets; jesli echo CSS/JS, zaznaczone nizej)
 * Scope:                       front-end — wykonuje sie WYLACZNIE na froncie (nie w wp-admin)
 * Status:                      AKTYWNY
 *
 * UWAGA: zrodlem prawdy jest baza WP (wtyczka Code Snippets). Ten plik to
 * mirror do wersjonowania/code review. Edycja tego pliku NIE zmienia
 * dzialania strony — trzeba wkleic zmiany z powrotem do wp-admin > Code Snippets,
 * lub zaktualizowac wp_snippets.code (np. przez wp-cli/wp eval).
 */

add_action('wp_footer', 'prinex_enhance_naklad_swatches', 100);
function prinex_enhance_naklad_swatches() {
    if (!is_product()) return;
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Dane markerów (cena + rabat) per wartość nakładu
        // SLUG wartości atrybutu (z bazy WP) → { price: "...", discount: "..." }
        var nakladData = {
            '100-szt':  { price: '349,00 zł', discount: '' },
            '250-szt':  { price: '379,00 zł', discount: 'Taniej o 57%' },
            '500-szt':  { price: '389,00 zł', discount: 'Taniej o 78%' },
            '1000-szt': { price: '399,00 zł', discount: 'Taniej o 89%' }
        };

        function enhanceNakladSwatches() {
            // Znajdź wszystkie listy swatchy dla atrybutu Nakład
            $('ul.variable-items-wrapper[data-attribute_name="attribute_pa_naklad"] li.variable-item').each(function() {
                var $item = $(this);
                
                // Nie modyfikuj jeśli już zmodyfikowane
                if ($item.find('.prinex-swatch-enhanced').length) return;
                
                var slug = $item.data('value');
                if (!slug) return;
                
                var data = nakladData[slug];
                if (!data) return;
                
                var nazwa = $item.find('.variable-item-span').text().trim();
                
                // Zbuduj nową zawartość: radio + nazwa + cena + rabat
                var newHtml = '<div class="prinex-swatch-enhanced">' +
                    '<span class="prinex-swatch-radio"></span>' +
                    '<span class="prinex-swatch-name">' + nazwa + '</span>' +
                    '<span class="prinex-swatch-price">' + data.price + '</span>' +
                    '<span class="prinex-swatch-discount">' + data.discount + '</span>' +
                    '</div>';
                
                // Podmień zawartość li
                $item.find('.variable-item-span').hide();
                $item.append(newHtml);
            });
        }

        // Pierwsze wykonanie
        setTimeout(enhanceNakladSwatches, 100);

        // Ponowne wykonanie gdy WooCommerce odświeży warianty
        $('.variations_form').on('woocommerce_update_variation_values woocommerce_variation_has_changed', function() {
            setTimeout(enhanceNakladSwatches, 100);
        });
    });
    </script>
    <?php
}
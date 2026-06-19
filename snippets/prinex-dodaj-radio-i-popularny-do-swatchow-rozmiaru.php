<?php
/**
 * PRINEX — eksport Code Snippet (mirror do wersjonowania, NIE zrodlo prawdy)
 *
 * ID snippetu (wp_snippets.id): 8
 * Tytul:                       PRINEX - Dodaj radio i POPULARNY do swatchów Rozmiaru
 * Typ:                         PHP (snippet typu code-snippets; jesli echo CSS/JS, zaznaczone nizej)
 * Scope:                       front-end — wykonuje sie WYLACZNIE na froncie (nie w wp-admin)
 * Status:                      NIEAKTYWNY
 *
 * UWAGA: zrodlem prawdy jest baza WP (wtyczka Code Snippets). Ten plik to
 * mirror do wersjonowania/code review. Edycja tego pliku NIE zmienia
 * dzialania strony — trzeba wkleic zmiany z powrotem do wp-admin > Code Snippets,
 * lub zaktualizowac wp_snippets.code (np. przez wp-cli/wp eval).
 */

add_action('wp_footer', 'prinex_enhance_rozmiar_swatches', 101);
function prinex_enhance_rozmiar_swatches() {
    if (!is_product()) return;
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Markery dla atrybutu Rozmiar
        var rozmiarData = {
            '30-60':  { label: '' },
            '50-80':  { label: 'POPULARNY' },
            '60-100': { label: '' },
            '80-120': { label: '' }
        };

        function enhanceRozmiarSwatches() {
            $('ul.variable-items-wrapper[data-attribute_name="attribute_pa_rozmiar"] li.variable-item').each(function() {
                var $item = $(this);
                
                if ($item.find('.prinex-rozmiar-enhanced').length) return;
                
                var slug = $item.data('value');
                if (!slug) return;
                
                var data = rozmiarData[slug];
                if (!data) data = { label: '' };
                
                var nazwa = $item.find('.variable-item-span').text().trim();
                
                var labelHtml = data.label ? 
                    '<span class="prinex-rozmiar-tag">' + data.label + '</span>' : 
                    '<span class="prinex-rozmiar-tag-empty"></span>';
                
                var newHtml = '<div class="prinex-rozmiar-enhanced">' +
                    '<span class="prinex-swatch-radio"></span>' +
                    '<span class="prinex-swatch-name">' + nazwa + '</span>' +
                    labelHtml +
                    '</div>';
                
                $item.find('.variable-item-span').hide();
                $item.append(newHtml);
            });
        }

        setTimeout(enhanceRozmiarSwatches, 100);

        $('.variations_form').on('woocommerce_update_variation_values woocommerce_variation_has_changed', function() {
            setTimeout(enhanceRozmiarSwatches, 100);
        });
    });
    </script>
    <?php
}
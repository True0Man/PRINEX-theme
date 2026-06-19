<?php
/**
 * PRINEX — eksport Code Snippet (mirror do wersjonowania, NIE zrodlo prawdy)
 *
 * ID snippetu (wp_snippets.id): 6
 * Tytul:                       PRINEX - Radio buttons zamiast dropdownów dla wariantów
 * Typ:                         PHP (snippet typu code-snippets; jesli echo CSS/JS, zaznaczone nizej)
 * Scope:                       front-end — wykonuje sie WYLACZNIE na froncie (nie w wp-admin)
 * Status:                      NIEAKTYWNY
 *
 * UWAGA: zrodlem prawdy jest baza WP (wtyczka Code Snippets). Ten plik to
 * mirror do wersjonowania/code review. Edycja tego pliku NIE zmienia
 * dzialania strony — trzeba wkleic zmiany z powrotem do wp-admin > Code Snippets,
 * lub zaktualizowac wp_snippets.code (np. przez wp-cli/wp eval).
 */

add_filter('woocommerce_dropdown_variation_attribute_options_html', 'prinex_radio_buttons_for_variations', 20, 2);
function prinex_radio_buttons_for_variations($html, $args) {
    $args = wp_parse_args(apply_filters('woocommerce_dropdown_variation_attribute_options_args', $args), array(
        'options'          => false,
        'attribute'        => false,
        'product'          => false,
        'selected'         => false,
        'name'             => '',
        'id'               => '',
        'class'            => '',
        'show_option_none' => false,
    ));

    if (empty($args['id'])) {
        $args['id'] = sanitize_title($args['attribute']);
    }
    if (empty($args['name'])) {
        $args['name'] = 'attribute_' . sanitize_title($args['attribute']);
    }

    $options = $args['options'];
    $product = $args['product'];
    $attribute = $args['attribute'];
    $name = $args['name'];
    $id = $args['id'];

    if (empty($options) && !empty($product) && !empty($attribute)) {
        $attributes = $product->get_variation_attributes();
        $options    = $attributes[$attribute];
    }

    // Zachowaj oryginalny select (niewidoczny) + dodaj radio-buttony
    $html = '<select id="' . esc_attr($id) . '" class="prinex-original-select" name="' . esc_attr($name) . '" data-attribute_name="attribute_' . esc_attr(sanitize_title($attribute)) . '" data-show_option_none="no" style="display:none !important;">';
    
    if (!empty($options)) {
        if ($product && taxonomy_exists($attribute)) {
            $terms = wc_get_product_terms($product->get_id(), $attribute, array('fields' => 'all'));
            foreach ($terms as $term) {
                if (in_array($term->slug, $options, true)) {
                    $html .= '<option value="' . esc_attr($term->slug) . '" ' . selected(sanitize_title($args['selected']), $term->slug, false) . '>' . esc_html(apply_filters('woocommerce_variation_option_name', $term->name, $term, $attribute, $product)) . '</option>';
                }
            }
        } else {
            foreach ($options as $option) {
                $html .= '<option value="' . esc_attr(sanitize_title($option)) . '" ' . selected(sanitize_title($args['selected']), sanitize_title($option), false) . '>' . esc_html(apply_filters('woocommerce_variation_option_name', $option, null, $attribute, $product)) . '</option>';
            }
        }
    }
    
    $html .= '</select>';

    // Teraz dodaj radio-buttony, które będą sterować selectem
    $html .= '<div class="prinex-radio-variations" data-target-select="' . esc_attr($id) . '">';
    
    if (!empty($options)) {
        if ($product && taxonomy_exists($attribute)) {
            $terms = wc_get_product_terms($product->get_id(), $attribute, array('fields' => 'all'));
            foreach ($terms as $term) {
                if (in_array($term->slug, $options, true)) {
                    $checked = sanitize_title($args['selected']) === $term->slug ? 'checked="checked"' : '';
                    $html .= '<label class="prinex-radio-option">';
                    $html .= '<input type="radio" name="prinex_radio_' . esc_attr($id) . '" value="' . esc_attr($term->slug) . '" ' . $checked . ' />';
                    $html .= '<span class="prinex-radio-label">' . esc_html(apply_filters('woocommerce_variation_option_name', $term->name, $term, $attribute, $product)) . '</span>';
                    $html .= '</label>';
                }
            }
        } else {
            foreach ($options as $option) {
                $val = sanitize_title($option);
                $checked = sanitize_title($args['selected']) === $val ? 'checked="checked"' : '';
                $html .= '<label class="prinex-radio-option">';
                $html .= '<input type="radio" name="prinex_radio_' . esc_attr($id) . '" value="' . esc_attr($val) . '" ' . $checked . ' />';
                $html .= '<span class="prinex-radio-label">' . esc_html(apply_filters('woocommerce_variation_option_name', $option, null, $attribute, $product)) . '</span>';
                $html .= '</label>';
            }
        }
    }
    
    $html .= '</div>';

    return $html;
}

// JavaScript - sync radio z hidden select
add_action('wp_footer', 'prinex_radio_variations_js', 99);
function prinex_radio_variations_js() {
    if (!is_product()) return;
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Czekaj aż WooCommerce zainicjalizuje formę wariantów
        setTimeout(function() {
            // Initial sync - z selecta (gdzie WooCommerce ustawił domyślny) do radio
            $('.prinex-original-select').each(function() {
                var $select = $(this);
                var selectId = $select.attr('id');
                var currentValue = $select.val();
                
                if (currentValue) {
                    var $radio = $('.prinex-radio-variations[data-target-select="' + selectId + '"] input[value="' + currentValue + '"]');
                    $radio.prop('checked', true);
                }
            });
        }, 100);
        
        // Klik radio -> ustaw wartość w hidden select i wywołaj change
        $(document).on('change', '.prinex-radio-variations input[type="radio"]', function() {
            var $radio = $(this);
            var selectId = $radio.closest('.prinex-radio-variations').data('target-select');
            var value = $radio.val();
            
            var $select = $('#' + selectId);
            $select.val(value).trigger('change');
        });
        
        // Gdy WooCommerce zmieni wybór wariantu (np. po znalezieniu match) - update radio
        $('.variations_form').on('woocommerce_variation_has_changed', function() {
            $('.prinex-original-select').each(function() {
                var $select = $(this);
                var selectId = $select.attr('id');
                var currentValue = $select.val();
                
                $('.prinex-radio-variations[data-target-select="' + selectId + '"] input[type="radio"]').prop('checked', false);
                if (currentValue) {
                    $('.prinex-radio-variations[data-target-select="' + selectId + '"] input[value="' + currentValue + '"]').prop('checked', true);
                }
            });
        });
    });
    </script>
    <?php
}
<?php
/**
 * PRINEX — szablon archiwum kategorii "Naklejki 3D Premium"
 * Dotyczy WYŁĄCZNIE tej kategorii (WooCommerce template hierarchy:
 * taxonomy-product_cat-{slug}.php ma priorytet nad taxonomy-product_cat.php).
 * Siatka produktów jest dynamiczna (natywna petla WP/WooCommerce) — sekcja SEO
 * pod siatka pochodzi z pola "Opis" tej kategorii (Produkty > Kategorie).
 */

defined( 'ABSPATH' ) || exit;

// Wlasny breadcrumb (Oferta / Naklejki 3D Premium) zamiast domyslnego WC
// (Strona glowna / Naklejki 3D Premium), zeby nie bylo dwoch okruszkow.
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

get_header();

do_action( 'woocommerce_before_main_content' );

$prinex_term       = get_queried_object();
$prinex_shop_url    = esc_url( wc_get_page_permalink( 'shop' ) );
$prinex_cat_name    = single_term_title( '', false );
?>

<section class="cat-wrap">
  <div class="container">

    <nav class="breadcrumb">
      <a href="<?php echo $prinex_shop_url; ?>">Oferta</a>
      <span class="sep">/</span>
      <span class="cur"><?php echo esc_html( $prinex_cat_name ); ?></span>
    </nav>

    <div class="cat-head">
      <div class="sig"></div>
      <h1><?php echo esc_html( $prinex_cat_name ); ?></h1>
      <p>Wypukłe naklejki 3D zalewane dwuskładnikową żywicą poliuretanową. Trwałe, odporne na UV, idealne do oznaczania produktów Twojej marki.</p>
    </div>

    <div class="cat-toolbar">
      <div class="cat-filters" id="catFilters">
        <label class="fchk on" data-filter="all"><span class="box"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"></path></svg></span>Wszystkie</label>
        <label class="fchk" data-filter="metal"><span class="box"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"></path></svg></span>Metalizowane</label>
      </div>
    </div>

    <div class="tile-grid">
      <?php
      if ( have_posts() ) :
        while ( have_posts() ) :
          the_post();
          global $product;
          if ( ! $product instanceof WC_Product || ! $product->is_visible() ) {
            continue;
          }

          $prinex_is_new     = ( time() - get_post_time( 'U', true ) ) < 14 * DAY_IN_SECONDS;
          $prinex_is_popular = $product->is_featured();
          $prinex_has_metal  = has_term( 'metalizowane', 'product_tag', get_the_ID() );
          $prinex_data_cat   = $prinex_has_metal ? 'metal' : '';

          $prinex_desc = wp_strip_all_tags( $product->get_short_description() );
          $prinex_desc = $prinex_desc ? wp_trim_words( $prinex_desc, 8, '…' ) : '';

          // Krotka nazwa kafla (np. "Srebrna Szlifowana" zamiast "Naklejki 3D Premium
          // Srebrna Szlifowana") — z dedykowanego pola, z fallbackiem na pelny tytul
          // gdy ktos doda nowy produkt i jeszcze nie ustawi skrotu.
          $prinex_short_name = get_post_meta( get_the_ID(), '_prinex_short_name', true );
          $prinex_tile_name  = $prinex_short_name ? $prinex_short_name : get_the_title();
          ?>
          <a href="<?php the_permalink(); ?>" class="product" data-cat="<?php echo esc_attr( $prinex_data_cat ); ?>">
            <div class="product-img">
              <?php if ( $prinex_is_new ) : ?>
                <span class="tile-badge new">Nowość</span>
              <?php elseif ( $prinex_is_popular ) : ?>
                <span class="tile-badge popular">Popularny</span>
              <?php endif; ?>
              <?php echo $product->get_image( 'woocommerce_thumbnail' ); ?>
            </div>
            <div class="product-name"><?php echo esc_html( $prinex_tile_name ); ?></div>
            <?php if ( $prinex_desc ) : ?>
              <div class="product-desc"><?php echo esc_html( $prinex_desc ); ?></div>
            <?php endif; ?>
          </a>
          <?php
        endwhile;
      else :
        ?>
        <p><?php esc_html_e( 'Brak produktów w tej kategorii.', 'generatepress-child' ); ?></p>
        <?php
      endif;

      // 8. kafel "Indywidualna" — statyczny placeholder (zgodnie z wzorcem),
      // bo produkt jest swiadomie odlozony do etapu 2 (wymaga wtyczki
      // kalkulatora cen — patrz CLAUDE.md). Brak strony "Wycena indywidualna"
      // w tej chwili, wiec link prowadzi na "#" do czasu jej powstania.
      ?>
      <a href="#" class="product">
        <div class="product-img">
          <?php echo get_the_post_thumbnail( 17, 'woocommerce_thumbnail' ); ?>
        </div>
        <div class="product-name">Indywidualna</div>
        <div class="product-desc">Twój rozmiar, kształt i nakład</div>
      </a>
    </div>

    <?php do_action( 'woocommerce_after_shop_loop' ); ?>

  </div>
</section>

<?php
// Sekcja SEO (Czym sa.../Dlaczego zywica/branze/folie/Jak zamowic/trust) —
// pelna szerokosc (wlasne .band w markupie), tresc z pola "Opis" kategorii,
// edytowalna w wp-admin > Produkty > Kategorie > Naklejki 3D Premium.
// Echo bez wp_kses_post(): tresc zawiera <svg>/<path>, ktorych kses_post
// nie przepuszcza. Pole "Opis" edytuje tylko admin/shop manager (unfiltered_html),
// czyli to ten sam poziom zaufania co post_content stron (np. strona glowna).
$prinex_seo_html = get_term_field( 'description', $prinex_term->term_id, 'product_cat', 'raw' );
if ( $prinex_seo_html && ! is_wp_error( $prinex_seo_html ) ) {
  echo $prinex_seo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

do_action( 'woocommerce_after_main_content' );
do_action( 'woocommerce_sidebar' );
get_footer();

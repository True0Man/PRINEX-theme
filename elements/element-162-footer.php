<?php
$logo = file_get_contents( get_stylesheet_directory() . '/assets/prinex-logo.svg' );
$home = esc_url( home_url('/') );
$year = date('Y');
?>
<footer class="prinex-footer">
  <div class="prinex-container">
    <div class="prinex-footer-top">

      <!-- Kolumna 1 — Brand -->
      <div class="prinex-footer-brand">
        <a href="<?php echo $home; ?>" aria-label="PRINEX — strona główna">
          <?php echo $logo; ?>
        </a>
        <div class="prinex-footer-sig"></div>
        <p>Producent wypukłych naklejek 3D Premium zalewanych żywicą poliuretanową dla klientów biznesowych.</p>
      </div>

      <!-- Kolumna 2 — Oferta -->
      <div class="prinex-footer-col">
        <h5>Oferta</h5>
        <a href="<?php echo $home; ?>sklep">Naklejki 3D Premium</a>
        <a href="<?php echo $home; ?>produkt/naklejki-3d-premium-srebrna-szlifowana">Srebrne szlifowane</a>
        <a href="<?php echo $home; ?>produkt/naklejki-3d-premium-srebrna-blask">Srebrne błysk</a>
        <a href="<?php echo $home; ?>produkt/naklejki-3d-premium-zlota-szlifowana">Złote szlifowane</a>
        <a href="<?php echo $home; ?>produkt/naklejki-3d-premium-biala">Białe</a>
        <a href="<?php echo $home; ?>produkt/naklejki-3d-econo">Econo</a>
      </div>

      <!-- Kolumna 3 — Pomoc -->
      <div class="prinex-footer-col">
        <h5>Pomoc</h5>
        <a href="<?php echo $home; ?>faq">FAQ</a>
        <a href="<?php echo $home; ?>jak-zamawiać">Jak zamawiać?</a>
        <a href="<?php echo $home; ?>przygotowanie-pliku">Przygotowanie pliku</a>
        <a href="<?php echo $home; ?>kontakt">Kontakt</a>
      </div>

      <!-- Kolumna 4 — Informacje -->
      <div class="prinex-footer-col">
        <h5>Informacje</h5>
        <a href="<?php echo $home; ?>regulamin">Regulamin</a>
        <a href="<?php echo $home; ?>polityka-prywatnosci">Polityka prywatności</a>
        <a href="<?php echo $home; ?>polityka-zwrotow">Polityka zwrotów</a>
        <a href="<?php echo $home; ?>o-nas">O nas</a>
      </div>

      <!-- Kolumna 5 — Kontakt -->
      <div class="prinex-footer-col">
        <h5>Kontakt</h5>
        <a href="mailto:biuro@prinex.com.pl">biuro@prinex.com.pl</a>
        <a href="<?php echo $home; ?>kontakt">Formularz kontaktowy</a>
        <a href="#">Pon–Pt 8:00–16:00</a>
      </div>

    </div><!-- /.prinex-footer-top -->
    <div class="prinex-footer-bottom">
      <p>© <?php echo $year; ?> PRINEX. Twoje naklejki 3D Premium.</p>
      <div class="prinex-footer-bottom-right">
        <a href="<?php echo $home; ?>regulamin">Regulamin</a>
        <a href="<?php echo $home; ?>polityka-prywatnosci">Polityka prywatności</a>
        <a href="<?php echo $home; ?>polityka-zwrotow">Polityka zwrotów</a>
        <a href="#cmplz-manage-consent">Ustawienia cookies</a>
      </div>
    </div>
  </div>
</footer>

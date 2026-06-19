<?php
/**
 * GeneratePress Child — PRINEX
 * functions.php
 */

// Załaduj styl motywu rodzica
add_action( 'wp_enqueue_scripts', 'prinex_child_enqueue_styles' );
function prinex_child_enqueue_styles() {
	wp_enqueue_style(
		'generatepress-parent',
		get_template_directory_uri() . '/style.css'
	);
}

// Rejestruj paletę kolorów PRINEX w edytorze WordPress / GenerateBlocks
add_action( 'after_setup_theme', 'prinex_editor_color_palette' );
function prinex_editor_color_palette() {
	add_theme_support( 'editor-color-palette', array(
		array(
			'name'  => 'Granat PRINEX',
			'slug'  => 'prinex-navy',
			'color' => '#0B457D',
		),
		array(
			'name'  => 'Zieleń PRINEX',
			'slug'  => 'prinex-green',
			'color' => '#78B833',
		),
		array(
			'name'  => 'Tło jasne PRINEX',
			'slug'  => 'prinex-bg',
			'color' => '#E8ECEF',
		),
	) );
}

<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function infine_child_theme_enqueue_styles() {
	wp_enqueue_style( 'infine-child-style', get_stylesheet_directory_uri() . '/style.css', array( 'infine-style' ), INFINE_THEME_VERSION ); 
}
add_action( 'wp_enqueue_scripts', 'infine_child_theme_enqueue_styles', 999 );

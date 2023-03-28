<?php

add_shortcode( 'bogo', 'bogo_shortcode_callback' );

function bogo_shortcode_callback( $atts, $content, $shortcode_tag ) {
	$atts = shortcode_atts( array(
	), $atts );

	return bogo_language_switcher();
}

<?php

add_shortcode( 'bogo', 'bogo_shortcode_callback' );

function bogo_shortcode_callback( $atts, $content, $shortcode_tag ) {
	$atts = shortcode_atts( array(
		'type' => 'language_switcher',
	), $atts );

	if ( 'language_switcher' === $atts['type'] ) {
		return bogo_language_switcher( 'echo=0' );
	}

	if ( 'language_suggestion' === $atts['type'] ) {
		return bogo_language_suggestion( 'echo=0' );
	}
}

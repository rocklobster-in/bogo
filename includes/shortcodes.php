<?php

add_shortcode( 'bogo', 'bogo_shortcode_callback' );

function bogo_shortcode_callback( $atts, $content, $shortcode_tag ) {
	$atts = shortcode_atts( array(
		'view' => 'list',
	), $atts );

	switch ( $atts['view'] ) {
		case 'suggestion':
			return bogo_language_suggestion( 'echo=0' );
		case 'list':
		default:
			return bogo_language_switcher( 'echo=0' );
	}
}

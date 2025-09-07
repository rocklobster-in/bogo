<?php

add_filter( 'wp_kses_allowed_html', 'bogo_kses_allowed_html', 10, 2 );

/**
 * Callback function dedicated for the wp_kses_allowed_html filter hook.
 */
function bogo_kses_allowed_html( $html, $context ) {
	// Support the `hreflang` attribute.
	if (
		! empty( $html['a']['href'] ) and
		! isset( $html['a']['hreflang'] )
	) {
		$html['a']['hreflang'] = true;
	}

	return $html;
}

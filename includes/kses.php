<?php

add_filter( 'wp_kses_allowed_html', 'bogo_kses_allowed_html', 10, 2 );

/**
 * Callback function dedicated for the wp_kses_allowed_html filter hook.
 */
function bogo_kses_allowed_html( $html, $context ) {
	global $allowedposttags;

	if ( 'bogo_form_inside' === $context ) {
		$html = array_merge( $allowedposttags, array(
			'button' => array(
				'disabled' => true,
				'name' => true,
				'type' => true,
				'value' => true,
			),
			'datalist' => array(),
			'fieldset' => array(
				'disabled' => true,
				'name' => true,
			),
			'input' => array(
				'accept' => true,
				'checked' => true,
				'disabled' => true,
				'list' => true,
				'max' => true,
				'maxlength' => true,
				'min' => true,
				'minlength' => true,
				'multiple' => true,
				'name' => true,
				'pattern' => true,
				'placeholder' => true,
				'readonly' => true,
				'required' => true,
				'step' => true,
				'type' => true,
				'value' => true,
			),
			'label' => array(
				'for' => true,
			),
			'legend' => array(),
			'option' => array(
				'disabled' => true,
				'label' => true,
				'selected' => true,
				'value' => true,
			),
			'output' => array(
				'for' => true,
				'name' => true,
			),
			'select' => array(
				'disabled' => true,
				'multiple' => true,
				'name' => true,
				'required' => true,
			),
			'textarea' => array(
				'cols' => true,
				'rows' => true,
				'disabled' => true,
				'maxlength' => true,
				'minlength' => true,
				'name' => true,
				'placeholder' => true,
				'readonly' => true,
				'required' => true,
			),
		) );
	}

	// Support the `hreflang` attribute.
	if (
		! empty( $html['a']['href'] ) and
		! isset( $html['a']['hreflang'] )
	) {
		$html['a']['hreflang'] = true;
	}

	if (
		! empty( $html['link']['href'] ) and
		! isset( $html['link']['hreflang'] )
	) {
		$html['link']['hreflang'] = true;
	}

	$html = array_map( static function ( $atts ) {
		static $global_attributes = array(
			'class' => true,
			'data-*' => true,
			'id' => true,
			'lang' => true,
			'role' => true,
			'title' => true,
		);

		if ( is_array( $atts ) ) {
			$atts = array_merge( $global_attributes, $atts );
		}

		return $atts;
	}, $html );

	return $html;
}

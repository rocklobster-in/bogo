<?php

/**
 * Returns a formatted string of HTML attributes.
 *
 * @param array $atts Associative array of attribute name and value pairs.
 * @return string Formatted HTML attributes.
 */
function bogo_format_atts( $atts ) {
	$atts_filtered = array();

	foreach ( $atts as $name => $value ) {
		$name = strtolower( trim( $name ) );

		if ( ! preg_match( '/^[a-z_:][a-z_:.0-9-]*$/', $name ) ) {
			continue;
		}

		static $boolean_attributes = array(
			'checked', 'disabled', 'multiple', 'readonly', 'required', 'selected',
		);

		if ( in_array( $name, $boolean_attributes ) and '' === $value ) {
			$value = false;
		}

		if ( is_numeric( $value ) ) {
			$value = (string) $value;
		}

		if ( null === $value or false === $value ) {
			unset( $atts_filtered[$name] );
		} elseif ( true === $value ) {
			$atts_filtered[$name] = $name; // boolean attribute
		} elseif ( is_string( $value ) ) {
			$atts_filtered[$name] = trim( $value );
		}
	}

	$output = '';

	foreach ( $atts_filtered as $name => $value ) {
		$output .= sprintf( ' %1$s="%2$s"', $name, esc_attr( $value ) );
	}

	return trim( $output );
}

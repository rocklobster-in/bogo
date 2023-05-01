<?php

function bogo_get_prop( $prop ) {
	$option = get_option( 'bogo' );

	if ( ! is_array( $option ) ) {
		$option = array();
	}

	return isset( $option[$prop] ) ? $option[$prop] : '';
}

function bogo_set_prop( $prop, $value ) {
	$option = get_option( 'bogo' );

	if ( ! is_array( $option ) ) {
		$option = array();
	}

	$option[$prop] = $value;

	update_option( 'bogo', $option );
}

function bogo_delete_prop( $prop ) {
	$option = get_option( 'bogo' );

	if ( ! is_array( $option ) ) {
		$option = array();
	}

	if ( isset( $option[$prop] ) ) {
		unset( $option[$prop] );
	}

	update_option( 'bogo', $option );
}

function bogo_plugin_url( $path = '' ) {
	$url = plugins_url( $path, BOGO_PLUGIN );

	if ( is_ssl() and 'http:' == substr( $url, 0, 5 ) ) {
		$url = 'https:' . substr( $url, 5 );
	}

	return $url;
}

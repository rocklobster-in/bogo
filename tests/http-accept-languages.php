<?php
/**
 * Regression checks for accepted browser languages.
 *
 * Run with: php tests/http-accept-languages.php
 * This parser has no WordPress dependencies, so no test framework is required.
 */

require_once dirname( __DIR__ ) . '/includes/language-functions.php';

( static function () {
	$original = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
	$cases = array(
		'zero quality is excluded' => array(
			'en-GB,sv-SE;q=0',
			array( 'en_GB' ),
		),
		'decimal zero quality is excluded' => array(
			'en;q=0.0,sv;q=0.00,de;q=0.000,fr;q=0.5',
			array( 'fr' ),
		),
		'all languages rejected' => array(
			'en-GB;q=0,sv-SE;q=0',
			array(),
		),
		'missing header' => array(
			null,
			array(),
		),
		'empty header' => array(
			'',
			array(),
		),
		'positive qualities retain their ordering and normalisation' => array(
			'SV-se;q=0.5,en-GB,de;q=0.8',
			array( 'en_GB', 'de', 'sv_SE' ),
		),
		'the smallest valid positive quality is retained' => array(
			'en-GB,sv-SE;q=0.001',
			array( 'en_GB', 'sv_SE' ),
		),
		'a final duplicate with zero quality is excluded' => array(
			'en-GB,sv-SE;q=0.5,sv-SE;q=0',
			array( 'en_GB' ),
		),
		'a final duplicate with positive quality is retained' => array(
			'en-GB,sv-SE;q=0,sv-SE;q=0.5',
			array( 'en_GB', 'sv_SE' ),
		),
	);
	$failures = array();

	try {
		foreach ( $cases as $label => list( $header, $expected ) ) {
			if ( null === $header ) {
				unset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
			} else {
				$_SERVER['HTTP_ACCEPT_LANGUAGE'] = $header;
			}

			$actual = bogo_http_accept_languages();

			if ( $actual !== $expected ) {
				$failures[] = $label;
				printf( "FAIL %s: expected %s; got %s\n", $label, json_encode( $expected ), json_encode( $actual ) );
			} else {
				printf( "PASS %s\n", $label );
			}
		}
	} finally {
		if ( null === $original ) {
			unset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
		} else {
			$_SERVER['HTTP_ACCEPT_LANGUAGE'] = $original;
		}
	}

	if ( $failures ) {
		throw new RuntimeException( implode( '; ', $failures ) );
	}
} )();

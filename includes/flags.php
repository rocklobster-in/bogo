<?php

function bogo_get_flag( $locale ) {
	$locale = explode( '_', $locale );
	$locale = array_slice( $locale, 0, 2 ); // de_DE_formal => de_DE
	$locale = implode( '_', $locale );

	$special_cases = array(
		'ca' => 'catalonia',
		'gd' => 'scotland',
		'cy' => 'wales',
		'am' => 'et',
		'az' => 'az',
		'bel' => 'by',
		'bs' => 'ba',
		'dzo' => 'bt',
		'el' => 'gr',
		'et' => 'ee',
		'fi' => 'fi',
		'ga' => 'ie',
		'hr' => 'hr',
		'ht' => 'ht',
		'hy' => 'am',
		'ja' => 'jp',
		'kk' => 'kz',
		'km' => 'kh',
		'lo' => 'la',
		'lv' => 'lv',
		'mn' => 'mn',
		'sq' => 'al',
		'tg' => 'tj',
		'th' => 'th',
		'tl' => 'ph',
		'uk' => 'ua',
		'vi' => 'vn',
	);

	if ( isset( $special_cases[$locale] ) ) {
		$file = $special_cases[$locale] . '.png';
	} elseif ( preg_match( '/_([A-Z]{2})$/', $locale, $matches ) ) {
		$file = strtolower( $matches[1] ) . '.png';
	} else {
		$file = 'zz.png'; // 'zz.png' doesn't exist, just a dummy.
	}

	$file = path_join( 'images/flag-icons', $file );
	$url = '';

	if ( file_exists( path_join( BOGO_PLUGIN_DIR, $file ) ) ) {
		$url = bogo_plugin_url( $file );
	}

	return apply_filters( 'bogo_get_flag', $url, $locale );
}

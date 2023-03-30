<?php

function bogo_language_suggestion( $args = '' ) {
	$args = wp_parse_args( $args, array(
		'echo' => false,
	) );

	$locale_to_suggest = false;

	foreach ( bogo_http_accept_languages() as $locale ) {
		$locale_to_suggest = bogo_get_closest_locale( $locale );

		if ( $locale_to_suggest ) {
			break;
		}
	}

	$output = '';

	if ( $locale_to_suggest ) {
		$lang_name = bogo_get_language( $locale_to_suggest );
		$lang_tag = bogo_language_tag( $locale_to_suggest );

		switch_to_locale( $locale_to_suggest );

		$link = sprintf(
			'<a %1$s>%2$s</a>',
			bogo_format_atts( array(
				'rel' => 'alternate',
				'hreflang' => $lang_tag,
				'href' => bogo_url(),
				'title' => $lang_name ? $lang_name : $lang_tag,
			) ),
			bogo_get_language( $locale_to_suggest )
		);

		$output = sprintf(
			'This plugin is also available in %1$s.',
			$link
		);

		restore_previous_locale();
	}

	$output = apply_filters( 'bogo_language_suggestion', $output, $args );

	if ( $args['echo'] ) {
		echo $output;
	} else {
		return $output;
	}
}

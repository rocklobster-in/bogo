<?php

function bogo_language_suggestion( $args = '' ) {
	$args = wp_parse_args( $args, array(
		'echo' => false,
	) );

	$locale_to_suggest = false;

	foreach ( bogo_http_accept_languages() as $locale ) {
		$locale = bogo_get_closest_locale( $locale );

		// A locale is available and it is not the current locale.
		if ( $locale and $locale !== determine_locale() ) {
			$locale_to_suggest = $locale;
			break;
		}
	}

	if ( $locale_to_suggest ) {
		$translation = reset( array_filter(
			bogo_language_switcher_links( $args ),
			function ( $link ) use ( $locale_to_suggest ) {
				return $link['locale'] === $locale_to_suggest && $link['href'];
			}
		) );
	}

	$output = '';

	if ( $translation ) {
		switch_to_locale( $locale_to_suggest );

		$link = sprintf(
			'<a %1$s>%2$s</a>',
			bogo_format_atts( array(
				'rel' => 'alternate',
				'hreflang' => $translation['lang'],
				'href' => $translation['href'],
				'title' => $translation['title'],
			) ),
			bogo_get_short_name( bogo_get_language( $locale_to_suggest ) )
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

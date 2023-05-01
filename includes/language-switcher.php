<?php

function bogo_language_switcher( $args = '' ) {
	$args = wp_parse_args( $args, array(
		'echo' => false,
	) );

	$links = bogo_language_switcher_links( $args );
	$total = count( $links );
	$count = 0;

	$output = '';

	foreach ( $links as $link ) {
		$count += 1;
		$class = array();
		$class[] = bogo_language_tag( $link['locale'] );
		$class[] = bogo_lang_slug( $link['locale'] );

		if ( get_locale() === $link['locale'] ) {
			$class[] = 'current';
		}

		if ( 1 == $count ) {
			$class[] = 'first';
		}

		if ( $total == $count ) {
			$class[] = 'last';
		}

		$class = implode( ' ', array_unique( $class ) );

		$label = $link['native_name'] ? $link['native_name'] : $link['title'];
		$title = $link['title'];

		if ( empty( $link['href'] ) ) {
			$li = esc_html( $label );
		} else {
			$atts = array(
				'rel' => 'alternate',
				'hreflang' => $link['lang'],
				'href' => esc_url( $link['href'] ),
				'title' => $title,
			);

			if ( get_locale() === $link['locale'] ) {
				$atts += array(
					'class' => 'current',
					'aria-current' => 'page',
				);
			}

			$li = sprintf(
				'<a %1$s>%2$s</a>',
				bogo_format_atts( $atts ),
				esc_html( $label )
			);
		}

		$li = sprintf(
			'<span class="bogo-language-name">%s</span>',
			$li
		);

		if ( apply_filters( 'bogo_use_flags', true ) ) {
			$country_code = bogo_get_country_code( $link['locale'] );

			$flag = sprintf(
				'<span class="bogoflags bogoflags-%s"></span>',
				$country_code ? strtolower( $country_code ) : 'zz'
			);

			$li = sprintf( '<li class="%1$s">%3$s %2$s</li>', $class, $li, $flag );
		} else {
			$li = sprintf( '<li class="%1$s">%2$s</li>', $class, $li );
		}

		$output .= $li . "\n";
	}

	$output = sprintf(
		'<ul class="bogo-language-switcher list-view">%s</ul>',
		$output
	);

	$output = apply_filters( 'bogo_language_switcher', $output, $args );

	if ( $args['echo'] ) {
		echo $output;
	} else {
		return $output;
	}
}


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

	$translations = array();

	if ( $locale_to_suggest ) {
		$translations = array_filter(
			bogo_language_switcher_links( $args ),
			function ( $link ) use ( $locale_to_suggest ) {
				return $link['locale'] === $locale_to_suggest && '' !== $link['href'];
			}
		);
	}

	$output = '';

	if ( $translations and $translation = reset( $translations ) ) {
		switch_to_locale( $locale_to_suggest );

		$lang_name = bogo_get_language( $locale_to_suggest );

		if ( $lang_name ) {
			$lang_name = bogo_get_short_name( $lang_name );
		} else {
			$lang_name = sprintf( '[%s]', $locale_to_suggest );
		}

		$link = sprintf(
			'<a %1$s>%2$s</a>',
			bogo_format_atts( array(
				'rel' => 'alternate',
				'hreflang' => $translation['lang'],
				'href' => $translation['href'],
				'title' => $translation['title'],
			) ),
			esc_html( $lang_name )
		);

		$output = sprintf(
			/* translators: %s: Language name */
			esc_html( __( "This page is also available in %s.", 'bogo' ) ),
			$link
		);

		$output = sprintf(
			'<p class="bogo-language-switcher suggestion-view">%s</p>',
			$output
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


function bogo_language_switcher_links( $args = '' ) {
	global $wp_query;

	$args = wp_parse_args( $args, array() );

	$locale = get_locale();

	$available_languages = bogo_available_languages();

	$translations = array();
	$is_singular = false;

	if ( is_singular()
	or ! empty( $wp_query->is_posts_page ) ) {
		$translations = bogo_get_post_translations( get_queried_object_id() );
		$is_singular = true;
	}

	$links = array();

	foreach ( $available_languages as $code => $name ) {
		$native_name = bogo_get_language_native_name( $code );

		if ( bogo_locale_is_alone( $code ) ) {
			$native_name = bogo_get_short_name( $native_name );
		}

		$link = array(
			'locale' => $code,
			'lang' => bogo_language_tag( $code ),
			'title' => $name,
			'native_name' => trim( $native_name ),
			'href' => '',
		);

		if ( $is_singular ) {
			if ( $locale === $code ) {
				$link['href'] = get_permalink( get_queried_object_id() );
			} elseif ( ! empty( $translations[$code] )
			and 'publish' == get_post_status( $translations[$code] ) ) {
				$link['href'] = get_permalink( $translations[$code] );
			}
		} else {
			$link['href'] = bogo_url( null, $code );
		}

		$links[] = $link;
	}

	return apply_filters( 'bogo_language_switcher_links', $links, $args );
}

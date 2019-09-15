<?php

add_shortcode( 'bogo', 'bogo_language_switcher' );

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

		if ( get_locale() == $link['locale'] ) {
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
			$li = sprintf(
				'<a rel="alternate" hreflang="%1$s" href="%2$s" title="%3$s">%4$s</a>',
				$link['lang'],
				esc_url( $link['href'] ),
				esc_attr( $title ),
				esc_html( $label ) );
		}

		if ( apply_filters( 'bogo_use_flags', true ) ) {
			$flag = bogo_get_flag( $link['locale'] );
			$flag = preg_replace( '/(?:.*?)([a-z]+)\.png$/', '$1', $flag, 1 );
			$flag = sprintf(
				'<span class="bogoflags bogoflags-%s"></span>',
				$flag ? $flag : 'zz' );

			$li = sprintf( '<li class="%1$s">%3$s %2$s</li>', $class, $li, $flag );
		} else {
			$li = sprintf( '<li class="%1$s">%2$s</li>', $class, $li );
		}

		$output .= $li . "\n";
	}

	$output = '<ul class="bogo-language-switcher">' . $output . '</ul>' . "\n";

	$output = apply_filters( 'bogo_language_switcher', $output, $args );

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
	$available_languages = bogo_available_languages( array(
		'exclude_enus_if_inactive' => true ) );

	$translations = array();
	$is_singular = false;

	if ( is_singular() || ! empty( $wp_query->is_posts_page ) ) {
		$translations = bogo_get_post_translations( get_queried_object_id() );
		$is_singular = true;
	}

	$links = array();

	foreach ( $available_languages as $code => $name ) {
		$link = array(
			'locale' => $code,
			'lang' => bogo_language_tag( $code ),
			'title' => $name,
			'native_name' => bogo_get_language_native_name( $code ),
			'href' => '',
		);

		if ( $is_singular ) {
			if ( $locale != $code
			&& ! empty( $translations[$code] )
			&& 'publish' == get_post_status( $translations[$code] ) ) {
				$link['href'] = get_permalink( $translations[$code] );
			}
		} else {
			if ( $locale != $code ) {
				$link['href'] = bogo_url( null, $code );
			}
		}

		$links[] = $link;
	}

	return apply_filters( 'bogo_language_switcher_links', $links, $args );
}

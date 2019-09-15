<?php

add_action( 'parse_query', 'bogo_parse_query' );

function bogo_parse_query( $query ) {
	$qv = &$query->query_vars;

	if ( ! empty( $qv['bogo_suppress_locale_query'] ) ) {
		return;
	}

	if ( $query->is_preview() && ( $qv['page_id'] || $qv['p'] ) ) {
		$qv['bogo_suppress_locale_query'] = true;
		return;
	}

	if ( isset( $qv['post_type'] ) && 'any' != $qv['post_type'] ) {
		$localizable = array_filter(
			(array) $qv['post_type'],
			'bogo_is_localizable_post_type'
		);

		if ( empty( $localizable ) ) {
			$qv['bogo_suppress_locale_query'] = true;
			return;
		}
	}

	$lang = isset( $qv['lang'] ) ? $qv['lang'] : '';

	if ( is_admin() ) {
		$locale = $lang;
	} else {
		if ( $lang ) {
			$locale = bogo_get_closest_locale( $lang );
		} else {
			$locale = get_locale();
		}

		if ( empty( $locale ) ) {
			$locale = bogo_get_default_locale();
		}
	}

	if ( empty( $locale ) || ! bogo_is_available_locale( $locale ) ) {
		$qv['bogo_suppress_locale_query'] = true;
		return;
	}

	$qv['lang'] = $locale;

	if ( is_admin() ) {
		return;
	}

	if ( $query->is_home
	&& 'page' == get_option( 'show_on_front' )
	&& get_option( 'page_on_front' ) ) {
		$query_keys = array_keys( wp_parse_args( $query->query ) );
		$query_keys = array_diff( $query_keys,
			array( 'preview', 'page', 'paged', 'cpage', 'lang' ) );

		if ( empty( $query_keys ) ) {
			$query->is_page = true;
			$query->is_home = false;
			$qv['page_id'] = get_option( 'page_on_front' );

			if ( ! empty( $qv['paged'] ) ) {
				$qv['page'] = $qv['paged'];
				unset( $qv['paged'] );
			}
		}
	}

	if ( '' != $qv['pagename'] ) {
		$query->queried_object = bogo_get_page_by_path( $qv['pagename'], $locale );

		if ( ! empty( $query->queried_object ) ) {
			$query->queried_object_id = (int) $query->queried_object->ID;
		} else {
			unset( $query->queried_object );
			unset( $query->queried_object_id );
		}

		if ( 'page' == get_option( 'show_on_front' )
		&& isset( $query->queried_object_id )
		&& $query->queried_object_id == get_option( 'page_for_posts' ) ) {
			$query->is_page = false;
			$query->is_home = true;
			$query->is_posts_page = true;
		}
	}

	if ( isset( $qv['post_type'] )
	&& 'any' != $qv['post_type']
	&& ! is_array( $qv['post_type'] )
	&& '' != $qv['name'] ) {
		$query->queried_object = bogo_get_page_by_path(
			$qv['name'], $locale, $qv['post_type'] );

		if ( ! empty( $query->queried_object ) ) {
			$query->queried_object_id = (int) $query->queried_object->ID;
		} else {
			unset( $query->queried_object );
			unset( $query->queried_object_id );
		}
	}

	if ( $query->is_posts_page
	&& ( ! isset( $qv['withcomments'] ) || ! $qv['withcomments'] ) ) {
		$query->is_comment_feed = false;
	}

	$query->is_singular =
		( $query->is_single || $query->is_page || $query->is_attachment );

	$query->is_embed =
		$query->is_embed && ( $query->is_singular || $query->is_404 );
}

add_filter( 'posts_join', 'bogo_posts_join', 10, 2 );

function bogo_posts_join( $join, $query ) {
	global $wpdb;

	$qv = &$query->query_vars;

	if ( ! empty( $qv['bogo_suppress_locale_query'] ) ) {
		return $join;
	}

	$locale = empty( $qv['lang'] ) ? '' : $qv['lang'];

	if ( ! bogo_is_available_locale( $locale ) ) {
		return $join;
	}

	if ( ! $meta_table = _get_meta_table( 'post' ) ) {
		return $join;
	}

	$join .= " LEFT JOIN $meta_table AS postmeta_bogo ON ($wpdb->posts.ID = postmeta_bogo.post_id AND postmeta_bogo.meta_key = '_locale')";

	return $join;
}

add_filter( 'posts_where', 'bogo_posts_where', 10, 2 );

function bogo_posts_where( $where, $query ) {
	global $wpdb;

	$qv = &$query->query_vars;

	if ( ! empty( $qv['bogo_suppress_locale_query'] ) ) {
		return $where;
	}

	$locale = empty( $qv['lang'] ) ? '' : $qv['lang'];

	if ( ! bogo_is_available_locale( $locale ) ) {
		return $where;
	}

	if ( ! $meta_table = _get_meta_table( 'post' ) ) {
		return $where;
	}

	$where .= " AND (1=0";

	$where .= $wpdb->prepare( " OR postmeta_bogo.meta_value LIKE %s", $locale );

	if ( bogo_is_default_locale( $locale ) ) {
		$where .= " OR postmeta_bogo.meta_id IS NULL";
	}

	$where .= ")";

	return $where;
}

add_filter( 'option_sticky_posts', 'bogo_option_sticky_posts' );

function bogo_option_sticky_posts( $posts ) {
	if ( is_admin() ) {
		return $posts;
	}

	$locale = get_locale();

	foreach ( $posts as $key => $post_id ) {
		if ( $locale != bogo_get_post_locale( $post_id ) ) {
			unset( $posts[$key] );
		}
	}

	return $posts;
}

add_filter( 'option_page_on_front', 'bogo_get_local_post' );
add_filter( 'option_page_for_posts', 'bogo_get_local_post' );

function bogo_get_local_post( $post_id ) {
	global $wpdb;

	if ( is_admin() || empty( $post_id ) ) {
		return $post_id;
	}

	$post_type = get_post_type( $post_id );

	if ( ! post_type_exists( $post_type )
	|| ! bogo_is_localizable_post_type( $post_type ) ) {
		return $post_id;
	}

	$locale = get_locale();

	if ( bogo_get_post_locale( $post_id ) == $locale ) {
		return $post_id;
	}

	$original = get_post_meta( $post_id, '_original_post', true );

	if ( empty( $original ) ) {
		$original = $post_id;
	}

	$q = "SELECT ID FROM $wpdb->posts AS posts";
	$q .= " LEFT JOIN $wpdb->postmeta AS pm1";
	$q .= " ON posts.ID = pm1.post_id AND pm1.meta_key = '_original_post'";
	$q .= " LEFT JOIN $wpdb->postmeta AS pm2";
	$q .= " ON posts.ID = pm2.post_id AND pm2.meta_key = '_locale'";
	$q .= " WHERE 1=1";
	$q .= " AND post_status = 'publish'";
	$q .= $wpdb->prepare( " AND post_type = %s", $post_type );
	$q .= $wpdb->prepare( " AND (ID = %d OR pm1.meta_value = %d)",
		$original, $original );
	$q .= " AND (1=0";
	$q .= $wpdb->prepare( " OR pm2.meta_value LIKE %s", $locale );
	$q .= bogo_is_default_locale( $locale ) ? " OR pm2.meta_id IS NULL" : "";
	$q .= ")";

	$translation = absint( $wpdb->get_var( $q ) );

	if ( $translation ) {
		return $translation;
	}

	return $post_id;
}

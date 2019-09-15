<?php

/* Post Template */

add_filter( 'body_class', 'bogo_body_class', 10, 2 );

function bogo_body_class( $classes, $class ) {
	$locale = bogo_language_tag( get_locale() );
	$locale = esc_attr( $locale );

	if ( $locale && ! in_array( $locale, $classes ) ) {
		$classes[] = $locale;
	}

	return $classes;
}

add_filter( 'post_class', 'bogo_post_class', 10, 3 );

function bogo_post_class( $classes, $class, $post_id ) {
	$locale = bogo_get_post_locale( $post_id );
	$locale = bogo_language_tag( $locale );
	$locale = esc_attr( $locale );

	if ( $locale && ! in_array( $locale, $classes ) ) {
		$classes[] = $locale;
	}

	return $classes;
}

function bogo_get_post_locale( $post_id ) {
	$locale = get_post_meta( $post_id, '_locale', true );

	if ( empty( $locale ) ) {
		$locale = bogo_get_default_locale();
	}

	return $locale;
}

function bogo_localizable_post_types() {
	$localizable = apply_filters( 'bogo_localizable_post_types',
		array( 'post', 'page' ) );

	$localizable = array_diff( $localizable,
		array( 'attachment', 'revision', 'nav_menu_item' ) );

	return $localizable;
}

function bogo_is_localizable_post_type( $post_type ) {
	return ! empty( $post_type ) && in_array( $post_type, bogo_localizable_post_types() );
}

function bogo_count_posts( $locale, $post_type = 'post' ) {
	global $wpdb;

	if ( ! bogo_is_available_locale( $locale )
	|| ! bogo_is_localizable_post_type( $post_type ) ) {
		return false;
	}

	$q = "SELECT COUNT(1) FROM $wpdb->posts";
	$q .= " LEFT JOIN $wpdb->postmeta ON ID = $wpdb->postmeta.post_id AND meta_key = '_locale'";
	$q .= " WHERE 1=1";
	$q .= $wpdb->prepare( " AND post_type = %s", $post_type );
	$q .= " AND post_status = 'publish'";
	$q .= " AND (1=0";
	$q .= $wpdb->prepare( " OR meta_value LIKE %s", $locale );
	$q .= bogo_is_default_locale( $locale ) ? " OR meta_id IS NULL" : "";
	$q .= ")";

	return (int) $wpdb->get_var( $q );
}

function bogo_get_post_translations( $post_id = 0 ) {
	$post = get_post( $post_id );

	if ( ! $post ) {
		return false;
	}

	if ( 'auto-draft' == $post->post_status ) {
		if ( ! empty( $_REQUEST['original_post'] ) ) {
			$original = get_post_meta( $_REQUEST['original_post'], '_original_post', true );

			if ( empty( $original ) ) {
				$original = (int) $_REQUEST['original_post'];
			}
		} else {
			return false;
		}
	} else {
		$original = get_post_meta( $post->ID, '_original_post', true );
	}

	if ( empty( $original ) ) {
		$original = $post->ID;
	}

	$args = array(
		'bogo_suppress_locale_query' => true,
		'posts_per_page' => -1,
		'post_status' => 'any',
		'post_type' => $post->post_type,
		'meta_key' => '_original_post',
		'meta_value' => $original,
	);

	$q = new WP_Query();
	$posts = $q->query( $args );

	$translations = array();

	$original_post_status = get_post_status( $original );

	if ( $original != $post->ID && $original_post_status && 'trash' != $original_post_status ) {
		$locale = bogo_get_post_locale( $original );
		$translations[$locale] = get_post( $original );
	}

	foreach ( $posts as $p ) {
		if ( $p->ID == $post->ID ) {
			continue;
		}

		$locale = bogo_get_post_locale( $p->ID );

		if ( ! bogo_is_available_locale( $locale ) ) {
			continue;
		}

		if ( ! isset( $translations[$locale] ) ) {
			$translations[$locale] = $p;
		}
	}

	return array_filter( $translations );
}

function bogo_get_post_translation( $post_id, $locale ) {
	$translations = bogo_get_post_translations( $post_id );

	if ( isset( $translations[$locale] ) ) {
		return $translations[$locale];
	}

	return false;
}

function bogo_get_page_by_path( $page_path, $locale = null, $post_type = 'page' ) {
	global $wpdb;

	if ( ! bogo_is_available_locale( $locale ) ) {
		$locale = bogo_get_default_locale();
	}

	$page_path = rawurlencode( urldecode( $page_path ) );
	$page_path = str_replace( '%2F', '/', $page_path );
	$page_path = str_replace( '%20', ' ', $page_path );

	$parts = explode( '/', trim( $page_path, '/' ) );
	$parts = array_map( 'esc_sql', $parts );
	$parts = array_map( 'sanitize_title_for_query', $parts );

	$in_string = "'" . implode( "','", $parts ) . "'";
	$post_type_sql = $post_type;
	$wpdb->escape_by_ref( $post_type_sql );

	$q = "SELECT ID, post_name, post_parent FROM $wpdb->posts";
	$q .= " LEFT JOIN $wpdb->postmeta ON ID = $wpdb->postmeta.post_id AND meta_key = '_locale'";
	$q .= " WHERE 1=1";
	$q .= " AND post_name IN ($in_string)";
	$q .= " AND (post_type = '$post_type_sql' OR post_type = 'attachment')";
	$q .= " AND (1=0";
	$q .= $wpdb->prepare( " OR meta_value LIKE %s", $locale );
	$q .= bogo_is_default_locale( $locale ) ? " OR meta_id IS NULL" : "";
	$q .= ")";

	$pages = $wpdb->get_results( $q, OBJECT_K );

	$revparts = array_reverse( $parts );

	$foundid = 0;

	foreach ( (array) $pages as $page ) {
		if ( $page->post_name != $revparts[0] ) {
			continue;
		}

		$count = 0;
		$p = $page;

		while ( $p->post_parent != 0 && isset( $pages[$p->post_parent] ) ) {
			$count++;
			$parent = $pages[$p->post_parent];

			if ( ! isset( $revparts[$count] )
			|| $parent->post_name != $revparts[$count] ) {
				break;
			}

			$p = $parent;
		}

		if ( $p->post_parent == 0
		&& $count + 1 == count( $revparts )
		&& $p->post_name == $revparts[$count] ) {
			$foundid = $page->ID;
			break;
		}
	}

	if ( $foundid ) {
		return get_page( $foundid );
	}

	return null;
}

function bogo_duplicate_post( $original_post, $locale ) {
	$original_post = get_post( $original_post );

	if ( ! $original_post
	|| ! bogo_is_localizable_post_type( get_post_type( $original_post ) )
	|| 'auto-draft' == get_post_status( $original_post ) ) {
		return false;
	}

	if ( ! bogo_is_available_locale( $locale )
	|| bogo_get_post_locale( $original_post->ID ) == $locale ) {
		return false;
	}

	if ( bogo_get_post_translation( $original_post->ID, $locale ) ) {
		return false;
	}

	$postarr = array(
		'post_content' => $original_post->post_content,
		'post_title' => $original_post->post_title,
		'post_name' => $original_post->post_name,
		'post_excerpt' => $original_post->post_excerpt,
		'post_status' => 'draft',
		'post_type' => $original_post->post_type,
		'comment_status' => $original_post->comment_status,
		'ping_status' => $original_post->ping_status,
		'post_password' => $original_post->post_password,
		'post_content_filtered' => $original_post->post_content_filtered,
		'post_parent' => $original_post->post_parent,
		'menu_order' => $original_post->menu_order,
		'post_mime_type' => $original_post->post_mime_type,
		'tax_input' => array(),
		'meta_input' => array(),
	);

	if ( ! empty( $original_post->post_parent ) ) {
		$parent_translation = bogo_get_post_translation(
			$original_post->post_parent, $locale );

		if ( $parent_translation ) {
			$postarr['post_parent'] = $parent_translation->ID;
		}
	}

	if ( $taxonomies = get_object_taxonomies( $original_post ) ) {
		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_post_terms( $original_post->ID,
				$taxonomy, array( 'fields' => 'ids' ) );

			if ( $terms && ! is_wp_error( $terms ) ) {
				$postarr['tax_input'][$taxonomy] = $terms;
			}
		}
	}

	if ( $post_metas = get_post_meta( $original_post->ID ) ) {
		$postarr['meta_input'] = $post_metas;
	}

	$postarr = apply_filters( 'bogo_duplicate_post',
		$postarr, $original_post, $locale );

	if ( $taxonomies = $postarr['tax_input'] ) {
		$postarr['tax_input'] = array();
	}

	if ( $post_metas = $postarr['meta_input'] ) {
		$postarr['meta_input'] = array();
	}

	$new_post_id = wp_insert_post( $postarr );

	if ( $new_post_id ) {
		if ( $taxonomies ) {
			foreach ( $taxonomies as $taxonomy => $terms ) {
				wp_set_post_terms( $new_post_id, $terms, $taxonomy );
			}
		}

		if ( $post_metas ) {
			foreach ( $post_metas as $meta_key => $meta_values ) {
				if ( in_array( $meta_key, array( '_locale', '_original_post' ) ) ) {
					continue;
				}

				foreach ( (array) $meta_values as $meta_value ) {
					add_post_meta( $new_post_id, $meta_key, $meta_value );
				}
			}
		}

		update_post_meta( $new_post_id, '_locale', $locale );

		$meta_original_post = get_post_meta( $original_post->ID,
			'_original_post', true );

		if ( $meta_original_post ) {
			update_post_meta( $new_post_id, '_original_post', $meta_original_post );
		} else {
			update_post_meta( $new_post_id, '_original_post', $original_post->ID );
		}
	}

	return $new_post_id;
}

add_filter( 'get_pages', 'bogo_get_pages', 10, 2 );

function bogo_get_pages( $pages, $args ) {
	if ( is_admin()
	|| ! bogo_is_localizable_post_type( $args['post_type'] )
	|| ! empty( $args['bogo_suppress_locale_query'] ) ) {
		return $pages;
	}

	$locale = isset( $args['lang'] ) ? $args['lang'] : get_locale();

	if ( ! bogo_is_available_locale( $locale ) ) {
		return $pages;
	}

	$new_pages = array();

	foreach ( (array) $pages as $page ) {
		$post_locale = bogo_get_post_locale( $page->ID );

		if ( $post_locale == $locale ) {
			$new_pages[] = $page;
		}
	}

	return $new_pages;
}

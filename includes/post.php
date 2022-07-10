<?php

/* Post Meta */

add_action( 'init',

	function() {
		$post_types = bogo_localizable_post_types();

		$auth_callback = function ( $allowed, $meta_key, $object_id, $user_id ) {
			return user_can( $user_id, 'edit_post', $object_id );
		};

		foreach ( $post_types as $post_type ) {
			register_post_meta( $post_type,
				'_locale',
				array(
					'type' => 'string',
					'single' => true,
					'auth_callback' => $auth_callback,
					'show_in_rest' => true,
				)
			);

			register_post_meta( $post_type,
				'_original_post',
				array(
					'type' => 'string',
					'single' => true,
					'auth_callback' => $auth_callback,
					'show_in_rest' => true,
				)
			);
		}
	},

	10, 0
);

/* Post Template */

add_filter( 'body_class', 'bogo_body_class', 10, 2 );

function bogo_body_class( $classes, $classes_to_add ) {
	$locale = bogo_language_tag( get_locale() );
	$locale = esc_attr( $locale );

	if ( $locale and ! in_array( $locale, $classes ) ) {
		$classes[] = $locale;
	}

	return $classes;
}

add_filter( 'post_class', 'bogo_post_class', 10, 3 );

function bogo_post_class( $classes, $classes_to_add, $post_id ) {
	$locale = bogo_get_post_locale( $post_id );
	$locale = bogo_language_tag( $locale );
	$locale = esc_attr( $locale );

	if ( $locale and ! in_array( $locale, $classes ) ) {
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
		array( 'post', 'page' )
	);

	$localizable = array_diff(
		$localizable,
		array( 'attachment', 'revision', 'nav_menu_item' )
	);

	return $localizable;
}

function bogo_is_localizable_post_type( $post_type ) {
	return ! empty( $post_type ) && in_array( $post_type, bogo_localizable_post_types() );
}

function bogo_count_posts( $locale, $post_type = 'post' ) {
	global $wpdb;

	if ( ! bogo_is_available_locale( $locale )
	or ! bogo_is_localizable_post_type( $post_type ) ) {
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

	static $translations = array();

	if ( isset( $translations[$post->ID] ) ) {
		return $translations[$post->ID];
	}

	$original_post = get_post_meta( $post->ID, '_original_post', true );

	// For back-compat
	if ( empty( $original_post ) ) {
		$original_post = $post->ID;
	}

	$args = array(
		'bogo_suppress_locale_query' => true,
		'posts_per_page' => -1,
		'post_status' => 'any',
		'post_type' => $post->post_type,
		'meta_key' => '_original_post',
		'meta_value' => $original_post,
	);

	$q = new WP_Query();
	$posts = $q->query( $args );

	// For back-compat
	if ( is_int( $original_post )
	and $p = get_post( $original_post )
	and 'trash' !== get_post_status( $p ) ) {
		array_unshift( $posts, $p );
	}

	$translations[$post->ID] = array();

	foreach ( $posts as $p ) {
		if ( $p->ID === $post->ID ) {
			continue;
		}

		$locale = bogo_get_post_locale( $p->ID );

		if ( ! bogo_is_available_locale( $locale ) ) {
			continue;
		}

		if ( ! isset( $translations[$post->ID][$locale] ) ) {
			$translations[$post->ID][$locale] = $p;
		}
	}

	$translations[$post->ID] = array_filter( $translations[$post->ID] );

	return $translations[$post->ID];
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
		if ( $page->post_name !== $revparts[0] ) {
			continue;
		}

		$count = 0;
		$p = $page;

		while ( $p->post_parent != 0
		and isset( $pages[$p->post_parent] ) ) {
			$count++;
			$parent = $pages[$p->post_parent];

			if ( ! isset( $revparts[$count] )
			or $parent->post_name !== $revparts[$count] ) {
				break;
			}

			$p = $parent;
		}

		if ( $p->post_parent == 0
		and $count + 1 == count( $revparts )
		and $p->post_name === $revparts[$count] ) {
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
	or ! bogo_is_localizable_post_type( get_post_type( $original_post ) )
	or 'auto-draft' == get_post_status( $original_post ) ) {
		return false;
	}

	if ( ! bogo_is_available_locale( $locale )
	or bogo_get_post_locale( $original_post->ID ) == $locale ) {
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
			$original_post->post_parent, $locale
		);

		if ( $parent_translation ) {
			$postarr['post_parent'] = $parent_translation->ID;
		}
	}

	if ( $taxonomies = get_object_taxonomies( $original_post ) ) {
		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_post_terms( $original_post->ID,
				$taxonomy, array( 'fields' => 'ids' )
			);

			if ( $terms and ! is_wp_error( $terms ) ) {
				$postarr['tax_input'][$taxonomy] = $terms;
			}
		}
	}

	if ( $post_metas = get_post_meta( $original_post->ID ) ) {

		$post_metas = array_map( function ( $post_meta ) {
			return array_map( 'maybe_unserialize', (array) $post_meta );
		}, $post_metas );

		$postarr['meta_input'] = $post_metas;
	}

	$postarr = apply_filters( 'bogo_duplicate_post', $postarr,
		$original_post, $locale
	);

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
			'_original_post', true
		);

		if ( $meta_original_post ) {
			update_post_meta( $new_post_id,
				'_original_post', $meta_original_post
			);
		} else {
			$original_post_guid = get_the_guid( $original_post );

			if ( empty( $original_post_guid ) ) {
				$original_post_guid = $original_post->ID;
			}

			$translations = bogo_get_post_translations( $original_post );

			update_post_meta( $original_post->ID,
				'_original_post', $original_post_guid
			);

			if ( $translations ) {
				foreach ( $translations as $tr_locale => $tr_post ) {
					update_post_meta( $tr_post->ID,
						'_original_post', $original_post_guid
					);
				}
			}

			update_post_meta( $new_post_id,
				'_original_post', $original_post_guid
			);
		}
	}

	return $new_post_id;
}

add_filter( 'get_pages', 'bogo_get_pages', 10, 2 );

function bogo_get_pages( $pages, $args ) {
	if ( is_admin()
	or ! bogo_is_localizable_post_type( $args['post_type'] )
	or ! empty( $args['bogo_suppress_locale_query'] ) ) {
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

add_action( 'save_post', 'bogo_save_post', 10, 2 );

function bogo_save_post( $post_id, $post ) {
	if ( did_action( 'import_start' )
	and ! did_action( 'import_end' ) ) {
		// Importing
		return;
	}

	if ( ! bogo_is_localizable_post_type( $post->post_type ) ) {
		return;
	}

	$current_locales = get_post_meta( $post_id, '_locale' );
	$locale = null;

	if ( ! empty( $current_locales ) ) {
		foreach ( $current_locales as $current_locale ) {
			if ( bogo_is_available_locale( $current_locale ) ) {
				$locale = $current_locale;
				break;
			}
		}

		if ( empty( $locale )
		or 1 < count( $current_locales ) ) {
			delete_post_meta( $post_id, '_locale' );
			$current_locales = array();
		}
	}

	if ( empty( $current_locales ) ) {
		if ( bogo_is_available_locale( $locale ) ) {
			// $locale = $locale;
		} elseif ( ! empty( $_REQUEST['locale'] )
		and bogo_is_available_locale( $_REQUEST['locale'] ) ) {
			$locale = $_REQUEST['locale'];
		} elseif ( 'auto-draft' == get_post_status( $post_id ) ) {
			$locale = bogo_get_user_locale();
		} else {
			$locale = bogo_get_default_locale();
		}

		add_post_meta( $post_id, '_locale', $locale, true );
	}

	$original_post = get_post_meta( $post_id, '_original_post', true );

	if ( empty( $original_post ) ) {
		$post_guid = get_the_guid( $post_id );

		if ( empty( $post_guid ) ) {
			$post_guid = $post_id;
		}

		$translations = bogo_get_post_translations( $post_id );

		update_post_meta( $post_id, '_original_post', $post_guid );

		if ( $translations ) {
			foreach ( $translations as $tr_locale => $tr_post ) {
				update_post_meta( $tr_post->ID, '_original_post', $post_guid );
			}
		}
	}
}

add_filter( 'pre_wp_unique_post_slug', 'bogo_unique_post_slug', 10, 6 );

function bogo_unique_post_slug( $override_slug, $slug, $post_id, $post_status, $post_type, $post_parent ) {

	if ( ! bogo_is_localizable_post_type( $post_type ) ) {
		return $override_slug;
	}

	$locale = bogo_get_post_locale( $post_id );

	if ( ! bogo_is_available_locale( $locale ) ) {
		return $override_slug;
	}

	$override_slug = $slug;

	$q = new WP_Query();

	global $wp_rewrite;

	$feeds = is_array( $wp_rewrite->feeds ) ? $wp_rewrite->feeds : array();

	if ( is_post_type_hierarchical( $post_type ) ) {
		$q_args = array(
			'name' => $slug,
			'lang' => $locale,
			'post_type' => array( $post_type, 'attachment' ),
			'post_parent' => $post_parent,
			'post__not_in' => array( $post_id ),
			'posts_per_page' => 1,
		);

		$is_bad_slug = in_array( $slug, $feeds )
			|| 'embed' === $slug
			|| preg_match( "@^($wp_rewrite->pagination_base)?\d+$@", $slug )
			|| apply_filters(
				'wp_unique_post_slug_is_bad_hierarchical_slug', false,
				$slug, $post_type, $post_parent
			);

		if ( ! $is_bad_slug ) {
			$q_results = $q->query( $q_args );
			$is_bad_slug = ! empty( $q_results );
		}

		if ( $is_bad_slug ) {
			$suffix = 1;

			while ( $is_bad_slug ) {
				$suffix += 1;
				$alt_slug = sprintf( '%s-%s',
					bogo_truncate_post_slug( $slug, 200 - ( strlen( $suffix ) + 1 ) ),
					$suffix
				);

				$q_results = $q->query( array_merge(
					$q_args,
					array( 'name' => $alt_slug )
				) );

				$is_bad_slug = ! empty( $q_results );
			}

			$override_slug = $alt_slug;
		}

	} else {
		$q_args = array(
			'name' => $slug,
			'lang' => $locale,
			'post_type' => $post_type,
			'post__not_in' => array( $post_id ),
			'posts_per_page' => 1,
		);

		$is_bad_slug = in_array( $slug, $feeds )
			|| 'embed' === $slug
			|| apply_filters(
				'wp_unique_post_slug_is_bad_flat_slug', false,
				$slug, $post_type
			);

		if ( ! $is_bad_slug ) {
			$post = get_post( $post_id );

			if ( 'post' === $post_type
			and ( ! $post or $post->post_name !== $slug )
			and preg_match( '/^[0-9]+$/', $slug ) ) {
				$slug_num = intval( $slug );

				if ( $slug_num ) {
					$permastructs = array_values( array_filter(
						explode( '/', get_option( 'permalink_structure' ) )
					) );
					$postname_index = array_search( '%postname%', $permastructs );

					$is_bad_slug = false
						|| 0 === $postname_index
						|| ( $postname_index
							&& '%year%' === $permastructs[$postname_index - 1]
							&& 13 > $slug_num )
						|| ( $postname_index
							&& '%monthnum%' === $permastructs[$postname_index - 1]
							&& 32 > $slug_num );
				}
			}
		}

		if ( ! $is_bad_slug ) {
			$q_results = $q->query( $q_args );
			$is_bad_slug = ! empty( $q_results );
		}

		if ( $is_bad_slug ) {
			$suffix = 1;

			while ( $is_bad_slug ) {
				$suffix += 1;
				$alt_slug = sprintf( '%s-%s',
					bogo_truncate_post_slug( $slug, 200 - ( strlen( $suffix ) + 1 ) ),
					$suffix
				);

				$q_results = $q->query( array_merge(
					$q_args,
					array( 'name' => $alt_slug )
				) );

				$is_bad_slug = ! empty( $q_results );
			}

			$override_slug = $alt_slug;
		}
	}

	return $override_slug;
}

function bogo_truncate_post_slug( $slug, $length = 200 ) {
	if ( strlen( $slug ) > $length ) {
		$decoded_slug = urldecode( $slug );

		if ( $decoded_slug === $slug ) {
			$slug = substr( $slug, 0, $length );
		} else {
			$slug = utf8_uri_encode( $decoded_slug, $length );
		}
	}

	return rtrim( $slug, '-' );
}


add_filter(
	'wp_sitemaps_posts_query_args',
	'bogo_sitemaps_posts_query_args',
	10, 2
);

function bogo_sitemaps_posts_query_args( $args, $post_type ) {
	if ( bogo_is_localizable_post_type( $post_type ) ) {
		$args['bogo_suppress_locale_query'] = true;
	}

	return $args;
}

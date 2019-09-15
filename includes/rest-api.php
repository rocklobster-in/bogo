<?php

add_action( 'rest_api_init', 'bogo_rest_api_init' );

function bogo_rest_api_init() {
	register_rest_route( 'bogo/v1',
		'/languages',
		array(
			'methods' => WP_REST_Server::READABLE,
			'callback' => 'bogo_rest_languages',
		)
	);

	register_rest_route( 'bogo/v1',
		'/posts/(?P<id>\d+)/translations',
		array(
			'methods' => WP_REST_Server::READABLE,
			'callback' => 'bogo_rest_post_translations',
		)
	);

	$locale_pattern = '[a-z]{2,3}(?:_[A-Z]{2}(?:_[A-Za-z0-9]+)?)?';

	register_rest_route( 'bogo/v1',
		'/posts/(?P<id>\d+)/translations/(?P<locale>' . $locale_pattern . ')',
		array(
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => 'bogo_rest_create_post_translation',
		)
	);
}

function bogo_rest_languages( WP_REST_Request $request ) {
	if ( ! function_exists( 'wp_get_available_translations' ) ) {
		require_once ABSPATH . 'wp-admin/includes/translation-install.php';
	}

	$available_translations = wp_get_available_translations();

	$local_available_locales = bogo_available_locales( array(
		'exclude_enus_if_inactive' => true ) );

	$available_translations = array_intersect_key(
		$available_translations,
		array_flip( $local_available_locales ) );

	return rest_ensure_response( $available_translations );
}

function bogo_rest_post_translations( WP_REST_Request $request ) {
	$post_id = $request->get_param( 'id' );

	$post = get_post( $post_id );

	if ( ! $post ) {
		return new WP_Error( 'bogo_post_not_found',
			__( "The requested post was not found.", 'bogo' ),
			array( 'status' => 404 ) );
	}

	$post_type_object = get_post_type_object( $post->post_type );
	$user_can_edit = current_user_can(
		$post_type_object->cap->edit_post, $post->ID );

	if ( ! $user_can_edit && 'publish' != get_post_status( $post ) ) {
		return new WP_Error( 'bogo_post_not_found',
			__( "The requested post was not found.", 'bogo' ),
			array( 'status' => 404 ) );
	}

	$response = array();
	$translations = bogo_get_post_translations( $post );

	foreach ( $translations as $locale => $translation ) {
		if ( ! current_user_can( 'edit_post', $translation->ID )
		&& 'publish' != get_post_status( $translation ) ) {
			continue;
		}

		$response[$locale] = array(
			'lang' => array( 'tag' => bogo_language_tag( $locale ) ),
			'id' => $translation->ID,
			'link' => get_permalink( $translation->ID ),
			'slug' => $translation->post_name,
			'type' => $translation->post_type,
			'date' => mysql_to_rfc3339( $translation->post_date ),
			'date_gmt' => mysql_to_rfc3339( $translation->post_date_gmt ),
			'modified' => mysql_to_rfc3339( $translation->post_modified ),
			'modified_gmt' => mysql_to_rfc3339( $translation->post_modified_gmt ),
			'guid' => array( 'rendered' => '', 'raw' => '' ),
			'title' => array( 'rendered' => '', 'raw' => '' ),
			'content' => array( 'rendered' => '', 'raw' => '' ),
			'excerpt' => array( 'rendered' => '', 'raw' => '' ),
		);

		$lang = bogo_get_language( $locale );
		$lang = empty( $lang ) ? $locale : $lang;
		$response[$locale]['lang']['name'] = $lang;

		if ( ! empty( $translation->guid ) ) {
			$response[$locale]['guid']['rendered'] =
				apply_filters( 'get_the_guid', $translation->guid );

			if ( $user_can_edit ) {
				$response[$locale]['guid']['raw'] = $translation->guid;
			}
		}

		if ( ! empty( $translation->post_title ) ) {
			$response[$locale]['title']['rendered'] =
				get_the_title( $translation->ID );

			if ( $user_can_edit ) {
				$response[$locale]['title']['raw'] = $translation->post_title;
			}
		}

		if ( ! empty( $translation->post_content ) ) {
			$response[$locale]['content']['rendered'] =
				apply_filters( 'the_content', $translation->post_content );

			if ( $user_can_edit ) {
				$response[$locale]['content']['raw'] = $translation->post_content;
			}
		}

		if ( ! empty( $translation->post_excerpt ) ) {
			$response[$locale]['excerpt']['rendered'] = apply_filters( 'the_excerpt',
				apply_filters( 'get_the_excerpt', $translation->post_excerpt ) );

			if ( $user_can_edit ) {
				$response[$locale]['excerpt']['raw'] = $translation->post_excerpt;
			}
		}
	}

	return rest_ensure_response( $response );
}

function bogo_rest_create_post_translation( WP_REST_Request $request ) {
	$post_id = $request->get_param( 'id' );

	$post = get_post( $post_id );

	if ( ! $post
	|| ! current_user_can( 'edit_post', $post->ID )
	&& 'publish' != get_post_status( $post ) ) {
		return new WP_Error( 'bogo_post_not_found',
			__( "The requested post was not found.", 'bogo' ),
			array( 'status' => 404 ) );
	}

	$locale = $request->get_param( 'locale' );

	if ( ! bogo_is_available_locale( $locale ) ) {
		return new WP_Error( 'bogo_locale_invalid',
			__( "The requested locale is not available.", 'bogo' ),
			array( 'status' => 400 ) );
	}

	if ( ( $post_type_object = get_post_type_object( $post->post_type ) )
	&& ! current_user_can( $post_type_object->cap->edit_posts ) ) {
		return new WP_Error( 'bogo_post_type_forbidden',
			__( "You are not allowed to edit posts in this post type.", 'bogo' ),
			array( 'status' => 403 ) );
	}

	$new_post_id = bogo_duplicate_post( $post, $locale );

	if ( ! $new_post_id ) {
		return new WP_Error( 'bogo_post_duplication_failed',
			__( "Failed to duplicate a post.", 'bogo' ),
			array( 'status' => 500 ) );
	}

	$new_post = get_post( $new_post_id );
	$response = array();

	$response[$locale] = array(
		'lang' => array( 'tag' => bogo_language_tag( $locale ) ),
		'id' => $new_post->ID,
		'link' => get_permalink( $new_post->ID ),
		'edit_link' => get_edit_post_link( $new_post->ID, 'raw' ),
		'slug' => $new_post->post_name,
		'type' => $new_post->post_type,
		'date' => mysql_to_rfc3339( $new_post->post_date ),
		'date_gmt' => mysql_to_rfc3339( $new_post->post_date_gmt ),
		'modified' => mysql_to_rfc3339( $new_post->post_modified ),
		'modified_gmt' => mysql_to_rfc3339( $new_post->post_modified_gmt ),
		'guid' => array( 'rendered' => '', 'raw' => $new_post->guid ),
		'title' => array( 'rendered' => '', 'raw' => $new_post->post_title ),
		'content' => array( 'rendered' => '', 'raw' => $new_post->post_content ),
		'excerpt' => array( 'rendered' => '', 'raw' => $new_post->post_excerpt ),
	);

	$lang = bogo_get_language( $locale );
	$lang = empty( $lang ) ? $locale : $lang;
	$response[$locale]['lang']['name'] = $lang;

	if ( ! empty( $new_post->guid ) ) {
		$response[$locale]['guid']['rendered'] =
			apply_filters( 'get_the_guid', $new_post->guid );
	}

	if ( ! empty( $new_post->post_title ) ) {
		$response[$locale]['title']['rendered'] =
			get_the_title( $new_post->ID );
	}

	if ( ! empty( $new_post->post_content ) ) {
		$response[$locale]['content']['rendered'] =
			apply_filters( 'the_content', $new_post->post_content );
	}

	if ( ! empty( $new_post->post_excerpt ) ) {
		$response[$locale]['excerpt']['rendered'] = apply_filters( 'the_excerpt',
			apply_filters( 'get_the_excerpt', $new_post->post_excerpt ) );
	}

	return rest_ensure_response( $response );
}

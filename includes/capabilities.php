<?php

add_filter( 'map_meta_cap', 'bogo_map_meta_cap', 10, 4 );

function bogo_map_meta_cap( $caps, $cap, $user_id, $args ) {
	$meta_caps = array(
		'bogo_manage_language_packs' => 'install_languages',
		'bogo_edit_terms_translation' => 'manage_categories',
		'bogo_access_all_locales' => 'manage_options',
		'bogo_access_locale' => 'read',
	);

	$meta_caps = apply_filters( 'bogo_map_meta_cap', $meta_caps );

	$caps = array_diff( $caps, array_keys( $meta_caps ) );

	if ( isset( $meta_caps[$cap] ) ) {
		$caps[] = $meta_caps[$cap];
	}

	static $accessible_locales = array();

	if ( 'bogo_access_all_locales' !== $cap
	and ! isset( $accessible_locales[$user_id] ) ) {
		$accessible_locales[$user_id] = bogo_get_user_accessible_locales(
			$user_id
		);
	}

	if ( 'bogo_access_locale' === $cap
	and ! user_can( $user_id, 'bogo_access_all_locales' ) ) {
		$locale = $args[0];

		if ( ! in_array( $locale, $accessible_locales[$user_id] ) ) {
			$caps[] = 'do_not_allow';
		}
	}

	if ( in_array( $cap, array( 'edit_post', 'delete_post' ), true )
	and $post = get_post( $args[0] )
	and $user_id !== $post->post_author
	and ! user_can( $user_id, 'bogo_access_all_locales' ) ) {
		$locale = bogo_get_post_locale( $post->ID );

		if ( ! in_array( $locale, $accessible_locales[$user_id] ) ) {
			$caps[] = 'do_not_allow';
		}
	}

	return $caps;
}

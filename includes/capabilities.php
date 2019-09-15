<?php

add_filter( 'map_meta_cap', 'bogo_map_meta_cap', 10, 4 );

function bogo_map_meta_cap( $caps, $cap, $user_id, $args ) {
	$meta_caps = array(
		'bogo_manage_language_packs' => 'install_languages',
		'bogo_edit_terms_translation' => 'manage_options',
		'bogo_access_all_locales' => 'manage_options',
		'bogo_access_locale' => 'read',
	);

	$meta_caps = apply_filters( 'bogo_map_meta_cap', $meta_caps );

	$caps = array_diff( $caps, array_keys( $meta_caps ) );

	if ( isset( $meta_caps[$cap] ) ) {
		$caps[] = $meta_caps[$cap];
	}

	if ( 'bogo_access_locale' == $cap
	&& ! user_can( $user_id, 'bogo_access_all_locales' ) ) {
		$accessible_locales = bogo_get_user_accessible_locales( $user_id );

		if ( ! empty( $accessible_locales ) ) {
			$accessible_locales = bogo_filter_locales( $accessible_locales );
			$locale = $args[0];

			if ( ! in_array( $locale, $accessible_locales ) ) {
				$caps[] = 'do_not_allow';
			}
		}
	}

	return $caps;
}

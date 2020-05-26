<?php

/* Toolbar (Admin Bar) */

add_action( 'admin_bar_menu', 'bogo_admin_bar_init', 0, 1 );

function bogo_admin_bar_init( $wp_admin_bar ) {
	switch_to_locale( bogo_get_user_locale() );
}

add_action( 'wp_after_admin_bar_render', 'bogo_after_admin_bar_render', 10, 0 );

function bogo_after_admin_bar_render() {
	if ( is_locale_switched() ) {
		restore_current_locale();
	}
}

add_action( 'admin_bar_menu', 'bogo_admin_bar_menu', 10, 1 );

function bogo_admin_bar_menu( $wp_admin_bar ) {
	$current_locale = bogo_get_user_locale();

	$available_languages = bogo_available_languages( array(
		'current_user_can_access' => true,
	) );

	if ( isset( $available_languages[$current_locale] ) ) {
		$current_language = $available_languages[$current_locale];
		unset( $available_languages[$current_locale] );
	} else {
		$current_language = $current_locale;
	}

	$wp_admin_bar->add_menu( array(
		'parent' => 'top-secondary',
		'id' => 'bogo-user-locale',
		'title' => sprintf(
			'<span class="ab-icon"></span><span class="ab-label">%s</span>',
			esc_html( $current_language ) ),
	) );

	foreach ( $available_languages as $locale => $lang ) {
		$url = add_query_arg(
			array(
				'action' => 'bogo-switch-locale',
				'locale' => $locale,
				'redirect_to' => urlencode( $_SERVER['REQUEST_URI'] ),
			),
			admin_url( 'profile.php' )
		);

		$url = wp_nonce_url( $url, 'bogo-switch-locale' );

		$wp_admin_bar->add_menu( array(
			'parent' => 'bogo-user-locale',
			'id' => 'bogo-user-locale-' . $locale,
			'title' => $lang,
			'href' => $url,
		) );
	}
}

add_action( 'admin_init', 'bogo_switch_user_locale', 10, 0 );

function bogo_switch_user_locale() {
	if ( empty( $_REQUEST['action'] )
	or 'bogo-switch-locale' != $_REQUEST['action'] ) {
		return;
	}

	check_admin_referer( 'bogo-switch-locale' );

	$locale = isset( $_REQUEST['locale'] ) ? $_REQUEST['locale'] : '';

	if ( ! bogo_is_available_locale( $locale )
	or $locale == bogo_get_user_locale() ) {
		return;
	}

	update_user_option( get_current_user_id(), 'locale', $locale, true );

	if ( ! empty( $_REQUEST['redirect_to'] ) ) {
		wp_safe_redirect( $_REQUEST['redirect_to'] );
		exit();
	}
}

function bogo_get_user_locale( $user_id = 0 ) {
	$default_locale = bogo_get_default_locale();

	if ( ! $user_id = absint( $user_id ) ) {
		if ( function_exists( 'wp_get_current_user' ) ) {
			$current_user = wp_get_current_user();

			if ( ! empty( $current_user->locale ) ) {
				return $current_user->locale;
			}
		}

		if ( ! $user_id = get_current_user_id() ) {
			return $default_locale;
		}
	}

	$locale = get_user_option( 'locale', $user_id );

	if ( bogo_is_available_locale( $locale ) ) {
		return $locale;
	}

	return $default_locale;
}

function bogo_get_user_accessible_locales( $user_id ) {
	global $wpdb;

	$user_id = absint( $user_id );

	if ( user_can( $user_id, 'bogo_access_all_locales' ) ) {
		$locales = bogo_available_locales();

		return $locales;
	}

	$meta_key = $wpdb->get_blog_prefix() . 'accessible_locale';

	$locales = (array) get_user_meta( $user_id, $meta_key );

	if ( bogo_is_enus_deactivated() ) {
		$locales = array_diff( $locales, array( 'en_US' ) );
	}

	$locales = bogo_filter_locales( $locales );

	if ( empty( $locales ) ) {
		$locales = array( bogo_get_default_locale() );
	}

	return $locales;
}

add_filter( 'insert_user_meta', 'bogo_user_meta_filter', 10, 3 );

function bogo_user_meta_filter( $meta, $user, $update ) {
	if ( user_can( $user, 'bogo_access_all_locales' ) ) {
		return $meta;
	}

	$locale = $meta['locale'];

	if ( empty( $locale ) ) {
		$locale = bogo_get_default_locale();
	}

	$accessible_locales = bogo_filter_locales(
		bogo_get_user_accessible_locales( $user->ID )
	);

	if ( empty( $accessible_locales ) ) {
		$locale = '';
	} elseif ( ! in_array( $locale, $accessible_locales, true ) ) {
		$locale = $accessible_locales[0];
	}

	$meta['locale'] = $locale;

	return $meta;
}

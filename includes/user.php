<?php

/* Admin Bar */

add_action( 'admin_bar_menu', 'bogo_admin_bar_menu' );

function bogo_admin_bar_menu( $wp_admin_bar ) {
	$current_locale = bogo_get_user_locale();
	$current_language = bogo_get_language( $current_locale );

	if ( ! $current_language ) {
		$current_language = $current_locale;
	}

	$wp_admin_bar->add_menu( array(
		'parent' => 'top-secondary',
		'id' => 'bogo-user-locale',
		'title' => sprintf(
			'<span class="ab-icon"></span><span class="ab-label">%s</span>',
			esc_html( $current_language ) ),
	) );

	$available_languages = bogo_available_languages( array(
		'exclude' => array( $current_locale ),
		'exclude_enus_if_inactive' => true,
		'current_user_can_access' => true,
	) );

	foreach ( $available_languages as $locale => $lang ) {
		$url = admin_url( 'profile.php?action=bogo-switch-locale&locale=' . $locale );

		$url = add_query_arg(
			array( 'redirect_to' => urlencode( $_SERVER['REQUEST_URI'] ) ),
			$url );

		$url = wp_nonce_url( $url, 'bogo-switch-locale' );

		$wp_admin_bar->add_menu( array(
			'parent' => 'bogo-user-locale',
			'id' => 'bogo-user-locale-' . $locale,
			'title' => $lang,
			'href' => $url,
		) );
	}
}

add_action( 'admin_init', 'bogo_switch_user_locale' );

function bogo_switch_user_locale() {
	if ( empty( $_REQUEST['action'] )
	|| 'bogo-switch-locale' != $_REQUEST['action'] ) {
		return;
	}

	check_admin_referer( 'bogo-switch-locale' );

	$locale = isset( $_REQUEST['locale'] ) ? $_REQUEST['locale'] : '';

	if ( ! bogo_is_available_locale( $locale )
	|| $locale == bogo_get_user_locale() ) {
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
	$meta_key = $wpdb->get_blog_prefix() . 'accessible_locale';

	$locales = get_user_meta( $user_id, $meta_key );

	if ( bogo_is_enus_deactivated() ) {
		$locales = array_diff( $locales, array( 'en_US' ) );
	}

	return $locales;
}

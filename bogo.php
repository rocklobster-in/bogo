<?php
/*
Plugin Name: Bogo
Description: A straight-forward multilingual plugin. No more double-digit custom DB tables or hidden HTML comments that could cause you headaches later on.
Plugin URI: https://ideasilo.wordpress.com/bogo/
Author: Takayuki Miyoshi
Author URI: https://ideasilo.wordpress.com/
Text Domain: bogo
Domain Path: /languages/
Version: 3.2.1
*/

define( 'BOGO_VERSION', '3.2.1' );

define( 'BOGO_PLUGIN', __FILE__ );

define( 'BOGO_PLUGIN_BASENAME', plugin_basename( BOGO_PLUGIN ) );

define( 'BOGO_PLUGIN_NAME', trim( dirname( BOGO_PLUGIN_BASENAME ), '/' ) );

define( 'BOGO_PLUGIN_DIR', untrailingslashit( dirname( BOGO_PLUGIN ) ) );

require_once BOGO_PLUGIN_DIR . '/includes/functions.php';
require_once BOGO_PLUGIN_DIR . '/includes/pomo.php';
require_once BOGO_PLUGIN_DIR . '/includes/rewrite.php';
require_once BOGO_PLUGIN_DIR . '/includes/link-template.php';
require_once BOGO_PLUGIN_DIR . '/includes/language-switcher.php';
require_once BOGO_PLUGIN_DIR . '/includes/nav-menu.php';
require_once BOGO_PLUGIN_DIR . '/includes/widgets.php';
require_once BOGO_PLUGIN_DIR . '/includes/post.php';
require_once BOGO_PLUGIN_DIR . '/includes/user.php';
require_once BOGO_PLUGIN_DIR . '/includes/capabilities.php';
require_once BOGO_PLUGIN_DIR . '/includes/query.php';
require_once BOGO_PLUGIN_DIR . '/includes/flags.php';
require_once BOGO_PLUGIN_DIR . '/includes/rest-api.php';

if ( is_admin() ) {
	require_once BOGO_PLUGIN_DIR . '/admin/admin.php';
}

add_action( 'plugins_loaded', 'bogo_plugins_loaded' );

function bogo_plugins_loaded() {
	load_plugin_textdomain( 'bogo', 'wp-content/plugins/bogo/languages', 'bogo/languages' );
}

add_action( 'init', 'bogo_init' );

function bogo_init() {
	bogo_languages();
	Bogo_POMO::import( get_locale() );

	if ( ! ( is_admin() || is_robots() || is_feed() || is_trackback() ) ) {
		$locale = get_locale();

		if ( ! isset( $_COOKIE['lang'] ) || $_COOKIE['lang'] != $locale ) {
			setcookie( 'lang', $locale, 0, '/' );
		}
	}
}

add_filter( 'locale', 'bogo_locale' );

function bogo_locale( $locale ) {
	global $wp_rewrite, $wp_query;

	if ( ! did_action( 'plugins_loaded' ) ) {
		return $locale;
	}

	static $bogo_locale = '';

	if ( $bogo_locale ) {
		return $bogo_locale;
	}

	if ( is_admin() ) {
		return $bogo_locale = bogo_get_user_locale();
	}

	$default_locale = bogo_get_default_locale();

	if ( ! empty( $wp_query->query_vars ) ) {
		if ( ( $lang = get_query_var( 'lang' ) )
		&& $closest = bogo_get_closest_locale( $lang ) ) {
			return $bogo_locale = $closest;
		} else {
			return $bogo_locale = $default_locale;
		}
	}

	if ( isset( $wp_rewrite ) && $wp_rewrite->using_permalinks() ) {
		$url = is_ssl() ? 'https://' : 'http://';
		$url .= $_SERVER['HTTP_HOST'];
		$url .= $_SERVER['REQUEST_URI'];

		$home = set_url_scheme( get_option( 'home' ) );
		$home = trailingslashit( $home );

		$available_locales = bogo_available_locales();
		$available_locales = array_map( 'bogo_lang_slug', $available_locales );
		$available_locales = implode( '|', $available_locales );
		$pattern = '#^' . preg_quote( $home ) . '(' . $available_locales . ')(/|$)#';

		if ( preg_match( $pattern, $url, $matches )
		&& $closest = bogo_get_closest_locale( $matches[1] ) ) {
			return $bogo_locale = $closest;
		}
	}

	$lang = bogo_get_lang_from_url();

	if ( $lang && $closest = bogo_get_closest_locale( $lang ) ) {
		return $bogo_locale = $closest;
	}

	return $bogo_locale = $default_locale;
}

add_filter( 'query_vars', 'bogo_query_vars' );

function bogo_query_vars( $query_vars ) {
	$query_vars[] = 'lang';

	return $query_vars;
}

add_action( 'wp_enqueue_scripts', 'bogo_enqueue_scripts' );

function bogo_enqueue_scripts() {
	wp_enqueue_style( 'bogo',
		plugins_url( 'includes/css/style.css', BOGO_PLUGIN_BASENAME ),
		array(), BOGO_VERSION, 'all' );

	if ( is_rtl() ) {
		wp_enqueue_style( 'bogo-rtl',
			plugins_url( 'includes/css/style-rtl.css', BOGO_PLUGIN_BASENAME ),
			array(), BOGO_VERSION, 'all' );
	}
}

add_action( 'deactivate_' . BOGO_PLUGIN_BASENAME, 'bogo_deactivate' );

function bogo_deactivate() {
	bogo_delete_prop( 'lang_rewrite_regex' );
}

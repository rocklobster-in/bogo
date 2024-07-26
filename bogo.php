<?php
/*
 * Plugin Name: Bogo
 * Description: A straight-forward multilingual plugin. No more double-digit custom DB tables or hidden HTML comments that could cause you headaches later on.
 * Plugin URI: https://ideasilo.wordpress.com/bogo/
 * Author: Takayuki Miyoshi
 * Author URI: https://ideasilo.wordpress.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bogo
 * Domain Path: /languages/
 * Version: 3.8.2
 * Requires at least: 6.4
 * Requires PHP: 7.4
 */

define( 'BOGO_VERSION', '3.8.2' );

define( 'BOGO_PLUGIN', __FILE__ );

define( 'BOGO_PLUGIN_BASENAME', plugin_basename( BOGO_PLUGIN ) );

define( 'BOGO_PLUGIN_NAME', trim( dirname( BOGO_PLUGIN_BASENAME ), '/' ) );

define( 'BOGO_PLUGIN_DIR', untrailingslashit( dirname( BOGO_PLUGIN ) ) );

require_once BOGO_PLUGIN_DIR . '/includes/functions.php';
require_once BOGO_PLUGIN_DIR . '/includes/language-functions.php';
require_once BOGO_PLUGIN_DIR . '/includes/formatting.php';
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
require_once BOGO_PLUGIN_DIR . '/includes/shortcodes.php';
require_once BOGO_PLUGIN_DIR . '/includes/block-editor/block-editor.php';

if ( is_admin() ) {
	require_once BOGO_PLUGIN_DIR . '/admin/admin.php';
}

add_action( 'init', 'bogo_init', 10, 0 );

function bogo_init() {
	bogo_languages();
	Bogo_POMO::import( determine_locale() );
}

add_filter( 'pre_determine_locale', 'bogo_pre_determine_locale', 10, 1 );

function bogo_pre_determine_locale( $locale ) {
	if ( ! empty( $_GET['filter_action'] ) ) {
		return $locale;
	}

	if ( isset( $_GET['lang'] )
	and $closest = bogo_get_closest_locale( $_GET['lang'] ) ) {
		$locale = $closest;
	}

	return $locale;
}

add_filter( 'locale', 'bogo_locale', 10, 1 );

function bogo_locale( $locale ) {
	global $wp_rewrite, $wp_query;

	if ( ! did_action( 'plugins_loaded' ) or is_admin() ) {
		return $locale;
	}

	static $bogo_locale = '';

	if ( $bogo_locale ) {
		return $bogo_locale;
	}

	$default_locale = bogo_get_default_locale();

	if ( ! empty( $wp_query->query_vars ) ) {
		if ( $lang = get_query_var( 'lang' )
		and $closest = bogo_get_closest_locale( $lang ) ) {
			return $bogo_locale = $closest;
		} else {
			return $bogo_locale = $default_locale;
		}
	}

	if ( isset( $wp_rewrite )
	and $wp_rewrite->using_permalinks() ) {
		$url = is_ssl() ? 'https://' : 'http://';
		$url .= $_SERVER['HTTP_HOST'];
		$url .= $_SERVER['REQUEST_URI'];

		$home = set_url_scheme( get_option( 'home' ) );
		$home = trailingslashit( $home );

		$pattern = '#^'
			. preg_quote( $home )
			. '(?:' . preg_quote( trailingslashit( $wp_rewrite->index ) ) . ')?'
			. bogo_get_lang_regex()
			. '(/|$)#';

		if ( preg_match( $pattern, $url, $matches )
		and $closest = bogo_get_closest_locale( $matches[1] ) ) {
			return $bogo_locale = $closest;
		}
	}

	$lang = bogo_get_lang_from_url();

	if ( $lang
	and $closest = bogo_get_closest_locale( $lang ) ) {
		return $bogo_locale = $closest;
	}

	return $bogo_locale = $default_locale;
}

add_filter( 'query_vars', 'bogo_query_vars', 10, 1 );

function bogo_query_vars( $query_vars ) {
	$query_vars[] = 'lang';

	return $query_vars;
}

add_action( 'wp_enqueue_scripts', 'bogo_enqueue_scripts', 10, 0 );

function bogo_enqueue_scripts() {
	wp_enqueue_style( 'bogo',
		plugins_url( 'includes/css/style.css', BOGO_PLUGIN_BASENAME ),
		array(), BOGO_VERSION, 'all'
	);

	if ( is_rtl() ) {
		wp_enqueue_style( 'bogo-rtl',
			plugins_url( 'includes/css/style-rtl.css', BOGO_PLUGIN_BASENAME ),
			array(), BOGO_VERSION, 'all'
		);
	}
}

add_action( 'deactivate_' . BOGO_PLUGIN_BASENAME, 'bogo_deactivate', 10, 0 );

function bogo_deactivate() {
	bogo_delete_prop( 'lang_rewrite_regex' );
}

<?php

add_filter( 'bloginfo', 'bogo_bloginfo_filter', 10, 2 );

function bogo_bloginfo_filter( $output, $show ) {
	if ( ! Bogo_POMO::is_ready() ) {
		return $output;
	}

	if ( 'name' == $show ) {
		$output = bogo_translate( 'blogname', 'blogname', $output );
	} elseif ( 'description' == $show ) {
		$output = bogo_translate( 'blogdescription', 'blogdescription', $output );
	}

	return $output;
}

add_filter( 'get_term', 'bogo_get_term_filter', 10, 2 );

function bogo_get_term_filter( $term, $taxonomy ) {
	if ( ! Bogo_POMO::is_ready() ) {
		return $term;
	}

	if ( $term instanceof WP_Term ) {
		$term = bogo_translate_term( $term );
	}

	return $term;
}

add_action( 'load-edit-tags.php', 'bogo_remove_get_term_filter' );

function bogo_remove_get_term_filter() {
	remove_filter( 'get_term', 'bogo_get_term_filter' );
}

function bogo_translate_term( WP_Term $term ) {
	$term->name = bogo_translate(
		sprintf( '%s:%d', $term->taxonomy, $term->term_id ),
		$term->taxonomy,
		$term->name );

	return $term;
}

function bogo_translate( $singular, $context = '', $default = '' ) {
	return Bogo_POMO::translate( $singular, $context, $default );
}

class Bogo_POMO {

	private static $mo;

	public static function translate( $singular, $context = '', $default = '' ) {
		if ( ! self::$mo ) {
			return '' !== $default ? $default : $singular;
		}

		$translated = self::$mo->translate( $singular, $context );

		if ( $translated == $singular && '' !== $default ) {
			return $default;
		} else {
			return $translated;
		}
	}

	public static function export( $locale, $entries = array() ) {
		if ( ! bogo_is_available_locale( $locale ) ) {
			return false;
		}

		$dir = self::dir();

		$headers = array(
			'PO-Revision-Date' => current_time( 'mysql', 'gmt' ) . '+0000',
			'MIME-Version' => '1.0',
			'Content-Type' => 'text/plain; charset=UTF-8',
			'Content-Transfer-Encoding' => '8bit',
			'X-Generator' => sprintf( 'Bogo %s', BOGO_VERSION ),
			'Language' => $locale,
			'Project-Id-Version' =>
				sprintf( 'WordPress %s', get_bloginfo( 'version' ) ),
		);

		require_once ABSPATH . WPINC . '/pomo/po.php';
		$po = new PO();
		$po->set_headers( $headers );

		foreach ( (array) $entries as $entry ) {
			$entry = new Translation_Entry( $entry );
			$po->add_entry( $entry );
		}

		$po_file = is_multisite()
			? sprintf( '%d-%s.po', get_current_blog_id(), $locale )
			: sprintf( '%s.po', $locale );
		$po_file = path_join( $dir, $po_file );
		$po->export_to_file( $po_file );

		$mo = new MO();
		$mo->set_headers( $headers );

		foreach ( (array) $entries as $entry ) {
			$entry = new Translation_Entry( $entry );
			$mo->add_entry( $entry );
		}

		$mo_file = is_multisite()
			? sprintf( '%d-%s.mo', get_current_blog_id(), $locale )
			: sprintf( '%s.mo', $locale );
		$mo_file = path_join( $dir, $mo_file );
		return $mo->export_to_file( $mo_file );
	}

	public static function import( $locale ) {
		if ( ! bogo_is_available_locale( $locale ) ) {
			return false;
		}

		$dir = self::dir();

		$mo_file = is_multisite()
			? sprintf( '%d-%s.mo', get_current_blog_id(), $locale )
			: sprintf( '%s.mo', $locale );
		$mo_file = path_join( $dir, $mo_file );

		if ( ! is_readable( $mo_file ) ) {
			return false;
		}

		$mo = new MO();

		if ( ! $mo->import_from_file( $mo_file ) ) {
			return false;
		}

		self::$mo = $mo;
		return true;
	}

	public static function reset() {
		self::$mo = null;
	}

	public static function is_ready() {
		return (bool) self::$mo;
	}

	private static function dir() {
		$dir = path_join( WP_LANG_DIR, 'bogo' );
		$dir = apply_filters( 'bogo_pomo_dir', $dir );
		wp_mkdir_p( $dir );
		return $dir;
	}
}

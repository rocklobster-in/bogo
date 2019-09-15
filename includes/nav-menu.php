<?php

add_filter( 'wp_get_nav_menu_items', 'bogo_get_nav_menu_items', 10, 3 );

function bogo_get_nav_menu_items( $items, $menu, $args ) {
	if ( is_admin() ) {
		return $items;
	}

	$locale = get_locale();

	foreach ( $items as $key => $item ) {
		if ( ! in_array( $locale, $item->bogo_locales ) ) {
			unset( $items[$key] );
		}
	}

	return $items;
}

add_filter( 'wp_setup_nav_menu_item', 'bogo_setup_nav_menu_item' );

function bogo_setup_nav_menu_item( $menu_item ) {
	if ( isset( $menu_item->bogo_locales ) ) {
		return $menu_item;
	}

	$menu_item->bogo_locales = array();

	if ( isset( $menu_item->post_type ) && 'nav_menu_item' == $menu_item->post_type ) {
		$menu_item->bogo_locales = get_post_meta( $menu_item->ID, '_locale' );
	}

	if ( $menu_item->bogo_locales ) {
		$menu_item->bogo_locales = bogo_filter_locales( $menu_item->bogo_locales );
	} else {
		$menu_item->bogo_locales = bogo_available_locales();
	}

	return $menu_item;
}

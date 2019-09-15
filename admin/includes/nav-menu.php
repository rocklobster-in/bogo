<?php

require_once( ABSPATH . 'wp-admin/includes/class-walker-nav-menu-edit.php' );

class Bogo_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {

	public function start_el( &$output, $item, $depth = 0, $args = '', $id = 0 ) {
		$parallel_output = '';

		parent::start_el( $parallel_output, $item, $depth );

		$parallel_output = preg_replace(
			'/<div class="menu-item-settings wp-clearfix" id="menu-item-settings-([0-9]+)">/',
			'<div class="menu-item-settings wp-clearfix has-bogo-settings" id="menu-item-settings-${1}">' . $this->language_settings( $item ),
			$parallel_output, 1 );

		$output .= $parallel_output;
	}

	private function language_settings( $menu_item ) {
		$available_languages = bogo_available_languages( array(
			'exclude_enus_if_inactive' => true,
			'orderby' => 'value',
		) );

		if ( ! $available_languages ) {
			return '';
		}

		$output = '';

		$output .= '<fieldset class="field-bogo-language description bogo-locale-options">';

		$output .= sprintf( '<legend>%s</legend>',
			/* translators: followed by available languages list */
			esc_html( __( 'Displayed on pages in', 'bogo' ) )
		);

		$name_attr = sprintf(
			'menu-item-bogo-locale[%s][]',
			$menu_item->ID
		);

		$dummy = sprintf(
			'<input type="hidden" name="%1$s" value="%2$s" />',
			esc_attr( $name_attr ),
			'zxx' // special code in ISO 639-2
		);

		$output .= $dummy;

		foreach ( $available_languages as $locale => $language ) {
			$selected = in_array( $locale, (array) $menu_item->bogo_locales );

			$id_attr = sprintf(
				'edit-menu-item-bogo-locale-%1$s-%2$s',
				$menu_item->ID,
				$locale
			);

			$input = sprintf(
				'<input type="checkbox" id="%1$s" name="%2$s" value="%3$s"%4$s />',
				esc_attr( $id_attr ),
				esc_attr( $name_attr ),
				esc_attr( $locale ),
				$selected ? ' checked="checked"' : ''
			);

			$label = sprintf(
				'<label for="%1$s" class="bogo-locale-option%2$s">%3$s %4$s</label>',
				esc_attr( $id_attr ),
				$selected ? ' checked' : '',
				$input,
				esc_html( $language )
			);

			$output .= $label;
		}

		$output .= '</fieldset>';

		return $output;
	}
}

add_filter( 'wp_edit_nav_menu_walker', 'bogo_edit_nav_menu_walker', 10, 2 );

function bogo_edit_nav_menu_walker( $class, $menu_id ) {
	return 'Bogo_Walker_Nav_Menu_Edit';
}

add_action( 'wp_update_nav_menu_item', 'bogo_update_nav_menu_item', 10, 2 );

function bogo_update_nav_menu_item( $menu_id, $menu_item_id ) {
	if ( ! isset( $_POST['menu-item-bogo-locale'][$menu_item_id] ) ) {
		return;
	}

	$requested_locales = (array) $_POST['menu-item-bogo-locale'][$menu_item_id];
	$current_locales = (array) get_post_meta( $menu_item_id, '_locale' );

	foreach ( (array) bogo_available_locales() as $locale ) {
		if ( in_array( $locale, $current_locales )
		&& ! in_array( $locale, $requested_locales ) ) {
			delete_post_meta( $menu_item_id, '_locale', $locale );
		}

		if ( ! in_array( $locale, $current_locales )
		&& in_array( $locale, $requested_locales ) ) {
			add_post_meta( $menu_item_id, '_locale', $locale );
		}
	}

	if ( ! metadata_exists( 'post', $menu_item_id, '_locale' ) ) {
		add_post_meta( $menu_item_id, '_locale', 'zxx' ); // special code in ISO 639-2
	}
}

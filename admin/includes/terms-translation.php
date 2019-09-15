<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Bogo_Terms_Translation_List_Table extends WP_List_Table {
	private $locale_to_edit = null;

	public static function define_columns() {
		$columns = array(
			'original' => __( 'Original', 'bogo' ),
			'translation' => __( 'Translation', 'bogo' ),
			'context' => __( 'Context', 'bogo' ),
		);

		return $columns;
	}

	public function prepare_items() {
		$this->locale_to_edit = isset( $_GET['locale'] ) ? $_GET['locale'] : '';

		if ( ! bogo_is_available_locale( $this->locale_to_edit ) ) {
			return;
		}

		$items = bogo_terms_translation( $this->locale_to_edit );

		if ( ! empty( $_REQUEST['s'] ) ) {
			$keywords = preg_split( '/[\s]+/', $_REQUEST['s'] );

			foreach ( $items as $key => $item ) {
				$haystack = $item['original'] . ' ' . $item['translated'];

				foreach ( $keywords as $needle ) {
					if ( false === stripos( $haystack, $needle ) ) {
						unset( $items[$key] );
						break;
					}
				}
			}
		}

		$items = array_filter( $items );
		$items = array_values( $items );

		$per_page = $this->get_items_per_page(
			'languages_page_bogo_texts_per_page' );
		$offset = ( $this->get_pagenum() - 1 ) * $per_page;

		$this->items = array_slice( $items, $offset, $per_page );

		$total_items = count( $items );
		$total_pages = ceil( $total_items / $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page' => $per_page,
		) );
	}

	public function get_columns() {
		return get_column_headers( get_current_screen() );
	}

	public function column_original( $item ) {
		return esc_html( $item['original'] );
	}

	public function column_translation( $item ) {
		return sprintf(
			'<input name="%1$s" type="text" id="%1$s" value="%2$s" class="%3$s" />',
			esc_attr( $item['name'] ),
			esc_attr( $item['translated'] ),
			'large-text'
		);
	}

	public function column_context( $item ) {
		return esc_html( $item['context'] );
	}

	protected function display_tablenav( $which ) {
		echo sprintf( '<div class="tablenav %1$s">', esc_attr( $which ) );
		$this->extra_tablenav( $which );
		$this->pagination( $which );
		echo '<br class="clear" />';
		echo '</div>';
	}

	protected function extra_tablenav( $which ) {
		if ( 'top' == $which ) {
			echo '<div class="alignleft">';
			echo '<select name="locale" id="select-locale">';
			echo sprintf(
				'<option value="">%1$s</option>',
				esc_html( __( '-- Select Language to Edit --', 'bogo' ) ) );

			$available_locales = bogo_available_locales( array(
				'exclude_enus_if_inactive' => true,
				'current_user_can_access' => true,
			) );

			foreach ( $available_locales as $locale ) {
				if ( bogo_is_default_locale( $locale ) ) {
					continue;
				}

				echo sprintf(
					'<option value="%1$s"%3$s>%2$s</option>',
					esc_attr( $locale ),
					esc_html( bogo_get_language( $locale ) ),
					$locale === $this->locale_to_edit ? ' selected="selected"' : ''
				);
			}

			echo '</select>';
			echo '</div>';
		}

		if ( 'bottom' == $which ) {
			echo '<div class="alignleft">';
			submit_button();
			echo '</div>';
		}
	}
}

function bogo_terms_translation( $locale_to_edit ) {
	static $items = array();
	static $locale = null;

	if ( ! empty( $items ) && $locale === $locale_to_edit ) {
		return $items;
	}

	$locale = $locale_to_edit;

	if ( ! Bogo_POMO::import( $locale ) ) {
		Bogo_POMO::reset();
	}

	if ( ! bogo_is_available_locale( $locale ) ) {
		return $items;
	}

	$items[] = array(
		'name' => 'blogname',
		'original' => get_option( 'blogname' ),
		'translated' => bogo_translate( 'blogname', 'blogname',
			get_option( 'blogname' ) ),
		'context' => __( 'Site Title', 'bogo' ),
	);

	$items[] = array(
		'name' => 'blogdescription',
		'original' => get_option( 'blogdescription' ),
		'translated' => bogo_translate( 'blogdescription', 'blogdescription',
			get_option( 'blogdescription' ) ),
		'context' => __( 'Tagline', 'bogo' ),
	);

	remove_filter( 'get_term', 'bogo_get_term_filter' );

	foreach ( (array) get_taxonomies( array(), 'objects' ) as $taxonomy ) {
		$tax_labels = get_taxonomy_labels( $taxonomy );
		$terms = get_terms( array(
			'taxonomy' => $taxonomy->name,
			'orderby' => 'slug',
			'hide_empty' => false,
		) );

		foreach ( (array) $terms as $term ) {
			$name = sprintf( '%s:%d', $taxonomy->name, $term->term_id );
			$items[] = array(
				'name' => $name,
				'original' => $term->name,
				'translated' => bogo_translate( $name, $taxonomy->name,
					$term->name ),
				'context' => $tax_labels->name,
			);
		}
	}

	add_filter( 'get_term', 'bogo_get_term_filter', 10, 2 );

	$items = apply_filters( 'bogo_terms_translation', $items, $locale );

	return $items;
}

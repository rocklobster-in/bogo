<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Bogo_Language_Packs_List_Table extends WP_List_Table {

	private $count_active = 0;
	private $count_inactive = 0;

	public static function define_columns() {
		$columns = array(
			'name' => __( 'Language', 'bogo' ),
			'status' => __( 'Status', 'bogo' ),
		);

		return $columns;
	}

	public function get_columns() {
		return get_column_headers( get_current_screen() );
	}

	public function prepare_items() {
		$this->items = array();

		$search_keys = empty( $_REQUEST['s'] )
			? array()
			: preg_split( '/[\s]+/', $_REQUEST['s'] );

		$status = trim( $_REQUEST['status'] ?? '' );

		$locales = array_keys( bogo_languages() );

		sort( $locales );

		$locales = array_unique( array_merge(
			array( bogo_get_default_locale() ),
			bogo_available_locales(),
			array( 'en_US' ),
			$locales
		) );

		foreach ( $locales as $locale ) {
			$language = bogo_get_language( $locale );

			$is_active = ( 'en_US' == $locale )
				? ! bogo_is_enus_deactivated()
				: bogo_is_available_locale( $locale );

			if ( $is_active ) {
				$this->count_active += 1;
			} else {
				$this->count_inactive += 1;
			}

			if ( $search_keys ) {
				$haystack = $locale . ' ' . $language;
				$needle_found = true;

				foreach ( $search_keys as $needle ) {
					if ( false === stripos( $haystack, $needle ) ) {
						$needle_found = false;
						break;
					}
				}

				if ( ! $needle_found ) {
					continue;
				}
			}

			if (
				'active' === $status and ! $is_active or
				'inactive' === $status and $is_active
			) {
				continue;
			}

			$this->items[] = (object) array(
				'locale' => $locale,
				'language' => $language,
				'active' => $is_active,
			);
		}

		$this->set_pagination_args( array(
			'total_items' => count( $this->items ),
			'total_pages' => 1,
		) );
	}

	protected function get_views() {
		$links = array();
		$menu_page_url = menu_page_url( 'bogo', false );

		$status = trim( $_REQUEST['status'] ?? '' );

		// All
		$count_all = $this->count_active + $this->count_inactive;

		$all = sprintf(
			/* translators: %s: number of language packs */
			_nx(
				'All <span class="count">(%s)</span>',
				'All <span class="count">(%s)</span>',
				$count_all, 'language status', 'bogo'
			),
			number_format_i18n( $count_all )
		);

		$links['all'] = sprintf(
			'<a href="%1$s"%2$s>%3$s</a>',
			esc_url( $menu_page_url ),
			'' === $status ? ' class="current"' : '',
			$all
		);

		// Active
		$active = sprintf(
			/* translators: %s: number of language packs */
			_nx(
				'Active <span class="count">(%s)</span>',
				'Active <span class="count">(%s)</span>',
				$this->count_active, 'language status', 'bogo'
			),
			number_format_i18n( $this->count_active )
		);

		$links['active'] = sprintf(
			'<a href="%1$s"%2$s>%3$s</a>',
			esc_url( add_query_arg( 'status', 'active', $menu_page_url ) ),
			'active' === $status ? ' class="current"' : '',
			$active
		);

		// Inactive
		$inactive = sprintf(
			/* translators: %s: number of language packs */
			_nx(
				'Inactive <span class="count">(%s)</span>',
				'Inactive <span class="count">(%s)</span>',
				$this->count_inactive, 'language status', 'bogo'
			),
			number_format_i18n( $this->count_inactive )
		);

		$links['inactive'] = sprintf(
			'<a href="%1$s"%2$s>%3$s</a>',
			esc_url( add_query_arg( 'status', 'inactive', $menu_page_url ) ),
			'inactive' === $status ? ' class="current"' : '',
			$inactive
		);

		return $links;
	}

	public function column_name( $item ) {
		echo sprintf(
			'<strong>%1$s</strong> [%2$s]',
			esc_html( $item->language ),
			esc_html( $item->locale )
		);
	}

	public function column_status( $item ) {
		$status = '';

		if ( $item->active ) {
			if ( bogo_is_default_locale( $item->locale ) ) {
				$status = __( 'Site Language', 'bogo' );
			} else {
				$status = __( 'Active', 'bogo' );
			}

		} else {
			$status = __( 'Inactive', 'bogo' );
		}

		if ( $status ) {
			echo sprintf(
				'<div class="status">%s</div>',
				esc_html( $status )
			);
		}

		if ( $item->active ) {
			$this->render_post_count( $item );
		}
	}

	private function render_post_count( $item ) {
		$count_posts = bogo_count_posts( $item->locale, 'post' );
		$count_pages = bogo_count_posts( $item->locale, 'page' );

		if ( $count_posts ) {
			$count_posts = sprintf(
				/* translators: %s: number of posts */
				_n( '%s Post', '%s Posts', $count_posts, 'bogo' ),
				number_format_i18n( $count_posts )
			);
		}

		if ( $count_pages ) {
			$count_pages = sprintf(
				/* translators: %s: number of pages */
				_n( '%s Page', '%s Pages', $count_pages, 'bogo' ),
				number_format_i18n( $count_pages )
			);
		}

		$edit_url = add_query_arg(
			array(
				'post_status' => 'publish',
				'lang' => $item->locale,
			),
			admin_url( 'edit.php' )
		);

		echo '<ul>';

		if ( $count_posts ) {
			echo sprintf(
				'<li class="post-count"><a href="%1$s">%2$s</a></li>',
				esc_url( add_query_arg( 'post_type', 'post', $edit_url ) ),
				esc_html( $count_posts )
			);
		}

		if ( $count_pages ) {
			echo sprintf(
				'<li class="page-count"><a href="%1$s">%2$s</a></li>',
				esc_url( add_query_arg( 'post_type', 'page', $edit_url ) ),
				esc_html( $count_pages )
			);
		}

		echo '</ul>';
	}

	public function single_row( $item ) {
		echo $item->active
			? '<tr class="active">'
			: '<tr>';

		$this->single_row_columns( $item );
		echo '</tr>';
	}

	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( $column_name !== $primary ) {
			return '';
		}

		$actions = array();

		if ( 'en_US' === $item->locale or $this->can_install_language_pack() ) {
			if ( bogo_is_default_locale( $item->locale ) ) {
				// nothing
			} elseif ( $item->active ) {
				$actions = array(
					'promote' => $this->action_link( $item, 'promote' ),
					'deactivate' => $this->action_link( $item, 'deactivate' ),
				);
			} else {
				$actions = array(
					'activate' => $this->action_link( $item, 'activate' ),
				);
			}
		}

		if ( $item->active and ! bogo_is_default_locale( $item->locale ) ) {
			$actions['translate'] = $this->action_link( $item, 'translate' );
		}

		if ( 'en_US' !== $item->locale ) {
			$actions['meet'] = $this->meet_the_team_link( $item );
		}

		return $this->row_actions( $actions );
	}

	private function action_link( $item, $action ) {
		$labels = array(
			'activate' => array(
				__( 'Activate', 'bogo' ),
				sprintf(
					/* translators: %s: language name */
					__( 'Activate %s language pack', 'bogo' ),
					$item->language
				),
			),
			'deactivate' => array(
				__( 'Deactivate', 'bogo' ),
				sprintf(
					/* translators: %s: language name */
					__( 'Deactivate %s language pack', 'bogo' ),
					$item->language
				),
			),
			'promote' => array(
				__( 'Set as Site Language', 'bogo' ),
				sprintf(
					/* translators: %s: language name */
					__( 'Set %s as Site Language', 'bogo' ),
					$item->language
				),
			),
			'translate' => array(
				__( 'Translate Terms', 'bogo' ),
				sprintf(
					/* translators: %s: language name */
					__( 'Translate terms into %s', 'bogo' ),
					$item->language
				),
			),
		);

		$link = menu_page_url( 'bogo', false );

		$link = add_query_arg( array(
			'action' => $action,
			'locale' => $item->locale,
		), $link );

		$link = wp_nonce_url( $link, 'bogo-tools' );

		$link = sprintf(
			'<a href="%1$s" class="%4$s" aria-label="%3$s">%2$s</a>',
			esc_url( $link ),
			esc_html( $labels[$action][0] ),
			esc_attr( $labels[$action][1] ),
			esc_attr( $action )
		);

		return $link;
	}

	private function meet_the_team_link( $item ) {
		$link = 'https://make.wordpress.org/polyglots/teams/';

		$locale = $item->locale;
		$locale = explode( '_', $locale, 3 );
		$locale = implode( '_', array_slice( $locale, 0, 2 ) );

		$link = add_query_arg( array(
			'locale' => $locale,
		), $link );

		$link .= '#main';

		$labels = array(
			__( 'Meet the Translation Team', 'bogo' ),
			sprintf(
				/* translators: %s: language name */
				__( 'Meet the Translation Team for %s', 'bogo' ),
				$item->language
			)
		);

		$link = sprintf(
			'<a href="%1$s" aria-label="%3$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%4$s</span></a>',
			esc_url( $link ),
			esc_html( $labels[0] ),
			esc_attr( $labels[1] ),
			/* translators: accessibility text */
			esc_html( __( '(opens in a new window)', 'bogo' ) )
		);

		return $link;
	}

	private function can_install_language_pack() {
		static $can_install = null;

		if ( ! isset( $can_install ) ) {
			$can_install = wp_can_install_language_pack();
		}

		return $can_install;
	}
}

function bogo_delete_language_pack( $locale ) {
	if (
		'en_US' === $locale or
		! bogo_is_available_locale( $locale ) or
		bogo_is_default_locale( $locale ) or
		! is_dir( WP_LANG_DIR ) or
		! $files = scandir( WP_LANG_DIR )
	) {
		return false;
	}

	$prefixes = array(
		'admin-',
		'admin-network-',
		'continents-cities-',
	);

	$suffixes = array(
		'.mo',
		'.po',
		'.l10n.php',
	);

	$pattern = sprintf(
		'/^(?:%1$s)?%2$s(?:%3$s)$/',
		implode( '|', array_map( 'preg_quote', $prefixes ) ),
		preg_quote( $locale ),
		implode( '|', array_map( 'preg_quote', $suffixes ) )
	);

	foreach ( $files as $file ) {
		if ( '.' === $file[0] or is_dir( $file ) ) {
			continue;
		}

		if ( preg_match( $pattern, $file ) ) {
			$result = @unlink( path_join( WP_LANG_DIR, $file ) );

			if ( ! $result ) {
				return false;
			}
		}
	}

	return true;
}

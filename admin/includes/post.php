<?php

/* Posts List Table */

add_filter( 'manage_pages_columns', 'bogo_pages_columns' );

function bogo_pages_columns( $posts_columns ) {
	return bogo_posts_columns( $posts_columns, 'page' );
}

add_filter( 'manage_posts_columns', 'bogo_posts_columns', 10, 2 );

function bogo_posts_columns( $posts_columns, $post_type ) {
	if ( ! bogo_is_localizable_post_type( $post_type ) ) {
		return $posts_columns;
	}

	if ( ! isset( $posts_columns['locale'] ) ) {
		$posts_columns = array_merge(
			array_slice( $posts_columns, 0, 3 ),
			array( 'locale' => __( 'Locale', 'bogo' ) ),
			array_slice( $posts_columns, 3 ) );
	}

	return $posts_columns;
}

add_action( 'manage_pages_custom_column',
	'bogo_manage_posts_custom_column', 10, 2 );
add_action( 'manage_posts_custom_column',
	'bogo_manage_posts_custom_column', 10, 2 );

function bogo_manage_posts_custom_column( $column_name, $post_id ) {
	if ( 'locale' != $column_name ) {
		return;
	}

	$post_type = get_post_type( $post_id );

	if ( ! bogo_is_localizable_post_type( $post_type ) ) {
		return;
	}

	$locale = get_post_meta( $post_id, '_locale', true );

	if ( empty( $locale ) ) {
		return;
	}

	$language = bogo_get_language( $locale );

	if ( empty( $language ) ) {
		$language = $locale;
	}

	echo sprintf( '<a href="%1$s">%2$s</a>',
		esc_url( add_query_arg(
			array( 'post_type' => $post_type, 'lang' => $locale ),
			'edit.php' ) ),
		esc_html( $language ) );
}

add_action( 'restrict_manage_posts', 'bogo_restrict_manage_posts' );

function bogo_restrict_manage_posts() {
	global $post_type;

	if ( ! bogo_is_localizable_post_type( $post_type ) ) {
		return;
	}

	$available_languages = bogo_available_languages();
	$current_locale = empty( $_GET['lang'] ) ? '' : $_GET['lang'];

	echo '<select name="lang">';

	$selected = ( '' == $current_locale ) ? ' selected="selected"' : '';

	echo '<option value=""' . $selected . '>'
		. esc_html( __( 'Show all locales', 'bogo' ) ) . '</option>';

	foreach ( $available_languages as $locale => $lang ) {
		$selected = ( $locale == $current_locale ) ? ' selected="selected"' : '';

		echo '<option value="' . esc_attr( $locale ) . '"' . $selected . '>'
			. esc_html( $lang ) . '</option>';
	}

	echo '</select>' . "\n";
}

add_filter( 'post_row_actions', 'bogo_post_row_actions', 10, 2 );
add_filter( 'page_row_actions', 'bogo_post_row_actions', 10, 2 );

function bogo_post_row_actions( $actions, $post ) {
	if ( ! bogo_is_localizable_post_type( $post->post_type ) ) {
		return $actions;
	}

	$post_type_object = get_post_type_object( $post->post_type );

	if ( ! current_user_can( $post_type_object->cap->edit_post, $post->ID )
	|| 'trash' == $post->post_status ) {
		return $actions;
	}

	$user_locale = bogo_get_user_locale();
	$post_locale = bogo_get_post_locale( $post->ID );

	if ( $user_locale == $post_locale ) {
		return $actions;
	}

	if ( $translation = bogo_get_post_translation( $post, $user_locale ) ) {
		if ( empty( $translation->ID ) || $translation->ID == $post->ID ) {
			return $actions;
		}

		$text = __( 'Edit %s translation', 'bogo' );
		$edit_link = get_edit_post_link( $translation->ID );
	} else {
		$text = __( 'Translate into %s', 'bogo' );
		$edit_link = admin_url( 'post-new.php?post_type=' . $post->post_type
			. '&action=bogo-add-translation'
			. '&locale=' . $user_locale
			. '&original_post=' . $post->ID );
		$edit_link = wp_nonce_url( $edit_link, 'bogo-add-translation' );
	}

	$language = bogo_get_language( $user_locale );

	if ( empty( $language ) ) {
		$language = $user_locale;
	}

	$actions['translate'] = sprintf(
		'<a href="%1$s">%2$s</a>',
		$edit_link,
		esc_html( sprintf( $text, $language ) ) );

	return $actions;
}

add_action( 'admin_init', 'bogo_add_translation' );

function bogo_add_translation() {
	if ( empty( $_REQUEST['action'] )
	|| 'bogo-add-translation' != $_REQUEST['action'] ) {
		return;
	}

	check_admin_referer( 'bogo-add-translation' );

	$locale = isset( $_REQUEST['locale'] ) ? $_REQUEST['locale'] : '';
	$original_post = isset( $_REQUEST['original_post'] )
		? absint( $_REQUEST['original_post'] ) : 0;

	if ( ! bogo_is_available_locale( $locale ) ) {
		return;
	}

	if ( ! $original_post || ! $original_post = get_post( $original_post ) ) {
		return;
	}

	$post_type_object = get_post_type_object( $original_post->post_type );

	if ( $post_type_object
	&& current_user_can( $post_type_object->cap->edit_posts ) ) {
		$new_post_id = bogo_duplicate_post( $original_post, $locale );

		if ( $new_post_id ) {
			$redirect_to = get_edit_post_link( $new_post_id, 'raw' );
			wp_safe_redirect( $redirect_to );
			exit();
		}
	}
}

/* Single Post */

add_action( 'add_meta_boxes', 'bogo_add_l10n_meta_boxes', 10, 2 );

function bogo_add_l10n_meta_boxes( $post_type, $post ) {
	if ( ! bogo_is_localizable_post_type( $post_type ) ) {
		return;
	}

	if ( in_array( $post_type, array( 'comment', 'link' ) ) ) {
		return;
	}

	add_meta_box( 'bogol10ndiv', __( 'Language', 'bogo' ),
		'bogo_l10n_meta_box', null, 'side', 'high' );
}

function bogo_l10n_meta_box( $post ) {
	$initial = ( 'auto-draft' == $post->post_status );

	if ( $initial ) {
		$post_locale = isset( $_REQUEST['locale'] ) ? $_REQUEST['locale'] : '';

		if ( ! bogo_is_available_locale( $post_locale ) ) {
			$post_locale = bogo_get_user_locale();
		}

		$original_post = empty( $_REQUEST['original_post'] )
			? '' : $_REQUEST['original_post'];
	} else {
		$post_locale = bogo_get_post_locale( $post->ID );
		$original_post = get_post_meta( $post->ID, '_original_post', true );

		if ( empty( $original_post ) ) {
			$original_post = $post->ID;
		}
	}

	$translations = bogo_get_post_translations( $post->ID );
	$available_locales = bogo_available_locales( array(
		'exclude' => array_merge(
			array( $post_locale ),
			array_keys( (array) $translations ) ),
		'exclude_enus_if_inactive' => true,
		'current_user_can_access' => true,
	) );

?>
<div class="hidden">
<input type="hidden" name="locale" value="<?php echo esc_attr( $post_locale ); ?>" />
<input type="hidden" name="original_post" value="<?php echo esc_attr( $original_post ); ?>" />
</div>

<div class="descriptions">
<?php
	$lang = bogo_get_language( $post_locale );
	$lang = empty( $lang ) ? $post_locale : $lang;
?>
<p><strong><?php echo esc_html( __( 'Language', 'bogo' ) ); ?>:</strong>
	<?php echo esc_html( $lang ); ?></p>
</div>

<?php
	do {
		if ( ! $translations && ( $initial || empty( $available_locales ) ) ) {
			break;
		}

		echo '<div class="descriptions">';
		echo sprintf( '<p><strong>%s:</strong></p>',
			esc_html( __( 'Translations', 'bogo' ) ) );
		echo '<ul id="bogo-translations">';

		if ( $translations ) {
			foreach ( $translations as $locale => $translation ) {
				$edit_link = get_edit_post_link( $translation->ID );
				echo '<li>';

				if ( $edit_link ) {
					echo sprintf(
						'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span></a>',
						esc_url( $edit_link ),
						get_the_title( $translation->ID ),
						/* translators: accessibility text */
						esc_html( __( '(opens in a new window)', 'bogo' ) )
					);
				} else {
					echo get_the_title( $translation->ID );
				}

				$lang = bogo_get_language( $locale );
				$lang = empty( $lang ) ? $locale : $lang;
				echo ' [' . $lang . ']';
				echo '</li>';
			}
		}

		echo '</ul>';
		echo '</div>';
	} while (0);

	do {
		if ( $initial || empty( $available_locales ) ) {
			break;
		}

		echo '<div id="bogo-add-translation-actions" class="descriptions">';
		echo sprintf( '<p><strong>%s:</strong></p>',
			esc_html( __( 'Add Translation', 'bogo' ) ) );
		echo '<select id="bogo-translations-to-add">';

		foreach ( $available_locales as $locale ) {
			$lang = bogo_get_language( $locale );
			$lang = empty( $lang ) ? $locale : $lang;
			echo sprintf( '<option value="%1$s">%2$s</option>',
				esc_attr( $locale ), esc_html( $lang ) );
		}

		echo '</select>';
		echo '<p>';
		echo sprintf(
			'<button type="button" class="button" id="%1$s">%2$s</button>',
			'bogo-add-translation',
			esc_html( __( 'Add Translation', 'bogo' ) ) );
		echo '<span class="spinner"></span>';
		echo '</p>';
		echo '<div class="clear"></div>';
		echo '</div>';
	} while (0);
}

add_action( 'save_post', 'bogo_save_post', 10, 2 );

function bogo_save_post( $post_id, $post ) {
	if ( did_action( 'import_start' ) && ! did_action( 'import_end' ) ) {
		// Importing
		return;
	}

	if ( ! bogo_is_localizable_post_type( $post->post_type ) ) {
		return;
	}

	$current_locales = get_post_meta( $post_id, '_locale' );
	$locale = null;

	if ( ! empty( $current_locales ) ) {
		foreach ( $current_locales as $current_locale ) {
			if ( bogo_is_available_locale( $current_locale ) ) {
				$locale = $current_locale;
				break;
			}
		}

		if ( empty( $locale ) || 1 < count( $current_locales ) ) {
			delete_post_meta( $post_id, '_locale' );
			$current_locales = array();
		}
	}

	if ( empty( $current_locales ) ) {
		if ( bogo_is_available_locale( $locale ) ) {
			// $locale = $locale;
		} elseif ( ! empty( $_REQUEST['locale'] )
		&& bogo_is_available_locale( $_REQUEST['locale'] ) ) {
			$locale = $_REQUEST['locale'];
		} elseif ( 'auto-draft' == get_post_status( $post_id ) ) {
			$locale = bogo_get_user_locale();
		} else {
			$locale = bogo_get_default_locale();
		}

		add_post_meta( $post_id, '_locale', $locale, true );
	}

	$current_original_posts = get_post_meta( $post_id, '_original_post' );

	if ( ! empty( $current_original_posts ) ) {
		if ( 1 < count( $current_original_posts ) ) {
			delete_post_meta( $post_id, '_original_post' );
		} else {
			return;
		}
	}

	if ( ! empty( $_REQUEST['original_post'] ) ) {
		$original = get_post_meta( $_REQUEST['original_post'],
			'_original_post', true );

		if ( empty( $original ) ) {
			$original = (int) $_REQUEST['original_post'];
		}

		add_post_meta( $post_id, '_original_post', $original, true );
		return;
	}

	$original = $post_id;

	while ( 1 ) {
		$q = new WP_Query();

		$posts = $q->query( array(
			'bogo_suppress_locale_query' => true,
			'posts_per_page' => 1,
			'post_status' => 'any',
			'post_type' => $post->post_type,
			'meta_key' => '_original_post',
			'meta_value' => $original ) );

		if ( empty( $posts ) ) {
			add_post_meta( $post_id, '_original_post', $original, true );
			return;
		}

		$original += 1;
	}
}

add_filter( 'wp_unique_post_slug', 'bogo_unique_post_slug', 10, 6 );

function bogo_unique_post_slug( $slug, $post_id, $status, $type, $parent, $original ) {
	global $wp_rewrite;

	if ( ! bogo_is_localizable_post_type( $type ) ) {
		return $slug;
	}

	$feeds = is_array( $wp_rewrite->feeds ) ? $wp_rewrite->feeds : array();

	if ( in_array( $original, $feeds ) ) {
		return $slug;
	}

	$locale = bogo_get_post_locale( $post_id );

	if ( empty( $locale ) ) {
		return $slug;
	}

	$args = array(
		'posts_per_page' => 1,
		'post__not_in' => array( $post_id ),
		'post_type' => $type,
		'name' => $original,
		'lang' => $locale,
	);

	$hierarchical = in_array( $type,
		get_post_types( array( 'hierarchical' => true ) ) );

	if ( $hierarchical ) {
		if ( preg_match( "@^($wp_rewrite->pagination_base)?\d+$@", $original ) ) {
			return $slug;
		}

		$args['post_parent'] = $parent;
	}

	$q = new WP_Query();
	$posts = $q->query( $args );

	if ( empty( $posts ) ) {
		$slug = $original;
	}

	return $slug;
}

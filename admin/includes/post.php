<?php

/* Posts List Table */

add_filter( 'manage_pages_columns', 'bogo_pages_columns', 10, 1 );

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
			array_slice( $posts_columns, 3 )
		);
	}

	return $posts_columns;
}

add_action( 'manage_pages_custom_column',
	'bogo_manage_posts_custom_column', 10, 2
);

add_action( 'manage_posts_custom_column',
	'bogo_manage_posts_custom_column', 10, 2
);

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
		esc_url(
			add_query_arg( array(
				'post_type' => $post_type,
				'lang' => $locale,
			), 'edit.php' )
		),
		esc_html( $language )
	);
}

add_action( 'restrict_manage_posts', 'bogo_restrict_manage_posts', 10, 2 );

function bogo_restrict_manage_posts( $post_type, $which ) {
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
	if ( ! bogo_is_localizable_post_type( $post->post_type )
	or 'trash' === $post->post_status ) {
		return $actions;
	}

	$post_type_object = get_post_type_object( $post->post_type );

	if ( ! current_user_can( $post_type_object->cap->edit_posts ) ) {
		return $actions;
	}

	$user_locale = bogo_get_user_locale();
	$post_locale = bogo_get_post_locale( $post->ID );

	if ( $user_locale == $post_locale ) {
		return $actions;
	}

	if ( $translation = bogo_get_post_translation( $post, $user_locale ) ) {
		if ( empty( $translation->ID )
		or $translation->ID === $post->ID ) {
			return $actions;
		}

		$text = __( 'Edit %s translation', 'bogo' );
		$edit_link = get_edit_post_link( $translation->ID );
	} else {
		$text = __( 'Translate into %s', 'bogo' );
		$edit_link = admin_url( 'post-new.php?post_type=' . $post->post_type
			. '&action=bogo-add-translation'
			. '&locale=' . $user_locale
			. '&original_post=' . $post->ID
		);
		$edit_link = wp_nonce_url( $edit_link, 'bogo-add-translation' );
	}

	$language = bogo_get_language( $user_locale );

	if ( empty( $language ) ) {
		$language = $user_locale;
	}

	$actions['translate'] = sprintf(
		'<a href="%1$s">%2$s</a>',
		$edit_link,
		esc_html( sprintf( $text, $language ) )
	);

	return $actions;
}

add_action( 'admin_init', 'bogo_add_translation', 10, 0 );

function bogo_add_translation() {
	if ( empty( $_REQUEST['action'] )
	or 'bogo-add-translation' != $_REQUEST['action'] ) {
		return;
	}

	check_admin_referer( 'bogo-add-translation' );

	$locale = isset( $_REQUEST['locale'] ) ? $_REQUEST['locale'] : '';
	$original_post = isset( $_REQUEST['original_post'] )
		? absint( $_REQUEST['original_post'] ) : 0;

	if ( ! bogo_is_available_locale( $locale ) ) {
		return;
	}

	if ( ! $original_post
	or ! $original_post = get_post( $original_post ) ) {
		return;
	}

	$post_type_object = get_post_type_object( $original_post->post_type );

	if ( $post_type_object
	and current_user_can( $post_type_object->cap->edit_posts ) ) {
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
		'bogo_l10n_meta_box', null, 'side', 'high',
		array(
			'__back_compat_meta_box' => true,
		)
	);
}

function bogo_l10n_meta_box( $post ) {
	$initial = ( 'auto-draft' == $post->post_status );

	if ( $initial ) {
		$post_locale = bogo_get_user_locale();
	} else {
		$post_locale = bogo_get_post_locale( $post->ID );
	}

	$translations = bogo_get_post_translations( $post->ID );
	$available_languages = bogo_available_languages( array(
		'current_user_can_access' => true,
	) );

?>

<div class="descriptions">
<?php
	if ( isset( $available_languages[$post_locale] ) ) {
		$lang = $available_languages[$post_locale];
	} else {
		$lang = $post_locale;
	}

	unset( $available_languages[$post_locale] );
?>
<p>
	<strong><?php echo esc_html( __( 'Language', 'bogo' ) ); ?>:</strong>
	<?php echo esc_html( $lang ); ?>
</p>
</div>

<?php
	echo '<div class="descriptions">';
	echo sprintf( '<p><strong>%s:</strong></p>',
		esc_html( __( 'Translations', 'bogo' ) )
	);

	echo '<ul id="bogo-translations">';

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

		if ( isset( $available_languages[$locale] ) ) {
			$lang = $available_languages[$locale];
		} else {
			$lang = $locale;
		}

		echo ' [' . $lang . ']';
		echo '</li>';

		// make it unavailable for select options
		unset( $available_languages[$locale] );
	}

	echo '</ul>';
	echo '</div>';

	if ( $initial or empty( $available_languages ) ) {
		return;
	}

	echo '<div id="bogo-add-translation-actions" class="descriptions">';
	echo sprintf( '<p><strong>%s:</strong></p>',
		esc_html( __( 'Add Translation', 'bogo' ) )
	);
	echo '<select id="bogo-translations-to-add">';

	foreach ( $available_languages as $locale => $lang ) {
		if ( isset( $translations[$locale] ) ) {
			continue;
		}

		echo sprintf( '<option value="%1$s">%2$s</option>',
			esc_attr( $locale ), esc_html( $lang )
		);
	}

	echo '</select>';
	echo '<p>';
	echo sprintf(
		'<button type="button" class="button" id="%1$s">%2$s</button>',
		'bogo-add-translation',
		esc_html( __( 'Add Translation', 'bogo' ) )
	);
	echo '<span class="spinner"></span>';
	echo '</p>';
	echo '<div class="clear"></div>';
	echo '</div>';
}

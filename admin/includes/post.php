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
	if ( 'locale' !== $column_name ) {
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

	$selected = ( '' === $current_locale ) ? ' selected="selected"' : '';

	echo '<option value=""' . $selected . '>'
		. esc_html( __( 'Show all locales', 'bogo' ) ) . '</option>';

	foreach ( $available_languages as $locale => $lang ) {
		$selected = ( $locale === $current_locale ) ? ' selected="selected"' : '';

		echo '<option value="' . esc_attr( $locale ) . '"' . $selected . '>'
			. esc_html( $lang ) . '</option>';
	}

	echo '</select>' . "\n";
}


add_filter( 'post_row_actions', 'bogo_post_row_actions', 10, 2 );
add_filter( 'page_row_actions', 'bogo_post_row_actions', 10, 2 );

function bogo_post_row_actions( $actions, $post ) {
	if (
		! bogo_is_localizable_post_type( $post->post_type ) or
		'trash' === $post->post_status
	) {
		return $actions;
	}

	$post_type_object = get_post_type_object( $post->post_type );

	if ( ! current_user_can( $post_type_object->cap->edit_posts ) ) {
		return $actions;
	}

	$user_locale = bogo_get_user_locale();
	$post_locale = bogo_get_post_locale( $post->ID );

	if ( $user_locale === $post_locale ) {
		return $actions;
	}

	if ( $translation = bogo_get_post_translation( $post, $user_locale ) ) {
		if ( empty( $translation->ID ) or $translation->ID === $post->ID ) {
			return $actions;
		}

		/* translators: %s: Language name */
		$text = __( 'Edit %s translation', 'bogo' );

		$edit_link = get_edit_post_link( $translation->ID );
	} else {
		/* translators: %s: Language name */
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
	if (
		empty( $_REQUEST['action'] ) or
		'bogo-add-translation' !== $_REQUEST['action']
	) {
		return;
	}

	check_admin_referer( 'bogo-add-translation' );

	$locale = trim( $_REQUEST['locale'] ?? '' );
	$original_post = absint( $_REQUEST['original_post'] ?? 0 );

	if ( ! bogo_is_available_locale( $locale ) ) {
		return;
	}

	if ( ! $original_post or ! $original_post = get_post( $original_post ) ) {
		return;
	}

	$post_type_object = get_post_type_object( $original_post->post_type );

	if (
		$post_type_object and
		current_user_can( $post_type_object->cap->edit_posts )
	) {
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
	$initial = ( 'auto-draft' === $post->post_status );

	if ( $initial ) {
		$post_locale = bogo_get_user_locale();
	} else {
		$post_locale = bogo_get_post_locale( $post->ID );
	}

	$translations = bogo_get_post_translations( $post->ID );

	$available_languages = bogo_available_languages( array(
		'current_user_can_access' => true,
	) );

	$post_language = $available_languages[$post_locale] ?? $post_locale;

	unset( $available_languages[$post_locale] );

?>

<div class="descriptions">
<p><?php

	echo wp_kses_data( sprintf(
		'<strong>%1$s</strong> %2$s',
		__( 'Language:', 'bogo' ),
		$post_language
	) );

?></p>
</div>

<div class="descriptions">
<p>
	<strong><?php echo wp_kses_data( __( 'Translations:', 'bogo' ) ); ?></strong>
</p>

<ul id="bogo-translations">
<?php

	foreach ( $translations as $locale => $translation ) {
		$li_content = get_the_title( $translation->ID );

		if ( $edit_link = get_edit_post_link( $translation->ID ) ) {
			$li_content = sprintf(
				'<a %1$s>%2$s<span class="screen-reader-text">%3$s</span></a>',
				bogo_format_atts( array(
					'href' => esc_url( $edit_link ),
					'target' => '_blank',
					'rel' => 'noopener noreferrer',
				) ),
				$li_content,
				/* translators: accessibility text */
				esc_html( __( '(opens in a new window)', 'bogo' ) )
			);
		}

		$li_content .= sprintf(
			' [%s]',
			$available_languages[$locale] ?? $locale
		);

		echo sprintf( '<li>%s</li>', $li_content );
	}

?>
</ul>
</div>

<?php

	$available_languages = array_diff_key( $available_languages, $translations );

	if ( ! $initial and $available_languages ) {

?>

<div class="descriptions" id="bogo-add-translation-actions">
<p>
	<strong><?php echo wp_kses_data( __( 'Add Translation:', 'bogo' ) ); ?></strong>
</p>

<?php

		foreach ( $available_languages as $locale => $lang ) {
			echo sprintf(
				'<p><button %1$s>%2$s</button> <span class="spinner"></span></p>',
				bogo_format_atts( array(
					'type' => 'button',
					'class' => 'button',
				) ),
				sprintf(
					/* translators: %s: Language name. */
					__( 'Add %s translation', 'bogo' ),
					esc_html( $lang )
				)
			);
		}

?>
<div class="clear"></div>
</div>
<?php

	}
}

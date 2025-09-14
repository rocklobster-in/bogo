<?php

add_action( 'personal_options_update', 'bogo_update_user_option', 10, 1 );
add_action( 'edit_user_profile_update', 'bogo_update_user_option', 10, 1 );

function bogo_update_user_option( $user_id ) {
	global $wpdb;

	$meta_key = $wpdb->get_blog_prefix() . 'accessible_locale';

	if ( ! empty( $_POST['setting_bogo_accessible_locales'] ) ) {
		delete_user_meta( $user_id, $meta_key );

		if ( isset( $_POST['bogo_accessible_locales'] ) ) {
			$locales = (array) $_POST['bogo_accessible_locales'];
			$locales = bogo_filter_locales( $locales );

			foreach ( $locales as $locale ) {
				add_user_meta( $user_id, $meta_key, $locale );
			}
		}

		if ( ! metadata_exists( 'user', $user_id, $meta_key ) ) {
			add_user_meta( $user_id, $meta_key, 'zxx' );
			// zxx is a special code in ISO 639-2
		}
	}

	if ( isset( $_POST['bogo_own_locale'] ) ) {
		$locale = trim( $_POST['bogo_own_locale'] );

		if ( bogo_is_available_locale( $locale ) ) {
			update_user_option( $user_id, 'locale', $locale, true );
		}
	}
}


add_action( 'personal_options', 'bogo_set_locale_options', 10, 1 );

function bogo_set_locale_options( $profileuser ) {
	if ( is_network_admin() or IS_PROFILE_PAGE ) {
		return;
	}

	if ( ! user_can( $profileuser, 'bogo_access_all_locales' ) ) {
		bogo_set_accessible_locales( $profileuser );
	}
}


function bogo_set_accessible_locales( $profileuser ) {
	$available_languages = bogo_available_languages( array(
		'orderby' => 'value',
	) );

	$accessible_locales = bogo_get_user_accessible_locales( $profileuser->ID );

?>

<!-- Bogo plugin -->
<tr>
<th scope="row"><?php echo esc_html( __( 'Locale', 'bogo' ) ); ?></th>
<td>
<input type="hidden" name="setting_bogo_accessible_locales" value="1" />
<span class="description"><?php echo esc_html( __( 'This user is allowed to access the following locales:', 'bogo' ) ); ?></span><br />
<fieldset class="bogo-locale-options">

<?php

	foreach ( $available_languages as $locale => $language ) {
		$checked = in_array( $locale, $accessible_locales );
		$id_attr = sprintf( 'bogo_accessible_locale-%s', $locale );

		$checkbox = sprintf(
			'<label %1$s><input %2$s />%3$s</label>',
			bogo_format_atts( array(
				'class' => 'bogo-locale-option' . ( $checked ? ' checked' : '' ),
				'for' => $id_attr,
			) ),
			bogo_format_atts( array(
				'type' => 'checkbox',
				'id' => $id_attr,
				'name' => 'bogo_accessible_locales[]',
				'value' => $locale,
				'checked' => $checked,
			) ),
			$language
		);

		echo wp_kses( $checkbox, 'bogo_form_inside' ) . "\n";
	}

?>
</fieldset>
</td>
</tr>

<?php
}

<?php

add_action( 'personal_options_update', 'bogo_update_user_option' );
add_action( 'edit_user_profile_update', 'bogo_update_user_option' );

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

add_action( 'personal_options', 'bogo_set_locale_options' );

function bogo_set_locale_options( $profileuser ) {
	if ( is_network_admin() ) {
		return;
	}

	if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
		bogo_select_own_locale( $profileuser );
	} elseif ( ! user_can( $profileuser, 'bogo_access_all_locales' ) ) {
		bogo_set_accessible_locales( $profileuser );
	}
}

function bogo_set_accessible_locales( $profileuser ) {
	$available_languages = bogo_available_languages( array(
		'exclude_enus_if_inactive' => true,
		'orderby' => 'value',
	) );
	$accessible_locales = bogo_get_user_accessible_locales( $profileuser->ID );

	if ( empty( $accessible_locales ) ) {
		$accessible_locales = array_keys( $available_languages );
	} else {
		$accessible_locales = bogo_filter_locales( $accessible_locales );
	}

?>

<!-- Bogo plugin -->
<tr>
<th scope="row"><?php echo esc_html( __( 'Locale', 'bogo' ) ); ?></th>
<td>
<input type="hidden" name="setting_bogo_accessible_locales" value="1" />
<span class="description"><?php echo esc_html( __( 'This user is allowed to access the following locales:', 'bogo' ) ); ?></span><br />
<fieldset class="bogo-locale-options">

<?php
	foreach ( $available_languages as $locale => $language ) :
		$checked = in_array( $locale, $accessible_locales );
		$id_attr = 'bogo_accessible_locale-' . $locale;
?>
<label class="bogo-locale-option<?php echo $checked ? ' checked' : ''; ?>" for="<?php echo $id_attr; ?>">
<input type="checkbox" id="<?php echo $id_attr; ?>" name="bogo_accessible_locales[]" value="<?php echo esc_attr( $locale ); ?>"<?php echo $checked ? ' checked="checked"' : ''; ?> /><?php echo esc_html( $language ); ?>
</label>
<?php
	endforeach;
?>
</fieldset>
</td>
</tr>

<?php
}

function bogo_select_own_locale( $profileuser ) {
	if ( ! empty( $profileuser->locale ) ) { // WordPress 4.7+
		return;
	}

	$available_languages = bogo_available_languages( array(
		'exclude_enus_if_inactive' => true,
		'orderby' => 'value',
		'current_user_can_access' => true,
	) );

	$selected = bogo_get_user_locale( $profileuser->ID );

?>

<!-- Bogo plugin -->
<tr>
<th scope="row"><?php echo esc_html( __( 'Locale', 'bogo' ) ); ?></th>
<td>
<select name="bogo_own_locale">
<?php foreach ( $available_languages as $locale => $lang ) : ?>
<option value="<?php echo esc_attr( $locale ); ?>" <?php selected( $locale, $selected ); ?>><?php echo esc_html( $lang ); ?></option>
<?php endforeach; ?>
</select>
</td>
</tr>

<?php
}

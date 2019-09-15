<?php

add_action( 'in_widget_form', 'bogo_in_widget_form', 10, 3 );

function bogo_in_widget_form( $widget, $return, $instance ) {
	$available_languages = bogo_available_languages( array(
		'exclude_enus_if_inactive' => true,
		'orderby' => 'value',
	) );

	if ( empty( $available_languages ) ) {
		return;
	}

	$return = null;

	$selected_languages = isset( $instance['bogo_locales'] )
		? (array) $instance['bogo_locales']
		: array_keys( $available_languages );

?>
<fieldset class="bogo-locale-options">
<legend><?php echo esc_html( __( 'Displayed on pages in', 'bogo' ) ); ?></legend>
<?php foreach ( $available_languages as $locale => $language ) :
	$checked = in_array( $locale, $selected_languages );
	$id_attr = $widget->get_field_id( 'bogo_locales' ) . '-' . $locale;
?>
<label class="bogo-locale-option<?php echo $checked ? ' checked' : ''; ?>" for="<?php echo $id_attr; ?>">
<input type="checkbox" id="<?php echo $id_attr; ?>" name="<?php echo $widget->get_field_name( 'bogo_locales' ); ?>[]" value="<?php echo esc_attr( $locale ); ?>"<?php echo $checked ? ' checked="checked"' : ''; ?> /><?php echo esc_html( $language ); ?>
</label>
<?php endforeach; ?>
</fieldset>
<?php
}

add_filter( 'widget_update_callback', 'bogo_widget_update_callback', 10, 4 );

function bogo_widget_update_callback( $instance, $new_instance, $old_instance, $widget ) {
	if ( isset( $new_instance['bogo_locales'] ) && is_array( $new_instance['bogo_locales'] ) ) {
		$instance['bogo_locales'] = $new_instance['bogo_locales'];
	} else {
		$instance['bogo_locales'] = array();
	}

	return $instance;
}

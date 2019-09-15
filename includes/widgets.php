<?php

/* Language Switcher Widget */

add_action( 'widgets_init', 'bogo_widgets_init' );

function bogo_widgets_init() {
	register_widget( 'Bogo_Widget_Language_Switcher' );
}

class Bogo_Widget_Language_Switcher extends WP_Widget {

	function __construct() {
		$widget_ops = array(
			'description' => __( 'Language switcher widget by Bogo plugin', 'bogo' ),
		);

		$control_ops = array();

		WP_Widget::__construct( 'bogo_language_switcher',
			__( 'Language Switcher', 'bogo' ),
			$widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title',
			empty( $instance['title'] )
				? __( 'Language Switcher', 'bogo' )
				: $instance['title'],
			$instance, $this->id_base );

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		echo bogo_language_switcher();

		echo $args['after_widget'];
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags( $instance['title'] );

		echo '<p><label for="' . $this->get_field_id( 'title' ) . '">' . esc_html( __( 'Title:', 'bogo' ) ) . '</label> <input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . esc_attr( $title ) . '" /></p>';
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '' ) );
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

}

/* Locale Option */

add_filter( 'widget_display_callback', 'bogo_widget_display_callback', 10, 3 );

function bogo_widget_display_callback( $instance, $widget, $args ) {
	if ( isset( $instance['bogo_locales'] ) ) {
		$locale = get_locale();

		if ( ! in_array( $locale, (array) $instance['bogo_locales'] ) ) {
			$instance = false;
		}
	}

	return $instance;
}

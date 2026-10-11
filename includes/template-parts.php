<?php

add_filter( 'render_block_data', 'bogo_localize_template_part', 10, 1 );

function bogo_localize_template_part( $block ) {
	if (
		is_admin() ||
		( function_exists( 'wp_is_serving_rest_request' ) && wp_is_serving_rest_request() ) ||
		'core/template-part' !== ( $block['blockName'] ?? '' ) ||
		! function_exists( 'bogo_get_default_locale' )
	) {
		return $block;
	}

	$locale = get_locale();
	if ( bogo_is_default_locale( $locale ) || ! bogo_is_available_locale( $locale ) ) {
		return $block;
	}

	$slug = $block['attrs']['slug'] ?? '';
	if ( ! $slug ) {
		return $block;
	}

	$localized_slug = apply_filters(
		'bogo_localized_template_part_slug',
		$slug . '-' . bogo_lang_slug( $locale ),
		$slug,
		$locale,
		$block
	);
	$theme = $block['attrs']['theme'] ?? get_stylesheet();

	if ( get_block_template( $theme . '//' . $localized_slug, 'wp_template_part' ) ) {
		$block['attrs']['slug'] = $localized_slug;
	}

	return $block;
}

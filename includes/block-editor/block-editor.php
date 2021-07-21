<?php

add_action(
	'enqueue_block_editor_assets',
	'bogo_init_block_editor_assets',
	10, 0
);

function bogo_init_block_editor_assets() {
	$assets = array();

	$asset_file = path_join(
		BOGO_PLUGIN_DIR,
		'includes/block-editor/index.asset.php'
	);

	if ( file_exists( $asset_file ) ) {
		$assets = include( $asset_file );
	}

	$assets = wp_parse_args( $assets, array(
		'src' => plugins_url(
			'includes/block-editor/index.js',
			BOGO_PLUGIN_BASENAME
		),
		'dependencies' => array(
			'wp-components',
			'wp-data',
			'wp-edit-post',
			'wp-element',
			'wp-plugins',
			'wp-i18n',
		),
		'version' => BOGO_VERSION,
	) );

	wp_register_script(
		'bogo-block-editor',
		$assets['src'],
		$assets['dependencies'],
		$assets['version']
	);

	wp_set_script_translations(
		'bogo-block-editor',
		'bogo'
	);
}

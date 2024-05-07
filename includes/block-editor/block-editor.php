<?php

add_action(
	'init',
	'bogo_register_language_switcher_block',
	10, 0
);

function bogo_register_language_switcher_block() {
	register_block_type(
		path_join( BOGO_PLUGIN_DIR, 'includes/block-editor/language-switcher' )
	);
}


add_action(
	'enqueue_block_editor_assets',
	'bogo_init_block_editor_assets',
	10, 0
);

function bogo_init_block_editor_assets() {
	$assets = array();

	$asset_file = path_join(
		BOGO_PLUGIN_DIR,
		'includes/block-editor/language-panel/index.asset.php'
	);

	if ( file_exists( $asset_file ) ) {
		$assets = include( $asset_file );
	}

	$assets = wp_parse_args( $assets, array(
		'dependencies' => array(
			'react',
			'wp-api-fetch',
			'wp-components',
			'wp-data',
			'wp-edit-post',
			'wp-element',
			'wp-i18n',
			'wp-plugins',
			'wp-url',
		),
		'version' => BOGO_VERSION,
	) );

	wp_register_script(
		'bogo-block-editor',
		plugins_url(
			'includes/block-editor/language-panel/index.js',
			BOGO_PLUGIN_BASENAME
		),
		$assets['dependencies'],
		$assets['version']
	);

	wp_set_script_translations(
		'bogo-block-editor',
		'bogo'
	);
}

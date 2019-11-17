<?php

add_action( 'enqueue_block_editor_assets', 'bogo_enqueue_block_editor_assets' );

function bogo_enqueue_block_editor_assets() {
	wp_enqueue_script(
		'bogo-block-editor',
		plugins_url( 'includes/js/block-editor.js', BOGO_PLUGIN_BASENAME ),
		array( 'wp-blocks' ),
		BOGO_VERSION
	);
}

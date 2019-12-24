<?php

add_action( 'enqueue_block_editor_assets', 'bogo_enqueue_block_editor_assets' );

function bogo_enqueue_block_editor_assets() {
	wp_enqueue_script(
		'bogo-block-editor',
		plugins_url( 'includes/js/block-editor.js', BOGO_PLUGIN_BASENAME ),
		array( 'wp-blocks', 'wp-element', 'wp-polyfill' ),
		sprintf( '%s-%s', BOGO_VERSION, '1fa563db2aad265f187e9c9992d63878' )
	);
}

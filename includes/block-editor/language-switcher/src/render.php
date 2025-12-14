<div <?php echo get_block_wrapper_attributes(); ?>>
<?php
switch ( $attributes['view'] ) {
	case 'suggestion':
		return bogo_language_suggestion( array( 'echo' => true ) );
	case 'list':
	default:
		return bogo_language_switcher( array( 'echo' => true ) );
}
?>
</div>

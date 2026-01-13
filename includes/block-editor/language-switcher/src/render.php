<div <?php echo get_block_wrapper_attributes(); ?>>
<?php
switch ( $attributes['view'] ) {
	case 'suggestion':
		bogo_language_suggestion( array( 'echo' => true ) );
		break;
	case 'list':
	default:
		bogo_language_switcher( array( 'echo' => true ) );
}
?>
</div><!-- end language switcher block -->

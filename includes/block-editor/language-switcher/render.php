<?php
switch ( $attributes['view'] ) {
	case 'suggestion':
		return bogo_language_suggestion( array( 'echo' => true ) );
	case 'list':
	default:
		return bogo_language_switcher( array( 'echo' => true ) );
}

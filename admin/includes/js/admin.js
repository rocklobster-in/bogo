( function( $ ) {

	'use strict';

	if ( typeof bogo === 'undefined' || bogo === null ) {
		return;
	}

	$( function() {
		if ( 'bogo-texts' == bogo.pagenow ) {
			$( '#select-locale' ).change( function() {
				location = 'admin.php?page=bogo-texts&locale=' + $( this ).val();
			} );
		}
	} );

} )( jQuery );

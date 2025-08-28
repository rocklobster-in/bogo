( function( $ ) {

	'use strict';

	if ( typeof bogo === 'undefined' || bogo === null ) {
		return;
	}

	$( function() {
		if ( 'bogo-texts' == bogo.pagenow ) {
			$( window ).on( 'beforeunload', function( event ) {
				var changed = false;

				$( '#bogo-terms-translation :text' ).each( function() {
					if ( this.defaultValue != $( this ).val() ) {
						changed = true;
					}
				} );

				if ( changed ) {
					event.returnValue = bogo.l10n.saveAlert;
					return bogo.l10n.saveAlert;
				}
			} );

			$( '#bogo-terms-translation' ).submit( function() {
				$( window ).off( 'beforeunload' );
			} );

			$( '#select-locale' ).change( function() {
				location = 'admin.php?page=bogo-texts&locale=' + $( this ).val();
			} );
		}
	} );

} )( jQuery );

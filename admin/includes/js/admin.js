( function( $ ) {

	'use strict';

	if ( typeof bogo === 'undefined' || bogo === null ) {
		return;
	}

	bogo.langName = function( locale ) {
		return bogo.availableLanguages[ locale ] || '';
	};

	$( function() {
		$( 'body.options-general-php select#WPLANG' ).each( function() {
			$( this ).find( 'option[selected="selected"]' ).removeAttr( 'selected' );
			var val = bogo.defaultLocale || 'en_US';
			val = ( 'en_US' == val ? '' : val );
			$( this ).find( 'option[value="' + val + '"]' ).first().attr( 'selected', 'selected' );
		} );
	} );

	$( function() {
		$( '#bogo-add-translation' ).click( function() {
			if ( ! bogo.post_id ) {
				return;
			}

			var locale = $( '#bogo-translations-to-add' ).val();
			var rest_url = bogo.apiSettings.getRoute(
				'/posts/' + bogo.post_id + '/translations/' + locale );
			$( '#bogo-add-translation' ).next( '.spinner' )
				.css( 'visibility', 'visible' );

			$.ajax( {
				type: 'POST',
				url: rest_url,
				beforeSend: function( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', bogo.apiSettings.nonce );
				}
			} ).done( function( response ) {
				var post = response[ locale ];

				if ( ! post ) {
					return;
				}

				var $added = $( '<a></a>' ).attr( {
					href: post.edit_link,
					target: '_blank',
					rel: 'noopener noreferrer'
				} ).html( function() {
					var output = post.title.rendered;
					output += ' <span class="screen-reader-text">'
						+ bogo.screenReaderText.targetBlank + '</span>';
					return output;
				} );

				$added = $( '<li></li>' ).append( $added ).append(
					' [' + post.lang.name + ']' );
				$( '#bogo-translations' ).append( $added );

				$( '#bogo-translations-to-add option[value="' + locale + '"]' ).detach();

				if ( $( '#bogo-translations-to-add option' ).length < 1 ) {
					$( '#bogo-add-translation-actions' ).detach();
				}
			} ).always( function() {
				$( '#bogo-add-translation' ).next( '.spinner' ).css( 'visibility', 'hidden' );
			} );
		} );
	} );

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
					event.returnValue = bogo.saveAlert;
					return bogo.saveAlert;
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

	bogo.apiSettings.getRoute = function( path ) {
		var url = bogo.apiSettings.root;

		url = url.replace(
			bogo.apiSettings.namespace,
			bogo.apiSettings.namespace + path );

		return url;
	};

} )( jQuery );

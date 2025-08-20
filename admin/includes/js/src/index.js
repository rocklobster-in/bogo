import { __ } from '@wordpress/i18n';

document.addEventListener( 'DOMContentLoaded', event => {

	const langName = locale => bogo.availableLanguages[ locale ]?.name;

	const defaultLocale = bogo.defaultLocale ?? 'en_US';

	// Settings > General > Site Language
	document.querySelectorAll(
		'body.options-general-php select#WPLANG option'
	).forEach( option => {
		if ( 'en_US' === defaultLocale ) {
			option.selected = ( '' === option.value );
		} else {
			option.selected = ( defaultLocale === option.value );
		}
	} );

} );

import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

document.addEventListener( 'DOMContentLoaded', event => {

	const langName = locale => bogo.availableLanguages[ locale ]?.name;

	const defaultLocale = bogo.defaultLocale ?? 'en_US';

	document.querySelectorAll(
		'#bogo-add-translation-actions button'
	).forEach( button => {
		const currentPostId = bogo.currentPost?.postId;

		if ( ! currentPostId ) {
			return;
		}

		const spinner = button.parentElement?.querySelector( '.spinner' );
		const locale = button.dataset.locale;

		button.addEventListener( 'click', event => {
			spinner?.classList.add( 'is-active' );

			apiFetch( {
				path: `/bogo/v1/posts/${ currentPostId }/translations/${ locale }`,
				method: 'POST',
			} ).then( ( response ) => {
				button.setAttribute( 'disabled', 'disabled' );
			} ).finally( ( response ) => {
				spinner?.classList.remove( 'is-active' );
			} );
		} );
	} );

} );

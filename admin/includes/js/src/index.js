import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

document.addEventListener( 'DOMContentLoaded', event => {

	const langName = locale => bogo.availableLanguages[ locale ]?.name ?? locale;

	document.querySelectorAll(
		'#bogo-add-translation-actions button'
	).forEach( button => {
		const currentPostId = bogo.currentPost?.postId;

		if ( ! currentPostId ) {
			return;
		}

		const spinner = button.parentElement?.querySelector( '.spinner' );
		const locale = button.dataset.locale;

		const addTranslationToList = ( locale, post ) => {
			const itemAdded = document.createElement( 'li' );

			const anchor = document.createElement( 'a' );

			anchor.setAttribute( 'href', post.edit_link );
			anchor.setAttribute( 'target', '_blank' );
			anchor.insertAdjacentText( 'afterbegin', post.title.rendered );

			const screenReaderText = document.createElement( 'span' );

			screenReaderText.classList.add( 'screen-reader-text' );

			screenReaderText.insertAdjacentText(
				'afterbegin',
				/* translators: accessibility text */
				__( '(opens in a new window)', 'bogo' )
			);

			anchor.insertAdjacentElement( 'beforeend', screenReaderText );

			itemAdded.appendChild( anchor );
			itemAdded.insertAdjacentText( 'beforeend', ` [${ langName( locale ) }]` );

			document.querySelector( '#bogo-translations' )?.appendChild( itemAdded );
		};

		button.addEventListener( 'click', event => {
			spinner?.classList.add( 'is-active' );

			apiFetch( {
				path: `/bogo/v1/posts/${ currentPostId }/translations/${ locale }`,
				method: 'POST',
			} ).then( ( response ) => {
				button.setAttribute( 'disabled', 'disabled' );

				const postAdded = response[ locale ];

				addTranslationToList( locale, postAdded );
			} ).finally( ( response ) => {
				spinner?.classList.remove( 'is-active' );
			} );
		} );
	} );

} );

const termsTranslationForm = document.querySelector(
	'#bogo-terms-translation'
);

if ( termsTranslationForm ) {
	const beforeUnloadHandler = event => {
		const translations = termsTranslationForm.querySelectorAll(
			'input.translation-text'
		);

		const changed = Array.from( translations ).some( elm => {
			return elm.defaultValue !== elm.value;
		} );

		if ( changed ) {
			event.preventDefault();
		}
	};

	window.addEventListener( 'beforeunload', beforeUnloadHandler );

	termsTranslationForm.addEventListener( 'submit', event => {
		window.removeEventListener( 'beforeunload', beforeUnloadHandler );
	} );
}

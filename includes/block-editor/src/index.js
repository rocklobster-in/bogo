import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { PanelRow, Button, ExternalLink, Spinner } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { dispatch, useSelect } from '@wordpress/data';
import { addQueryArgs, hasQueryArg } from '@wordpress/url';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

function LanguagePanel() {
	const currentPost = useSelect( ( select ) => {
		return Object.assign( {},
			select( 'core/editor' ).getCurrentPost(),
			bogo.currentPost
		);
	} );

	if ( -1 == bogo.localizablePostTypes.indexOf( currentPost.type ) ) {
		return( <></> );
	}

	const [ translations, setTranslations ]
		= useState( currentPost.translations );

	const PostLanguage = () => {
		return(
			<PanelRow>
				<span>{ __( 'Language', 'bogo' ) }</span>
				<div>{ getLanguage( currentPost.locale ) }</div>
			</PanelRow>
		);
	}

	const Translations = () => {
		const listItems = [];

		Object.entries( translations ).forEach( ( [ key, value ] ) => {
			if ( value.editLink && value.postTitle ) {
				listItems.push(
					<li key={ key }>
						<ExternalLink href={ value.editLink }>
							{ value.postTitle }
						</ExternalLink>
						<br />
						<em>{ getLanguage( key ) }</em>
					</li>
				);
			} else if ( value.postTitle ) {
				listItems.push(
					<li key={ key }>
						{ value.postTitle }
						<br />
						<em>{ getLanguage( key ) }</em>
					</li>
				);
			}
		} );

		const ListItems = ( props ) => {
			if ( props.listItems.length ) {
				return(
					<ul>{ props.listItems }</ul>
				);
			} else {
				return(
					<em>{ __( 'None', 'bogo' ) }</em>
				);
			}
		}

		return(
			<PanelRow>
				<span>{ __( 'Translations', 'bogo' ) }</span>
				<ListItems listItems={ listItems } />
			</PanelRow>
		);
	}

	const AddTranslation = () => {
		const addTranslation = ( locale ) => {
			const translationsAlt = Object.assign( {}, translations );

			translationsAlt[ locale ] = {
				creating: true,
			};

			setTranslations( translationsAlt );

			apiFetch( {
				path: '/bogo/v1/posts/' + currentPost.id +
					'/translations/' + locale,
				method: 'POST',
			} ).then( ( response ) => {
				const translationsAlt = Object.assign( {}, translations );

				translationsAlt[ locale ] = {
					postId: response[ locale ].id,
					postTitle: response[ locale ].title.raw,
					editLink: response[ locale ].edit_link,
					creating: false,
				};

				setTranslations( translationsAlt );

				dispatch( 'core/notices' ).createInfoNotice(
					__( "Translation post created.", 'bogo' ),
					{
						isDismissible: true,
						type: 'snackbar',
						speak: true,
						actions: [
							{
								url: translationsAlt[ locale ].editLink,
								label: __( 'Edit Post', 'bogo' ),
							}
						]
					}
				);
			} );
		}

		const listItems = [];

		Object.entries( translations ).forEach( ( [ key, value ] ) => {
			if ( value.postId ) {
				return;
			}

			listItems.push(
				<li key={ key }>
					<Button
						isDefault
						onClick={ () => { addTranslation( key ) } }
					>
						{ bogo.l10n.addTranslation[ key ] }
					</Button>
					{ value.creating && <Spinner /> }
				</li>
			);
		} );

		if ( listItems.length < 1 || 'auto-draft' == currentPost.status ) {
			return( <></> );
		}

		return(
			<PanelRow>
				<ul>{ listItems }</ul>
			</PanelRow>
		);
	}

	return(
		<PluginDocumentSettingPanel
			name="bogo-language-panel"
			title={ __( 'Language', 'bogo' ) }
			className="bogo-language-panel"
		>
			<PostLanguage />
			<Translations />
			<AddTranslation />
		</PluginDocumentSettingPanel>
	);
}

registerPlugin( 'bogo-language-panel', {
	render: LanguagePanel,
	icon: 'translation'
} );

apiFetch.use( ( options, next ) => {
	const postLang = bogo.currentPost.lang;

	if ( postLang ) {
		if (
			typeof options.url === 'string' &&
			! hasQueryArg( options.url, 'lang' )
		) {
			options.url = addQueryArgs( options.url, { lang: postLang } );
		}

		if (
			typeof options.path === 'string' &&
			! hasQueryArg( options.path, 'lang' )
		) {
			options.path = addQueryArgs( options.path, { lang: postLang } );
		}
	}

	return next( options, next );
} );

const getLanguage = ( locale ) => {
	return bogo.availableLanguages[locale]
		? bogo.availableLanguages[locale]
		: locale;
}

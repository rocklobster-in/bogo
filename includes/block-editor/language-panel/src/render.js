import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { PanelRow, Button, ExternalLink, Spinner, SelectControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { dispatch, useSelect } from '@wordpress/data';
import { sprintf, __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

export default function LanguagePanel() {
	const { currentPost, currentLocale, postId, postStatus } = useSelect( ( select ) => {
		const post = select( 'core/editor' ).getCurrentPost();
		const meta = select( 'core/editor' ).getEditedPostAttribute( 'meta' );
		
		return {
			currentPost: Object.assign( {}, post, bogo.currentPost ),
			currentLocale: meta?._locale || bogo.currentPost.locale || '',
			postId: post.id,
			postStatus: post.status,
		};
	} );

	if ( -1 == bogo.localizablePostTypes.indexOf( currentPost.type ) ) {
		return( <></> );
	}

	const [ translations, setTranslations ]
		= useState( currentPost.translations );

	const PostLanguage = () => {
		const languageOptions = Object.entries( bogo.availableLanguages ).map( ( [ locale, lang ] ) => {
			return {
				value: locale,
				label: lang.name || locale,
			};
		} );

		const handleLanguageChange = ( newLocale ) => {
			// Don't update if post is auto-draft or doesn't have an ID
			if ( ! postId || 'auto-draft' === postStatus ) {
				return;
			}
			
			dispatch( 'core/editor' ).editPost( {
				meta: { _locale: newLocale }
			} );
		};

		// Check if post has translation connections.
		const hasTranslations = Object.values( translations ).some( 
			( translation ) => translation.postId 
		);

		const isDisabled = ! postId || 'auto-draft' === postStatus || hasTranslations;

		return(
			<PanelRow>
				<span>{ __( 'Language', 'bogo' ) }</span>
				<SelectControl
					value={ currentLocale }
					options={ languageOptions }
					onChange={ handleLanguageChange }
					disabled={ isDisabled }
					__nextHasNoMarginBottom
				/>
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
			const translationsAlt = { ...translations };

			translationsAlt[ locale ] = {
				creating: true,
			};

			setTranslations( translationsAlt );

			apiFetch( {
				path: `/bogo/v1/posts/${ currentPost.id }/translations/${ locale }`,
				method: 'POST',
			} ).then( ( response ) => {
				const translationsAlt = { ...translations };

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
						{
							sprintf(
								/* translators: %s: Language name. */
								__( 'Add %s translation', 'bogo' ),
								getLanguage( key )
							)
						}
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

const getLanguage = ( locale ) => {
	return bogo.availableLanguages[locale]
		? bogo.availableLanguages[locale].name
		: locale;
}

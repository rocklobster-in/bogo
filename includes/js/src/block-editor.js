const { registerPlugin } = wp.plugins;
const { PluginDocumentSettingPanel } = wp.editPost;
const { PanelRow, Button, ExternalLink, Spinner } = wp.components;
const { useState } = wp.element;
const { dispatch, useSelect } = wp.data;
const { apiFetch } = wp;

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
				<span>{ bogo.l10n.language }</span>
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
			}
		} );

		const ListItems = ( props ) => {
			if ( props.listItems.length ) {
				return(
					<ul>{ props.listItems }</ul>
				);
			} else {
				return(
					<em>{ bogo.l10n.none }</em>
				);
			}
		}

		return(
			<PanelRow>
				<span>{ bogo.l10n.translations }</span>
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
					bogo.l10n.noticePostCreation,
					{
						isDismissible: true,
						type: 'snackbar',
						speak: true,
						actions: [
							{
								url: translationsAlt[ locale ].editLink,
								label: bogo.l10n.editPost,
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
			title={ bogo.l10n.language }
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

const getLanguage = ( locale ) => {
	return bogo.availableLanguages[locale]
		? bogo.availableLanguages[locale]
		: locale;
}

const { registerPlugin } = wp.plugins;
const { PluginDocumentSettingPanel } = wp.editPost;
const { PanelRow, SelectControl } = wp.components;
const { useState } = wp.element;
const { withState } = wp.compose;
const { select, dispatch } = wp.data;
const { apiFetch } = wp;

function LanguagePanel() {
	const currentPost = Object.assign( {},
		select( 'core/editor' ).getCurrentPost(),
		bogo.currentPost
	);

	if ( -1 == bogo.localizablePostTypes.indexOf( currentPost.type ) ) {
		return( <></> );
	}

	const [ translations, setTranslations ]
		= useState( currentPost.translations );

	const PostLanguage = () => {
		return(
			<PanelRow>
				<span>Language</span>
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
						<a
							href={ value.editLink }
							target="_blank"
							rel="noopener noreferrer"
						>
							{ value.postTitle }
						</a>
						<span className="screen-reader-text">
							{ bogo.screenReaderText.targetBlank }
						</span>
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
					<em>None</em>
				);
			}
		}

		return(
			<PanelRow>
				<span>Translations</span>
				<ListItems listItems={ listItems } />
			</PanelRow>
		);
	}

	const AddTranslation = withState( {
		locale: "",
	} )( ( { locale, setState } ) => {
		const options = [
			{ label: "Add Translation", value: "" }
		];

		Object.entries( translations ).forEach( ( [ key, value ] ) => {
			if ( ! value.postId ) {
				options.push( { label: getLanguage( key ), value: key } );
			}
		} );

		const addTranslation = ( locale ) => {
			setState( { locale } );

			apiFetch( {
				path: '/bogo/v1/posts/' + currentPost.id +
					'/translations/' + locale,
				method: 'POST',
			} ).then( ( response ) => {
				const translationsAlt = Object.assign( {}, translations );

				translationsAlt[ locale ] = {
					postId: response[ locale ].id,
					postTitle: response[ locale ].title.rendered,
					editLink: response[ locale ].edit_link,
				};

				setTranslations( translationsAlt );

				dispatch('core/notices').createInfoNotice(
					'Translation post created.',
					{
						isDismissible: true,
						type: 'snackbar',
						speak: true,
					}
				);
			} );
		}

		if ( options.length <= 1 || 'auto-draft' == currentPost.status ) {
			return( <></> );
		}

		return(
			<PanelRow>
				<span></span>
				<SelectControl
					value={ locale }
					options={ options }
					onChange={ ( locale ) => { addTranslation( locale ) } }
				/>
			</PanelRow>
		);
	} );

	return(
		<PluginDocumentSettingPanel
			name="bogo-language-panel"
			title="Language"
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

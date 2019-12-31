const { registerPlugin } = wp.plugins;
const { PluginDocumentSettingPanel } = wp.editPost;
const { Spinner, PanelRow, SelectControl } = wp.components;
const { useState } = wp.element;
const { withState } = wp.compose;
const { apiFetch } = wp;

function LanguagePanel() {

  const [ translations, setTranslations ]
    = useState( bogo.currentPost.translations );

  const PostLanguage = () => {
    return(
      <PanelRow>
        <span>Language</span>
        <div>{ getLanguage( bogo.currentPost.locale ) }</div>
      </PanelRow>
    );
  }

  const Translations = () => {
    let listItems = [];

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
            <span className="screen-reader-text">(opens in a new window)</span>
            <br /> [{ getLanguage( key ) }]
          </li>
        );
      }
    } );

    return(
      <PanelRow>
        <span>Translations</span>
        <ul>{ listItems }</ul>
      </PanelRow>
    );
  }

  const AddTranslation = withState( {
    locale: "",
  } )( ( { locale, setState } ) => {
    let options = [
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
        path: '/bogo/v1/posts/' + bogo.currentPost.postId +
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
      } );
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

const { registerPlugin } = wp.plugins;
const { PluginDocumentSettingPanel } = wp.editPost;
const { Spinner, PanelRow } = wp.components;
const { useState } = wp.element;

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
          <li>
            <a
              href={value.editLink}
              target="_blank"
              rel="noopener noreferrer"
            >
              { value.postTitle }
            </a>
            <span class="screen-reader-text">(opens in a new window)</span>
            <br /> [{ getLanguage( key ) }]
          </li>
        );
      } else if ( value.postTitle ) {
        listItems.push(
          <li>
            { value.postTitle }
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

  return(
    <PluginDocumentSettingPanel
      name="bogo-language-panel"
      title="Language"
      className="bogo-language-panel"
    >
      <PostLanguage />
      <Translations />
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

function init_bogo_admin() {

  if ( typeof bogo === 'undefined' || bogo === null ) {
    return;
  }

  bogo.langName = function( locale ) {
    return bogo.availableLanguages[ locale ] || '';
  };

  bogo.apiSettings.getRoute = function( path ) {

    var url = bogo.apiSettings.root;
    url = url.replace( bogo.apiSettings.namespace, bogo.apiSettings.namespace + path );

    return url;
  };
  

  var bogo_add_translation = document.getElementById( 'bogo-add-translation' );

  if ( bogo_add_translation ) {

    bogo_add_translation.onclick = function() {

      if ( !bogo.currentPost.postId ) {
        return;
      }

      var locale = document.getElementById('bogo-translations-to-add').value;
      var rest_url = bogo.apiSettings.getRoute( '/posts/' + bogo.currentPost.postId + '/translations/' + locale );
      var spinner_element = document.getElementById( 'bogo-add-translation' ).nextElementSibling;

      spinner_element.style.visibility = 'visible';

      var httpRequest = new XMLHttpRequest();
      httpRequest.onreadystatechange = function( data ) {

        if ( httpRequest.readyState == XMLHttpRequest.DONE ) {   // XMLHttpRequest.DONE == 4

          if ( httpRequest.status == 200 ) {

            var response = JSON.parse( httpRequest.response );
            var post = response[locale];

            if (!post) {
              return;
            }

            // The element into which appending will be done
            var element = document.getElementById( 'bogo-translations' );

            // The element to be appended
            var child = document.createElement( 'LI' );
            var output = post.title.rendered;
            output += ' <span class="screen-reader-text">' + bogo.l10n.targetBlank + '</span>';
            child.innerHTML = '<a href="' + post.edit_link + '" target="_blank" rel="noopener noreferrer">' + output + '</a> [' + bogo.availableLanguages[locale] + ']';

            // append
            element.appendChild( child );

            // remove appended option
            document.querySelector( '#bogo-translations-to-add option[value="' + locale + '"]' ).remove();

            var langs = document.getElementById( 'bogo-translations-to-add' );

            if (!langs.options.length) {
              document.getElementById( 'bogo-add-translation-actions' ).remove();
            }
          }

          spinner_element.style.visibility = 'hidden';
        }
      };

      httpRequest.open( 'POST', rest_url );
      httpRequest.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
      httpRequest.setRequestHeader( 'X-WP-Nonce', bogo.apiSettings.nonce );
      httpRequest.send();

    };
  }



  if ( 'bogo-texts' == bogo.pagenow ) {

    // window.addEventListener( "beforeunload", function( event ) {
    window.onbeforeunload =  function( event ) {
      var changed = false;

      document.querySelectorAll( "#bogo-terms-translation input[type=text]" ).forEach( text => {
        if ( text.defaultValue != text.value ) {
              changed = true;
            }
      });

      if ( changed ) {
        event.returnValue = bogo.l10n.saveAlert;
        return bogo.l10n.saveAlert;
      }
    };



    document.getElementById('bogo-terms-translation').onsubmit = function(){
      window.onbeforeunload = function () {};
    };


    var select_local = document.getElementById('select-locale');
    select_local.addEventListener('change',function(){
      location = 'admin.php?page=bogo-texts&locale=' + select_local.value;
    });

  }



}


// fire
init_bogo_admin();

interface bogoObject {
    langName: any;
    availableLanguages: any;
    apiSettings: any;
    currentPost: any;
    l10n: any;
    pagenow: any;
}

declare var bogo: bogoObject;

(function () {

    if (typeof bogo === 'undefined' || bogo === null) {
        return;
    }

    bogo.langName = function (locale: any) {
        return bogo.availableLanguages[locale] || '';
    };

    bogo.apiSettings.getRoute = function (path: any) {
        let url = bogo.apiSettings.root;
        url = url.replace(bogo.apiSettings.namespace, bogo.apiSettings.namespace + path);

        return url;
    };

    const bogo_add_translation = document.querySelector('#bogo-add-translation');

    if (bogo_add_translation) {

        bogo_add_translation.addEventListener('click', function () {

            if (!bogo.currentPost.postId) {
                return;
            }

            const locale: any = (<HTMLInputElement>document.querySelector('#bogo-translations-to-add')).value;
            const rest_url: any = bogo.apiSettings.getRoute('/posts/' + bogo.currentPost.postId + '/translations/' + locale);
            const spinner_element: any = document.querySelector('#bogo-add-translation').nextElementSibling;

            spinner_element.style.visibility = 'visible';

            const httpRequest = new XMLHttpRequest();
            httpRequest.onreadystatechange = function (data) {

                if (httpRequest.readyState == XMLHttpRequest.DONE) {   // XMLHttpRequest.DONE == 4

                    if (httpRequest.status == 200) {

                        const response = JSON.parse(httpRequest.response);
                        const post = response[locale];

                        if (!post) {
                            return;
                        }

                        // The element into which appending will be done
                        const element = document.getElementById('bogo-translations');

                        // The element to be appended
                        let child = document.createElement('LI');
                        let output = post.title.rendered;
                        output += ' <span class="screen-reader-text">' + bogo.l10n.targetBlank + '</span>';
                        child.innerHTML = '<a href="' + post.edit_link + '" target="_blank" rel="noopener noreferrer">' + output + '</a> [' + bogo.availableLanguages[locale] + ']';

                        // append
                        element.appendChild(child);

                        // remove appended option
                        document.querySelector('#bogo-translations-to-add option[value="' + locale + '"]').remove();

                        const langs: any = document.querySelector('#bogo-translations-to-add');

                        if (!langs.options.length) {
                            document.querySelector('#bogo-add-translation-actions').remove();
                        }
                    }

                    spinner_element.style.visibility = 'hidden';
                }
            }

            httpRequest.open('POST', rest_url);
            httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            httpRequest.setRequestHeader('X-WP-Nonce', bogo.apiSettings.nonce);
            httpRequest.send();

        });
    }

    if ('bogo-texts' == bogo.pagenow) {

        window.onbeforeunload = function (event: any) {
            let changed = false;

            document.querySelectorAll("#bogo-terms-translation input[type=text]").forEach((text: any) => {
                if (text.defaultValue != text.value) {
                    changed = true;
                }
            });

            if (changed) {
                event.returnValue = bogo.l10n.saveAlert;
                return bogo.l10n.saveAlert;
            }
        };

        const bogo_terms = (<HTMLInputElement>document.querySelector('#bogo-terms-translation'));
        bogo_terms.addEventListener('submit', function () {
            window.onbeforeunload = function () {
            };
        });

        const select_local = (<HTMLInputElement>document.querySelector('#select-locale'));
        select_local.addEventListener('change', function () {
          window.location.href = 'admin.php?page=bogo-texts&locale=' + select_local.value;
        });
    }
})();

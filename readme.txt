=== Bogo ===
Contributors: takayukister, itpixelz
Tags: multilingual, localization, language, locale, admin
Requires at least: 5.4
Tested up to: 5.6
Stable tag: 3.3.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://contactform7.com/donate/

A straight-forward multilingual plugin. No more double-digit custom DB tables or hidden HTML comments that could cause you headaches later on.

== Description ==

https://ideasilo.wordpress.com/bogo/

Bogo is a straight-forward multilingual plugin for WordPress.

The core of WordPress itself has the built-in localization capability so you can use the dashboard and theme in one language other than English. Bogo expands this capability to let you easily build a multilingual blog on a single WordPress install.

Here are some technical details for those interested. Bogo plugin assigns [one language per post](https://wordpress.org/support/article/multilingual-wordpress/#different-types-of-multilingual-plugins). It plays nice with WordPress – Bogo does not create any additional custom table on your database, unlike some other plugins in this category. This design makes Bogo a solid, reliable and conflict-free multilingual plugin.

= Getting started with Bogo =

1. Install language packs

	First, install language packs for languages you use on the site. You can view and install language packs in the **Language Packs** screen (**Languages > Language Packs**).

2. Select your language for admin screen

	Bogo lets each logged-in user select a language for their admin screen UI. Select a language from the menu on the [**Toolbar**](https://wordpress.org/support/article/administration-screens/#toolbar-keeping-it-all-together), or from the menu in the **Profile** screen (**Users > Your Profile**) if the **Toolbar** is invisible.

3. Translate your posts and pages

	To create a translation post, go to the editor screen for the original post and find the **Language** box. Bogo does only make a copy of the post; translating the copied post is your task.

4. Add language switcher widgets

	It would be useful for site visitors if you have a language switcher on your site. Bogo provides the **Language Switcher** widget in the **Widgets** screen (**Appearance > Widgets**).

	You can also use the `[bogo]` shortcode to put a language switcher inside a post content. If you want to use this shortcode in your theme's template files, embed the following code into the template:

	`<?php echo do_shortcode( '[bogo]' ); ?>`

= Privacy notices =

With the default configuration, this plugin, in itself, does not:

* track users by stealth;
* write any user personal data to the database;
* send any data to external servers;
* use cookies.

== Installation ==

1. Upload the entire `bogo` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the **Plugins** screen (**Plugins > Installed Plugins**).

== Screenshots ==

1. You can select your language in the **Toolbar**.
1. The **Language** box manages the post's translations.
1. The **Language Packs** screen lets you view and install language packs.

== Changelog ==

= 3.4 =

* Modernize the JS build process.
* Uses `__()` in JavaScript.
* Has removed some unused functions and variables.
* Has been tested with WordPress 5.6.

= 3.3.4 =

* Fixed: Unintended locale was set in the Posts screen.
* Supports `set_screen_option_bogo_texts_per_page` filter hook.
* REST API: Adds the `permission_callback` argument to endpoint definition.

= 3.3.3 =

* Fixed: Translation selector with no available option was displayed in a Classic Editor meta box.
* Fixed: The `lang` variable in a request was ignored on admin screens.
* Fixed: Elements in the HTML header had incorrect language attributes.

= 3.3.2 =

* User locale: Fixes several issues seen when you are logged-in as a non-Administrator role user.
* User locale: Renders the **Toolbar** in the logged-in user's locale even on the front side.
* Capabilities: Editor role users can now access the **Terms Translation** page, but higher level capabilities are required to edit some of translation items.
* Makes the `exclude_enus_if_inactive` option true by default.
* New filter hook: `bogo_get_short_name`

= 3.3.1 =

* Block Editor: Displays a spinner icon when creating a translation post.
* Block Editor: Suggests posts in the same locale as the current post when making a text link or doing other operations in a block.
* Block Editor: Fixes the issue that character references appear in post titles.

= 3.3 =

* Supports Block Editor.
* Adds the `short_name` option to `bogo_available_languages()`.
* Uses the post guid for the value of the `_original_post` post meta.
* Improves HTML markup of the language switcher.

=== Bogo ===
Contributors: rocklobsterinc, takayukister, itpixelz
Tags: multilingual, localization, language, locale, admin
Requires at least: 6.7
Tested up to: 6.9
Stable tag: 3.9.0.1
Requires PHP: 7.4
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://contactform7.com/donate/

A straight-forward multilingual plugin. No more double-digit custom DB tables or hidden HTML comments that could cause you headaches later on.

== Description ==

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

= 3.9.1 =

* Confirmed WordPress 6.9 compatibility.

= 3.9 =

* Overhauls the JavaScript used in the admin screens.
* Fixes a lot of errors that the Plugin Check plugin (PCP) has reported.

= 3.8.2 =

* Fixes a bug that prevents block editor from working correctly on 6.4-6.5 versions of WordPress.

= 3.8.1 =

* Language packs: Fixes a bug that blocks language pack deactivation.

= 3.8 =

* Language switcher: Updates `apiVersion` to `3` in the `block.json` file.
* Lets `bogo_http_accept_languages()` always return an array.
* Fixes a bug that makes it impossible to have two sticky posts or more.

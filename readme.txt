=== Bogo ===
Contributors: takayukister, itpixelz
Tags: multilingual, localization, language, locale, admin
Requires at least: 6.4
Tested up to: 6.2
Stable tag: 3.6.1
Requires PHP: 7.4
License: GPL v2 or later
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

= 3.7 =

* Language switcher: Adds the language suggestion view.
* Improves `bogo_format_atts()`.
* Updates the languages list in `bogo_languages()` based on translate.wordpress.org.
* Uses a static list to retrieve language native names.
* National flags: Drops famfamfam.com icons in favor of Unicode regional indicator symbols.
* New filter hook: `bogo_get_language`
* New filter hook: `bogo_get_language_native_name`
* New filter hook: `bogo_get_country_code`

= 3.6.1 =

* Updates bundled JS libraries.
* Includes asset files in the wp.org SVN repository.

= 3.6 =

* Introduces the language switcher block.
* Fixes a bug that causes multiple serialization on post duplication.
* Fixes a bug in jQuery code that makes the site language unable to switch.

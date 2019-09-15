=== Bogo ===
Contributors: takayukister
Tags: multilingual, localization, language, locale, admin
Requires at least: 4.9
Tested up to: 4.9
Stable tag: 3.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://pledgie.com/campaigns/17860

A straight-forward multilingual plugin. No more double-digit custom DB tables or hidden HTML comments that could cause you headaches later on.

== Description ==

https://ideasilo.wordpress.com/bogo/

Bogo is a straight-forward multilingual plugin for WordPress.

The core of WordPress itself has the built-in localization capability so you can use the dashboard and theme in one language other than English. Bogo expands this capability to let you easily build a multilingual blog on a single WordPress install.

Here are some technical details for those interested. Bogo plugin assigns [one language per post](https://codex.wordpress.org/Multilingual_WordPress#Different_types_of_multilingual_plugins). It plays nice with WordPress – Bogo does not create any additional custom table on your database, unlike some other plugins in this category. This design makes Bogo a solid, reliable and conflict-free multilingual plugin.

= Getting Started with Bogo =

1. Install language files

	First, make sure you have installed language files for all languages used in your site. If you have a localized version of WordPress installed, you should already have these files for that language.

	If you don't have language files yet, you can install them via Bogo's admin page (Languages > Installed Languages).

2. Select your language for admin screen (dashboard)

	Bogo allows each user to select a language for his/her own WordPress admin screen. Logged-in users can switch languages from the drop-down menu on the Admin Bar.

	If the Admin Bar is hidden, you can also switch language on your Profile page.

3. Translate posts and pages

	You can translate posts and pages into the languages you have installed.

	WordPress saves the contents of each post or page as usual, but Bogo adds '_locale' post_meta data. The '_locale' holds the language code of the post.

4. Add a language switcher to your site

	You will want to place a language switcher on your site that allows visitors to switch languages they see on the site. The easiest method is using the Language Switcher widget included in Bogo.

	Bogo also provides a shortcode "[bogo]" to allow you to place a language switcher inside a post or page content by simply inserting [bogo]. To embed a language switcher directly into your theme's template file, use this shortcode as follows:

	`<?php echo do_shortcode( '[bogo]' ); ?>`

== Installation ==

1. Upload the entire `bogo` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.

== Screenshots ==

1. You can switch your admin language in the Admin Bar.
1. The Language Post Box manages language and translations of the Post/Page.
1. In the Language Packs screen, you can install and manage language packs.

== Changelog ==

= 3.2.1 =

* Recalculates values in some WP query parameters in bogo_parse_query().

= 3.2 =

* Uses the install_languages as the default capability required for the Language Packs page.
* Adds rel="noopener noreferrer" and screenreader text to target="_blank" links.
* Adds a filter hook: bogo_terms_translation.
* Introduces the Bogo_Terms_Translation_List_Table class.
* Introduces the Bogo_Language_Packs_List_Table class.

= 3.1.4 =

* Ease restrictions on locale code to accept special cases like "pt_PT_ao90".
* Add screenreader accessibility text "(opens in a new window)" to target=blank links.

= 3.1.3 =

* Avoid warnings in cases there is empty $row_actions for some reason.

= 3.1.2 =

* Fixed: Warnings were shown in Menus admin page when there were no menu registered.

= 3.1.1 =

* Fixed: Suppress locale query on preview.

= 3.1 =

* Renovated markup and style around nation flags.

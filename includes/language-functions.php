<?php

/**
 * Retrieves installable languages.
 *
 * @link http://api.wordpress.org/translations/core/1.0/
 */
function bogo_languages() {
	static $languages = array();
	static $textdomain_loaded = false;

	if ( $languages and $textdomain_loaded and ! is_locale_switched() ) {
		return apply_filters( 'bogo_languages', $languages );
	}

	$languages = array(
		'af' => __( 'Afrikaans', 'bogo' ),
		'am' => __( 'Amharic', 'bogo' ),
		'ar' => __( 'Arabic', 'bogo' ),
		'arg' => __( 'Aragonese', 'bogo' ),
		'ary' => __( 'Moroccan Arabic', 'bogo' ),
		'as' => __( 'Assamese', 'bogo' ),
		'az' => __( 'Azerbaijani', 'bogo' ),
		'azb' => __( 'South Azerbaijani', 'bogo' ),
		'bel' => __( 'Belarusian', 'bogo' ),
		'bg_BG' => __( 'Bulgarian', 'bogo' ),
		'bn_BD' => __( 'Bengali (Bangladesh)', 'bogo' ),
		'bo' => __( 'Tibetan', 'bogo' ),
		'bs_BA' => __( 'Bosnian', 'bogo' ),
		'ca' => __( 'Catalan', 'bogo' ),
		'ceb' => __( 'Cebuano', 'bogo' ),
		'ckb' => __( 'Kurdish (Sorani)', 'bogo' ),
		'cs_CZ' => __( 'Czech', 'bogo' ),
		'cy' => __( 'Welsh', 'bogo' ),
		'da_DK' => __( 'Danish', 'bogo' ),
		'de_AT' => __( 'German (Austria)', 'bogo' ),
		'de_CH' => __( 'German (Switzerland)', 'bogo' ),
		'de_CH_informal' => __( 'German (Switzerland, Informal)', 'bogo' ),
		'de_DE' => __( 'German', 'bogo' ),
		'de_DE_formal' => __( 'German (Formal)', 'bogo' ),
		'dsb' => __( 'Lower Sorbian', 'bogo' ),
		'dzo' => __( 'Dzongkha', 'bogo' ),
		'el' => __( 'Greek', 'bogo' ),
		'en_AU' => __( 'English (Australia)', 'bogo' ),
		'en_CA' => __( 'English (Canada)', 'bogo' ),
		'en_GB' => __( 'English (UK)', 'bogo' ),
		'en_NZ' => __( 'English (New Zealand)', 'bogo' ),
		'en_US' => __( 'English (United States)', 'bogo' ),
		'en_ZA' => __( 'English (South Africa)', 'bogo' ),
		'eo' => __( 'Esperanto', 'bogo' ),
		'es_AR' => __( 'Spanish (Argentina)', 'bogo' ),
		'es_CL' => __( 'Spanish (Chile)', 'bogo' ),
		'es_CO' => __( 'Spanish (Colombia)', 'bogo' ),
		'es_CR' => __( 'Spanish (Costa Rica)', 'bogo' ),
		'es_DO' => __( 'Spanish (Dominican Republic)', 'bogo' ),
		'es_EC' => __( 'Spanish (Ecuador)', 'bogo' ),
		'es_ES' => __( 'Spanish (Spain)', 'bogo' ),
		'es_GT' => __( 'Spanish (Guatemala)', 'bogo' ),
		'es_MX' => __( 'Spanish (Mexico)', 'bogo' ),
		'es_PE' => __( 'Spanish (Peru)', 'bogo' ),
		'es_PR' => __( 'Spanish (Puerto Rico)', 'bogo' ),
		'es_UY' => __( 'Spanish (Uruguay)', 'bogo' ),
		'es_VE' => __( 'Spanish (Venezuela)', 'bogo' ),
		'et' => __( 'Estonian', 'bogo' ),
		'eu' => __( 'Basque', 'bogo' ),
		'fa_AF' => __( 'Persian (Afghanistan)', 'bogo' ),
		'fa_IR' => __( 'Persian', 'bogo' ),
		'fi' => __( 'Finnish', 'bogo' ),
		'fr_BE' => __( 'French (Belgium)', 'bogo' ),
		'fr_CA' => __( 'French (Canada)', 'bogo' ),
		'fr_FR' => __( 'French (France)', 'bogo' ),
		'fur' => __( 'Friulian', 'bogo' ),
		'fy' => __( 'Frisian', 'bogo' ),
		'gd' => __( 'Scottish Gaelic', 'bogo' ),
		'gl_ES' => __( 'Galician', 'bogo' ),
		'gu' => __( 'Gujarati', 'bogo' ),
		'haz' => __( 'Hazaragi', 'bogo' ),
		'he_IL' => __( 'Hebrew', 'bogo' ),
		'hi_IN' => __( 'Hindi', 'bogo' ),
		'hr' => __( 'Croatian', 'bogo' ),
		'hsb' => __( 'Upper Sorbian', 'bogo' ),
		'hu_HU' => __( 'Hungarian', 'bogo' ),
		'hy' => __( 'Armenian', 'bogo' ),
		'id_ID' => __( 'Indonesian', 'bogo' ),
		'is_IS' => __( 'Icelandic', 'bogo' ),
		'it_IT' => __( 'Italian', 'bogo' ),
		'ja' => __( 'Japanese', 'bogo' ),
		'jv_ID' => __( 'Javanese', 'bogo' ),
		'ka_GE' => __( 'Georgian', 'bogo' ),
		'kab' => __( 'Kabyle', 'bogo' ),
		'kk' => __( 'Kazakh', 'bogo' ),
		'km' => __( 'Khmer', 'bogo' ),
		'kn' => __( 'Kannada', 'bogo' ),
		'ko_KR' => __( 'Korean', 'bogo' ),
		'lo' => __( 'Lao', 'bogo' ),
		'lt_LT' => __( 'Lithuanian', 'bogo' ),
		'lv' => __( 'Latvian', 'bogo' ),
		'mk_MK' => __( 'Macedonian', 'bogo' ),
		'ml_IN' => __( 'Malayalam', 'bogo' ),
		'mn' => __( 'Mongolian', 'bogo' ),
		'mr' => __( 'Marathi', 'bogo' ),
		'ms_MY' => __( 'Malay', 'bogo' ),
		'my_MM' => __( 'Myanmar (Burmese)', 'bogo' ),
		'nb_NO' => __( 'Norwegian (Bokmål)', 'bogo' ),
		'ne_NP' => __( 'Nepali', 'bogo' ),
		'nl_BE' => __( 'Dutch (Belgium)', 'bogo' ),
		'nl_NL' => __( 'Dutch', 'bogo' ),
		'nl_NL_formal' => __( 'Dutch (Formal)', 'bogo' ),
		'nn_NO' => __( 'Norwegian (Nynorsk)', 'bogo' ),
		'oci' => __( 'Occitan', 'bogo' ),
		'pa_IN' => __( 'Panjabi (India)', 'bogo' ),
		'pl_PL' => __( 'Polish', 'bogo' ),
		'ps' => __( 'Pashto', 'bogo' ),
		'pt_AO' => __( 'Portuguese (Angola)', 'bogo' ),
		'pt_BR' => __( 'Portuguese (Brazil)', 'bogo' ),
		'pt_PT' => __( 'Portuguese (Portugal)', 'bogo' ),
		'pt_PT_ao90' => __( 'Portuguese (Portugal, AO90)', 'bogo' ),
		'rhg' => __( 'Rohingya', 'bogo' ),
		'ro_RO' => __( 'Romanian', 'bogo' ),
		'ru_RU' => __( 'Russian', 'bogo' ),
		'sah' => __( 'Sakha', 'bogo' ),
		'si_LK' => __( 'Sinhala', 'bogo' ),
		'sk_SK' => __( 'Slovak', 'bogo' ),
		'skr' => __( 'Saraiki', 'bogo' ),
		'sl_SI' => __( 'Slovenian', 'bogo' ),
		'snd' => __( 'Sindhi', 'bogo' ),
		'sq' => __( 'Albanian', 'bogo' ),
		'sr_RS' => __( 'Serbian', 'bogo' ),
		'sv_SE' => __( 'Swedish', 'bogo' ),
		'sw' => __( 'Swahili', 'bogo' ),
		'szl' => __( 'Silesian', 'bogo' ),
		'ta_IN' => __( 'Tamil', 'bogo' ),
		'ta_LK' => __( 'Tamil (Sri Lanka)', 'bogo' ),
		'tah' => __( 'Tahitian', 'bogo' ),
		'te' => __( 'Telugu', 'bogo' ),
		'th' => __( 'Thai', 'bogo' ),
		'tl' => __( 'Tagalog', 'bogo' ),
		'tr_TR' => __( 'Turkish', 'bogo' ),
		'tt_RU' => __( 'Tatar', 'bogo' ),
		'ug_CN' => __( 'Uighur', 'bogo' ),
		'uk' => __( 'Ukrainian', 'bogo' ),
		'ur' => __( 'Urdu', 'bogo' ),
		'uz_UZ' => __( 'Uzbek', 'bogo' ),
		'vi' => __( 'Vietnamese', 'bogo' ),
		'zh_CN' => __( 'Chinese (China)', 'bogo' ),
		'zh_HK' => __( 'Chinese (Hong Kong)', 'bogo' ),
		'zh_TW' => __( 'Chinese (Taiwan)', 'bogo' ),
	);

	$textdomain_loaded = is_textdomain_loaded( 'bogo' ) && ! is_locale_switched();

	asort( $languages, SORT_STRING );

	return apply_filters( 'bogo_languages', $languages );
}


/**
 * Returns the language name corresponding to the specified locale code.
 *
 * @param string $locale Locale code.
 */
function bogo_get_language( $locale ) {
	$languages = bogo_languages();

	if ( isset( $languages[$locale] ) ) {
		$language = $languages[$locale];
	} else {
		$language = false;
	}

	return apply_filters( 'bogo_get_language', $language, $locale );
}


/**
 * Returns the language native name corresponding to the specified locale code.
 *
 * @link http://api.wordpress.org/translations/core/1.0/
 *
 * @param string $locale Locale code.
 */
function bogo_get_language_native_name( $locale ) {
	$native_names = array(
		'af' => 'Afrikaans',
		'am' => 'አማርኛ',
		'ar' => 'العربية',
		'arg' => 'Aragonés',
		'ary' => 'العربية المغربية',
		'as' => 'অসমীয়া',
		'az' => 'Azərbaycan dili',
		'azb' => 'گؤنئی آذربایجان',
		'bel' => 'Беларуская мова',
		'bg_BG' => 'Български',
		'bn_BD' => 'বাংলা',
		'bo' => 'བོད་ཡིག',
		'bs_BA' => 'Bosanski',
		'ca' => 'Català',
		'ceb' => 'Cebuano',
		'ckb' => 'كوردی‎',
		'cs_CZ' => 'Čeština',
		'cy' => 'Cymraeg',
		'da_DK' => 'Dansk',
		'de_AT' => 'Deutsch (Österreich)',
		'de_CH' => 'Deutsch (Schweiz)',
		'de_CH_informal' => 'Deutsch (Schweiz, Du)',
		'de_DE' => 'Deutsch',
		'de_DE_formal' => 'Deutsch (Sie)',
		'dsb' => 'Dolnoserbšćina',
		'dzo' => 'རྫོང་ཁ',
		'el' => 'Ελληνικά',
		'en_AU' => 'English (Australia)',
		'en_CA' => 'English (Canada)',
		'en_GB' => 'English (UK)',
		'en_NZ' => 'English (New Zealand)',
		'en_US' => 'English (United States)',
		'en_ZA' => 'English (South Africa)',
		'eo' => 'Esperanto',
		'es_AR' => 'Español de Argentina',
		'es_CL' => 'Español de Chile',
		'es_CO' => 'Español de Colombia',
		'es_CR' => 'Español de Costa Rica',
		'es_DO' => 'Español de República Dominicana',
		'es_EC' => 'Español de Ecuador',
		'es_ES' => 'Español',
		'es_GT' => 'Español de Guatemala',
		'es_MX' => 'Español de México',
		'es_PE' => 'Español de Perú',
		'es_PR' => 'Español de Puerto Rico',
		'es_UY' => 'Español de Uruguay',
		'es_VE' => 'Español de Venezuela',
		'et' => 'Eesti',
		'eu' => 'Euskara',
		'fa_AF' => '(فارسی (افغانستان',
		'fa_IR' => 'فارسی',
		'fi' => 'Suomi',
		'fr_BE' => 'Français de Belgique',
		'fr_CA' => 'Français du Canada',
		'fr_FR' => 'Français',
		'fur' => 'Friulian',
		'fy' => 'Frysk',
		'gd' => 'Gàidhlig',
		'gl_ES' => 'Galego',
		'gu' => 'ગુજરાતી',
		'haz' => 'هزاره گی',
		'he_IL' => 'עִבְרִית',
		'hi_IN' => 'हिन्दी',
		'hr' => 'Hrvatski',
		'hsb' => 'Hornjoserbšćina',
		'hu_HU' => 'Magyar',
		'hy' => 'Հայերեն',
		'id_ID' => 'Bahasa Indonesia',
		'is_IS' => 'Íslenska',
		'it_IT' => 'Italiano',
		'ja' => '日本語',
		'jv_ID' => 'Basa Jawa',
		'ka_GE' => 'ქართული',
		'kab' => 'Taqbaylit',
		'kk' => 'Қазақ тілі',
		'km' => 'ភាសាខ្មែរ',
		'kn' => 'ಕನ್ನಡ',
		'ko_KR' => '한국어',
		'lo' => 'ພາສາລາວ',
		'lt_LT' => 'Lietuvių kalba',
		'lv' => 'Latviešu valoda',
		'mk_MK' => 'Македонски јазик',
		'ml_IN' => 'മലയാളം',
		'mn' => 'Монгол',
		'mr' => 'मराठी',
		'ms_MY' => 'Bahasa Melayu',
		'my_MM' => 'ဗမာစာ',
		'nb_NO' => 'Norsk bokmål',
		'ne_NP' => 'नेपाली',
		'nl_BE' => 'Nederlands (België)',
		'nl_NL' => 'Nederlands',
		'nl_NL_formal' => 'Nederlands (Formeel)',
		'nn_NO' => 'Norsk nynorsk',
		'oci' => 'Occitan',
		'pa_IN' => 'ਪੰਜਾਬੀ',
		'pl_PL' => 'Polski',
		'ps' => 'پښتو',
		'pt_AO' => 'Português de Angola',
		'pt_BR' => 'Português do Brasil',
		'pt_PT' => 'Português',
		'pt_PT_ao90' => 'Português (AO90)',
		'rhg' => 'Ruáinga',
		'ro_RO' => 'Română',
		'ru_RU' => 'Русский',
		'sah' => 'Сахалыы',
		'si_LK' => 'සිංහල',
		'sk_SK' => 'Slovenčina',
		'skr' => 'سرائیکی',
		'sl_SI' => 'Slovenščina',
		'snd' => 'سنڌي',
		'sq' => 'Shqip',
		'sr_RS' => 'Српски језик',
		'sv_SE' => 'Svenska',
		'sw' => 'Kiswahili',
		'szl' => 'Ślōnskŏ gŏdka',
		'ta_IN' => 'தமிழ்',
		'ta_LK' => 'தமிழ்',
		'tah' => 'Reo Tahiti',
		'te' => 'తెలుగు',
		'th' => 'ไทย',
		'tl' => 'Tagalog',
		'tr_TR' => 'Türkçe',
		'tt_RU' => 'Татар теле',
		'ug_CN' => 'ئۇيغۇرچە',
		'uk' => 'Українська',
		'ur' => 'اردو',
		'uz_UZ' => 'O‘zbekcha',
		'vi' => 'Tiếng Việt',
		'zh_CN' => '简体中文',
		'zh_HK' => '香港中文',
		'zh_TW' => '繁體中文',
	);

	if ( isset( $native_names[$locale] ) ) {
		$native_name = $native_names[$locale];
	} else {
		$native_name = false;
	}

	return apply_filters( 'bogo_get_language_native_name',
		$native_name, $locale
	);
}


/**
 * Returns the ISO 3166-1 alpha-2 country code corresponding to
 * the specified locale code.
 *
 * @link https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
 *
 * @param string $locale Locale code.
 */
function bogo_get_country_code( $locale ) {
	$special_cases = array(
		'am' => 'ET', // Amharic => Ethiopia
		'ary' => 'MA', // Moroccan Arabic => Morocco
		'az' => 'AZ', // Azerbaijani => Azerbaijan
		'bel' => 'BY', // Belarusian => Belarus
		'dzo' => 'BT', // Dzongkha => Bhutan
		'el' => 'GR', // Greek => Greece
		'et' => 'EE', // Estonian => Estonia
		'fi' => 'FI', // Finnish => Finland
		'hr' => 'HR', // Croatian => Croatia
		'hy' => 'AM', // Armenian => Armenia
		'ja' => 'JP', // Japanese => Japan
		'kk' => 'KZ', // Kazakh => Kazakhstan
		'km' => 'KH', // Khmer => Cambodia
		'lo' => 'LA', // Lao => Laos
		'lv' => 'LV', // Latvian => Latvia
		'mn' => 'MN', // Mongolian => Mongolia
		'ps' => 'AF', // Pashto => Afghanistan
		'sq' => 'AL', // Albanian => Albania
		'th' => 'TH', // Thai => Thailand
		'tl' => 'PH', // Tagalog => Philippines
		'uk' => 'UA', // Ukrainian => Ukraine
		'ur' => 'PK', // Urdu => Pakistan
		'vi' => 'VN', // Vietnamese => Vietnam
	);

	if ( preg_match( '/^[a-z]+_([A-Z]{2})/', $locale, $matches ) ) {
		$country_code = $matches[1];
	} elseif ( isset( $special_cases[$locale] ) ) {
		$country_code = $special_cases[$locale];
	} else {
		$country_code = false;
	}

	return apply_filters( 'bogo_get_country_code', $country_code, $locale );
}


/**
 * Retrieves the default locale for the site.
 */
function bogo_get_default_locale() {
	static $locale = '';

	if ( ! empty( $locale ) ) {
		return $locale;
	}

	if ( defined( 'WPLANG' ) ) {
		$locale = WPLANG;
	}

	if ( is_multisite() ) {
		if ( wp_installing()
		or false === $ms_locale = get_option( 'WPLANG' ) ) {
			$ms_locale = get_site_option( 'WPLANG' );
		}

		if ( $ms_locale !== false ) {
			$locale = $ms_locale;
		}
	} else {
		$db_locale = get_option( 'WPLANG' );

		if ( $db_locale !== false ) {
			$locale = $db_locale;
		}
	}

	if ( ! empty( $locale ) ) {
		return $locale;
	}

	return 'en_US';
}


/**
 * Returns true if the specified locale is the default locale.
 *
 * @param string $locale Locale code.
 */
function bogo_is_default_locale( $locale ) {
	$default_locale = bogo_get_default_locale();

	return ! empty( $locale ) && $locale == bogo_get_default_locale();
}


/**
 * Returns true if the en_US locale is deactivated on this site.
 */
function bogo_is_enus_deactivated() {
	if ( bogo_is_default_locale( 'en_US' ) ) {
		return false;
	}

	return (bool) bogo_get_prop( 'enus_deactivated' );
}


/**
 * Retrieves locale codes active on this site.
 */
function bogo_available_locales( $args = '' ) {
	$defaults = array(
		'exclude' => array(),
		'exclude_enus_if_inactive' => true,
		'current_user_can_access' => false,
	);

	$args = wp_parse_args( $args, $defaults );

	static $installed_locales = array();

	if ( empty( $installed_locales ) ) {
		$installed_locales = get_available_languages();
		$installed_locales[] = bogo_get_default_locale();
		$installed_locales[] = 'en_US';
	}

	$available_locales = $installed_locales;

	if ( $args['current_user_can_access']
	and ! current_user_can( 'bogo_access_all_locales' ) ) {
		$user_accessible_locales = bogo_get_user_accessible_locales(
			get_current_user_id()
		);

		$available_locales = array_intersect(
			$available_locales,
			$user_accessible_locales
		);
	}

	if ( ! empty( $args['exclude'] ) ) {
		$available_locales = array_diff(
			$available_locales,
			(array) $args['exclude']
		);
	}

	if ( $args['exclude_enus_if_inactive']
	and bogo_is_enus_deactivated() ) {
		$available_locales = array_diff(
			$available_locales,
			array( 'en_US' )
		);
	}

	return array_unique( array_filter( $available_locales ) );
}


/**
 * Retrieves languages active on this site.
 */
function bogo_available_languages( $args = '' ) {
	$defaults = array(
		'exclude' => array(),
		'orderby' => 'key',
		'order' => 'ASC',
		'exclude_enus_if_inactive' => true,
		'current_user_can_access' => false,
		'short_name' => true,
	);

	$args = wp_parse_args( $args, $defaults );

	$langs = array();

	$available_locales = bogo_available_locales( $args );

	foreach ( $available_locales as $locale ) {
		$lang = (string) bogo_get_language( $locale );

		if ( $args['short_name'] and bogo_locale_is_alone( $locale ) ) {
			$lang = bogo_get_short_name( $lang );
		}

		$lang = trim( $lang );
		$langs[$locale] = empty( $lang ) ? "[$locale]" : $lang;
	}

	if ( 'value' == $args['orderby'] ) {
		natcasesort( $langs );

		if ( 'DESC' == $args['order'] ) {
			$langs = array_reverse( $langs );
		}
	} else {
		if ( 'DESC' == $args['order'] ) {
			krsort( $langs );
		} else {
			ksort( $langs );
		}
	}

	$langs = apply_filters( 'bogo_available_languages', $langs, $args );

	return $langs;
}


/**
 * Returns true if the specified locale is active on this site.
 *
 * @param string $locale Locale code.
 */
function bogo_is_available_locale( $locale ) {
	if ( empty( $locale ) ) {
		return false;
	}

	static $available_locales = array();

	if ( empty( $available_locales ) ) {
		$available_locales = bogo_available_locales();
	}

	return in_array( $locale, $available_locales );
}


/**
 * Filters locales list.
 */
function bogo_filter_locales( $locales, $filter = 'available' ) {
	return array_intersect( (array) $locales, bogo_available_locales() );
}


/**
 * Retrieves the language tag for the specified locale.
 *
 * @link https://www.ietf.org/rfc/bcp/bcp47.txt
 *
 * @param string $locale Locale code.
 */
function bogo_language_tag( $locale ) {
	$tag = preg_replace( '/[^0-9a-zA-Z]+/', '-', $locale );
	$tag = trim( $tag, '-' );

	$tag = explode( '-', $tag );
	$tag = array_slice( $tag, 0, 2 ); // de-DE-formal => de-DE
	$tag = implode( '-', $tag );

	return apply_filters( 'bogo_language_tag', $tag, $locale );
}


/**
 * Retrieves the language slug for the specified locale.
 *
 * @param string $locale Locale code.
 */
function bogo_lang_slug( $locale ) {
	$tag = bogo_language_tag( $locale );
	$slug = $tag;

	if ( false !== $pos = strpos( $tag, '-' ) ) {
		$slug = substr( $tag, 0, $pos );
	}

	$variations = preg_grep( '/^' . $slug . '/',
		bogo_available_locales()
	);

	if ( 1 < count( $variations ) ) {
		$slug = $tag;
	}

	return apply_filters( 'bogo_lang_slug', $slug, $locale );
}


/**
 * Returns true if the specified locale has no sibling active on this site.
 *
 * @param string $locale Locale code.
 */
function bogo_locale_is_alone( $locale ) {
	$tag = bogo_language_tag( $locale );

	if ( false === strpos( $tag, '-' ) ) {
		return true;
	}

	$slug = bogo_lang_slug( $locale );

	return strlen( $slug ) < strlen( $tag );
}


/**
 * Retrieves the short version of the specified language name.
 */
function bogo_get_short_name( $orig_name ) {
	$short_name = $orig_name = (string) $orig_name;

	$langs_with_variants = array(
		'中文',
		'Français',
		'Português',
		'Español',
	);

	foreach ( $langs_with_variants as $lang ) {
		if ( false !== strpos( $orig_name, $lang ) ) {
			$short_name = $lang;
			break;
		}
	}

	if ( preg_match( '/^([^()]+)/', $short_name, $matches ) ) {
		$short_name = $matches[1];
	}

	$short_name = apply_filters( 'bogo_get_short_name', $short_name, $orig_name );

	return trim( $short_name );
}


/**
 * Retrieves the regular expression pattern that matches
 * all available language slugs on this site.
 */
function bogo_get_lang_regex() {
	$langs = array_map( 'bogo_lang_slug', bogo_available_locales() );
	$langs = array_filter( $langs );

	if ( empty( $langs ) ) {
		return '';
	}

	return '(' . implode( '|', $langs ) . ')';
}


/**
 * Retrieves the locale that is active on this site and
 * closest to the specified locale.
 *
 * @param string $locale_orig Locale code.
 * @return string|bool Locale code. False if there is no close locale.
 */
function bogo_get_closest_locale( $locale_orig ) {
	$locale_orig = strtolower( $locale_orig );
	$locale_pattern = '/^([a-z]{2,3})(?:[_-]([a-z]{2})(?:[_-]([a-z0-9]+))?)?$/';

	if ( ! preg_match( $locale_pattern, $locale_orig, $matches ) ) {
		return false;
	}

	$language_code = $matches[1];
	$region_code = isset( $matches[2] ) ? $matches[2] : '';
	$variant_code = isset( $matches[3] ) ? $matches[3] : '';

	$locales = bogo_available_locales();

	if ( $variant_code and $region_code ) {
		$locale = $language_code
			. '_' . strtoupper( $region_code )
			. '_' . $variant_code;

		if ( false !== array_search( $locale, $locales ) ) {
			return $locale;
		}
	}

	if ( $region_code ) {
		$locale = $language_code
			. '_' . strtoupper( $region_code );

		if ( false !== array_search( $locale, $locales ) ) {
			return $locale;
		}
	}

	$locale = $language_code;

	if ( false !== array_search( $locale, $locales ) ) {
		return $locale;
	}

	if ( $matches = preg_grep( "/^{$locale}_/", $locales ) ) {
		return array_shift( $matches );
	}

	return false;
}


/**
 * Returns an ordered list of language tags based on the client
 * language preference.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Language
 */
function bogo_http_accept_languages() {
	if ( ! isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
		return false;
	}

	$languages = array();

	foreach ( explode( ',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) as $lang ) {
		$lang = trim( strtolower( $lang ) );

		if ( preg_match( '/^([a-z-]+)(?:;q=([0-9.]+))?$/', $lang, $matches ) ) {
			$language_tag = $matches[1];
			$qvalue = isset( $matches[2] ) ? 0 + $matches[2] : 1;

			if ( preg_match( '/^([a-z]{2})(?:-([a-z]{2}))?$/', $language_tag, $matches ) ) {
				$language_tag = $matches[1];

				if ( isset( $matches[2] ) ) {
					$language_tag .= '_' . strtoupper( $matches[2] );
				}

				$languages[$language_tag] = $qvalue;
			}
		}
	}

	natsort( $languages );

	return array_reverse( array_keys( $languages ) );
}


/**
 * A wrapper function of bogo_get_url_with_lang().
 */
function bogo_url( $url = '', $locale = '' ) {
	if ( ! $locale ) {
		$locale = determine_locale();
	}

	$args = array(
		'using_permalinks' => (bool) get_option( 'permalink_structure' ),
	);

	return bogo_get_url_with_lang( $url, $locale, $args );
}


/**
 * Returns a URL that is a different language version of the original URL.
 *
 * @param string $url The original URL.
 * @param string $locale Locale code.
 * @param string|array $args Options.
 * @return string The result URL.
 */
function bogo_get_url_with_lang( $url = '', $locale = '', $args = '' ) {
	global $wp_rewrite;

	$defaults = array(
		'using_permalinks' => true,
	);

	$args = wp_parse_args( $args, $defaults );

	if ( ! $url ) {
		if ( ! $url = redirect_canonical( $url, false ) ) {
			$url = is_ssl() ? 'https://' : 'http://';
			$url .= $_SERVER['HTTP_HOST'];
			$url .= $_SERVER['REQUEST_URI'];
		}

		if ( $frag = strstr( $url, '#' ) ) {
			$url = substr( $url, 0, - strlen( $frag ) );
		}

		if ( $query = wp_parse_url( $url, PHP_URL_QUERY ) ) {
			parse_str( $query, $query_vars );

			foreach ( array_keys( $query_vars ) as $qv ) {
				if ( ! get_query_var( $qv ) ) {
					$url = remove_query_arg( $qv, $url );
				}
			}
		}
	}

	$default_locale = bogo_get_default_locale();

	if ( ! $locale ) {
		$locale = $default_locale;
	}

	$use_implicit_lang = apply_filters( 'bogo_use_implicit_lang', true );

	$lang_slug = ( $use_implicit_lang && $locale === $default_locale )
		? ''
		: bogo_lang_slug( $locale );

	$url = remove_query_arg( 'lang', $url );

	if ( ! $args['using_permalinks'] ) {
		if ( $lang_slug ) {
			$url = add_query_arg( array( 'lang' => $lang_slug ), $url );
		}

		return $url;
	}

	$tail_slashed = ( '/' === substr( $url, -1 ) );

	$home = set_url_scheme( get_option( 'home' ) );
	$home = untrailingslashit( $home );

	if ( $wp_rewrite->using_index_permalinks() ) {
		$pattern = '#^'
			. preg_quote( $home )
			. '(?:/' . preg_quote( $wp_rewrite->index ) . ')?'
			. '(?:/' . bogo_get_lang_regex() . '(?![0-9A-Za-z%_-]))?'
			. '#';

		$replacement = $home . '/' . $wp_rewrite->index;

		if ( $lang_slug ) {
			$replacement .= '/' . $lang_slug;
		}

		$url = preg_replace(
			$pattern,
			$replacement,
			$url
		);

		$url = preg_replace(
			'#' . preg_quote( $wp_rewrite->index ) . '/?$#',
			'',
			$url
		);

	} else {
		$pattern = '#^'
			. preg_quote( $home )
			. '(?:/' . bogo_get_lang_regex() . '(?![0-9A-Za-z%_-]))?'
			. '#';

		$replacement = $home;

		if ( $lang_slug ) {
			$replacement .= '/' . $lang_slug;
		}

		$url = preg_replace(
			$pattern,
			$replacement,
			$url
		);
	}

	if ( ! $tail_slashed ) {
		$url = untrailingslashit( $url );
	}

	return $url;
}


/**
 * Determines the language from the specified URL.
 *
 * @param string $url URL.
 */
function bogo_get_lang_from_url( $url = '' ) {
	if ( ! $url ) {
		$url = is_ssl() ? 'https://' : 'http://';
		$url .= $_SERVER['HTTP_HOST'];
		$url .= $_SERVER['REQUEST_URI'];
	}

	if ( $frag = strstr( $url, '#' ) ) {
		$url = substr( $url, 0, - strlen( $frag ) );
	}

	$home = set_url_scheme( get_option( 'home' ) );
	$home = trailingslashit( $home );

	$available_languages = array_map( 'bogo_lang_slug',
		bogo_available_locales()
	);

	$regex = '#^'
		. preg_quote( $home )
		. '(' . implode( '|', $available_languages ) . ')'
		. '/#';

	if ( preg_match( $regex, trailingslashit( $url ), $matches ) ) {
		return $matches[1];
	}

	if ( $query = wp_parse_url( $url, PHP_URL_QUERY ) ) {
		parse_str( $query, $query_vars );

		if ( isset( $query_vars['lang'] )
		and in_array( $query_vars['lang'], $available_languages ) ) {
			return $query_vars['lang'];
		}
	}

	return false;
}

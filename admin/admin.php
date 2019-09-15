<?php

require_once BOGO_PLUGIN_DIR . '/admin/includes/user.php';
require_once BOGO_PLUGIN_DIR . '/admin/includes/post.php';
require_once BOGO_PLUGIN_DIR . '/admin/includes/nav-menu.php';
require_once BOGO_PLUGIN_DIR . '/admin/includes/widgets.php';
require_once BOGO_PLUGIN_DIR . '/admin/includes/language-packs.php';
require_once BOGO_PLUGIN_DIR . '/admin/includes/terms-translation.php';

add_action( 'admin_init', 'bogo_upgrade' );

function bogo_upgrade() {
	$old_ver = bogo_get_prop( 'version' );
	$new_ver = BOGO_VERSION;

	if ( $old_ver != $new_ver ) {
		require_once BOGO_PLUGIN_DIR . '/admin/includes/upgrade.php';
		do_action( 'bogo_upgrade', $new_ver, $old_ver );
		bogo_set_prop( 'version', $new_ver );
	}
}

add_action( 'admin_enqueue_scripts', 'bogo_admin_enqueue_scripts' );

function bogo_admin_enqueue_scripts( $hook_suffix ) {
	wp_enqueue_style( 'bogo-admin',
		plugins_url( 'admin/includes/css/admin.css', BOGO_PLUGIN_BASENAME ),
		array(), BOGO_VERSION, 'all' );

	if ( is_rtl() ) {
		wp_enqueue_style( 'bogo-admin-rtl',
			plugins_url( 'admin/includes/css/admin-rtl.css', BOGO_PLUGIN_BASENAME ),
			array(), BOGO_VERSION, 'all' );
	}

	wp_enqueue_script( 'bogo-admin',
		plugins_url( 'admin/includes/js/admin.js', BOGO_PLUGIN_BASENAME ),
		array( 'jquery' ), BOGO_VERSION, true );

	$local_args = array(
		'saveAlert' => __(
			"The changes you made will be lost if you navigate away from this page.",
			'bogo' ),
		'apiSettings' => array(
			'root' => esc_url_raw( rest_url( 'bogo/v1' ) ),
			'namespace' => 'bogo/v1',
			'nonce' => ( wp_installing() && ! is_multisite() )
				? '' : wp_create_nonce( 'wp_rest' ),
		),
		'screenReaderText' => array(
			/* translators: accessibility text */
			'targetBlank' => __( '(opens in a new window)', 'bogo' ),
		),
		'availableLanguages' => bogo_available_languages( array(
			'exclude_enus_if_inactive' => true,
			'orderby' => 'value',
		) ),
		'defaultLocale' => bogo_get_default_locale(),
		'pagenow' => isset( $_GET['page'] ) ? trim( $_GET['page'] ) : '',
	);

	if ( 'post.php' == $hook_suffix && ! empty( $GLOBALS['post'] ) ) {
		$post = $GLOBALS['post'];
		$local_args = array_merge( $local_args, array(
			'post_id' => $post->ID,
		) );
	}

	wp_localize_script( 'bogo-admin', 'bogo', $local_args );
}

add_action( 'admin_menu', 'bogo_admin_menu' );

function bogo_admin_menu() {
	add_menu_page( __( 'Languages', 'bogo' ), __( 'Languages', 'bogo' ),
		'bogo_manage_language_packs', 'bogo', 'bogo_tools_page',
		'dashicons-translation', 73 ); // between Users (70) and Tools (75)

	$tools = add_submenu_page( 'bogo',
		__( 'Language Packs', 'bogo' ),
		__( 'Language Packs', 'bogo' ),
		'bogo_manage_language_packs', 'bogo', 'bogo_tools_page' );

	add_action( 'load-' . $tools, 'bogo_load_tools_page' );

	$available_locales = bogo_available_locales( array(
		'exclude_enus_if_inactive' => true,
		'current_user_can_access' => true,
	) );

	if ( 1 < count( $available_locales ) ) {
		$texts = add_submenu_page( 'bogo',
			__( 'Terms Translation', 'bogo' ),
			__( 'Terms Translation', 'bogo' ),
			'bogo_edit_terms_translation', 'bogo-texts', 'bogo_texts_page' );

		add_action( 'load-' . $texts, 'bogo_load_texts_page' );
	}
}

add_filter( 'set-screen-option', 'bogo_set_screen_options', 10, 3 );

function bogo_set_screen_options( $result, $option, $value ) {
	$bogo_screens = array(
		'languages_page_bogo_texts_per_page',
	);

	if ( in_array( $option, $bogo_screens ) ) {
		$result = $value;
	}

	return $result;
}

function bogo_load_tools_page() {
	require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );

	$action = isset( $_GET['action'] ) ? $_GET['action'] : '';
	$locale = isset( $_GET['locale'] ) ? $_GET['locale'] : null;

	if ( 'activate' == $action ) {
		check_admin_referer( 'bogo-tools' );

		if ( ! current_user_can( 'bogo_manage_language_packs' ) ) {
			wp_die( __( "You are not allowed to manage translations.", 'bogo' ) );
		}

		if ( 'en_US' == $locale ) {
			bogo_set_prop( 'enus_deactivated', false );

			$redirect_to = add_query_arg(
				array( 'message' => 'enus_activated' ),
				menu_page_url( 'bogo', false ) );
		} else {
			if ( wp_download_language_pack( $locale ) ) {
				$redirect_to = add_query_arg(
					array( 'locale' => $locale, 'message' => 'install_success' ),
					menu_page_url( 'bogo', false ) );
			} else {
				$redirect_to = add_query_arg(
					array( 'locale' => $locale, 'message' => 'install_failed' ),
					menu_page_url( 'bogo', false ) );
			}
		}

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'deactivate' == $action ) {
		check_admin_referer( 'bogo-tools' );

		if ( ! current_user_can( 'bogo_manage_language_packs' ) ) {
			wp_die( __( "You are not allowed to manage translations.", 'bogo' ) );
		}

		if ( 'en_US' == $locale ) {
			bogo_set_prop( 'enus_deactivated', true );

			$redirect_to = add_query_arg(
				array( 'message' => 'enus_deactivated' ),
				menu_page_url( 'bogo', false ) );
		} else {
			if ( bogo_delete_language_pack( $locale ) ) {
				$redirect_to = add_query_arg(
					array( 'locale' => $locale, 'message' => 'delete_success' ),
					menu_page_url( 'bogo', false ) );
			} else {
				$redirect_to = add_query_arg(
					array( 'locale' => $locale, 'message' => 'delete_failed' ),
					menu_page_url( 'bogo', false ) );
			}
		}

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'promote' == $action ) {
		check_admin_referer( 'bogo-tools' );

		if ( ! current_user_can( 'bogo_manage_language_packs' ) ) {
			wp_die( __( "You are not allowed to manage translations.", 'bogo' ) );
		}

		if ( 'en_US' == $locale || ! bogo_is_available_locale( $locale ) ) {
			$locale = '';
		}

		if ( update_option( 'WPLANG', $locale ) ) {
			$redirect_to = add_query_arg(
				array( 'locale' => $locale, 'message' => 'promote_success' ),
				menu_page_url( 'bogo', false ) );
		} else {
			$redirect_to = add_query_arg(
				array( 'locale' => $locale, 'message' => 'promote_failed' ),
				menu_page_url( 'bogo', false ) );
		}

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'translate' == $action ) {
		check_admin_referer( 'bogo-tools' );

		if ( ! current_user_can( 'bogo_edit_terms_translation', $locale ) ) {
			wp_die( __( "You are not allowed to edit translations.", 'bogo' ) );
		}

		$is_active = ( 'en_US' == $locale )
			? ! bogo_is_enus_deactivated()
			: bogo_is_available_locale( $locale );

		if ( ! bogo_is_default_locale( $locale ) && $is_active ) {
			$redirect_to = add_query_arg(
				array( 'locale' => $locale ),
				menu_page_url( 'bogo-texts', false ) );

			wp_safe_redirect( $redirect_to );
			exit();
		}
	}

	$current_screen = get_current_screen();

	add_filter( 'manage_' . $current_screen->id . '_columns',
		array( 'Bogo_Language_Packs_List_Table', 'define_columns' ) );
}

function bogo_tools_page() {
	$list_table = new Bogo_Language_Packs_List_Table();
	$list_table->prepare_items();

?>
<div class="wrap">

<h1 class="wp-heading-inline"><?php
	echo esc_html( __( 'Language Packs', 'bogo' ) );
?></h1>

<?php
	if ( ! empty( $_REQUEST['s'] ) ) {
		echo sprintf(
			'<span class="subtitle">%s</span>',
			sprintf(
				__( 'Search results for &#8220;%s&#8221;', 'bogo' ),
				esc_html( $_REQUEST['s'] )
			)
		);
	}
?>

<hr class="wp-header-end">

<?php bogo_admin_notice(); ?>

<?php $list_table->views(); ?>

<form method="get" action="" id="bogo-language-packs">
	<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
	<?php $list_table->search_box( __( 'Search Language', 'bogo' ), 'bogo-language-packs' ); ?>
	<?php $list_table->display(); ?>
</form>

</div>
<?php
}

function bogo_load_texts_page() {
	$action = isset( $_POST['action'] ) ? $_POST['action'] : '';

	if ( 'save' == $action ) {
		check_admin_referer( 'bogo-edit-text-translation' );

		$locale = isset( $_POST['locale'] ) ? $_POST['locale'] : null;

		if ( ! current_user_can( 'bogo_edit_terms_translation' ) ) {
			wp_die( __( "You are not allowed to edit translations.", 'bogo' ) );
		}

		$entries = array();

		foreach ( (array) bogo_terms_translation( $locale ) as $item ) {
			$entries[] = array(
				'singular' => $item['name'],
				'translations' => array( $item['translated'] ),
				'context' => preg_replace( '/:.*$/', '', $item['name'] ),
			);
		}

		foreach ( $_POST as $p_key => $p_val ) {
			if ( in_array( $p_key, array( 'action', 'locale', 'submit', 'paged' ) )
			|| substr( $p_key, 0, 1 ) == '_' ) {
				continue;
			}

			$entries[] = array(
				'singular' => $p_key,
				'translations' => array( $p_val ),
				'context' => preg_replace( '/:.*$/', '', $p_key ),
			);
		}

		if ( Bogo_POMO::export( $locale, $entries ) ) {
			$message = 'translation_saved';
		} else {
			$message = 'translation_failed';
		}

		$redirect_to = add_query_arg(
			array(
				'locale' => $locale,
				'message' => $message,
				'paged' => isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1,
			),
			menu_page_url( 'bogo-texts', false )
		);

		wp_safe_redirect( $redirect_to );
		exit();

	} else {
		$current_screen = get_current_screen();

		add_filter( 'manage_' . $current_screen->id . '_columns',
			array( 'Bogo_Terms_Translation_List_Table', 'define_columns' ) );

		add_screen_option( 'per_page', array(
			'label' => __( 'Items', 'bogo' ),
			'default' => 20,
		) );
	}
}

function bogo_texts_page() {
	$list_table = new Bogo_Terms_Translation_List_Table();
	$list_table->prepare_items();

?>
<div class="wrap">

<h1 class="wp-heading-inline"><?php
	echo esc_html( __( 'Terms Translation', 'bogo' ) );
?></h1>

<?php
	if ( ! empty( $_REQUEST['s'] ) ) {
		echo sprintf(
			'<span class="subtitle">%s</span>',
			sprintf(
				__( 'Search results for &#8220;%s&#8221;', 'bogo' ),
				esc_html( $_REQUEST['s'] )
			)
		);
	}
?>

<hr class="wp-header-end">

<?php bogo_admin_notice(); ?>

<form action="" method="get">
<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
<input type="hidden" name="locale" value="<?php echo esc_attr( $_REQUEST['locale'] ); ?>" />
<?php
	$list_table->search_box(
		__( 'Search Translation', 'bogo' ), 'bogo-terms-translation'
	);
?>
</form>

<form action="" method="post" id="bogo-terms-translation">
<input type="hidden" name="action" value="save" />
<input type="hidden" name="paged" value="<?php echo absint( $_GET['paged'] ); ?>" />
<?php
	wp_nonce_field( 'bogo-edit-text-translation' );
	$list_table->display();
?>
</form>
</div>
<?php
}

function bogo_admin_notice( $reason = '' ) {
	if ( empty( $reason ) && isset( $_GET['message'] ) ) {
		$reason = $_GET['message'];
	}

	if ( 'install_success' == $reason ) {
		$message = __( "Translation installed successfully.", 'bogo' );
	} elseif ( 'install_failed' == $reason ) {
		$message = __( "Translation install failed.", 'bogo' );
	} elseif ( 'promote_success' == $reason ) {
		$message = __( "Site language set successfully.", 'bogo' );
	} elseif ( 'promote_failed' == $reason ) {
		$message = __( "Setting site language failed.", 'bogo' );
	} elseif ( 'delete_success' == $reason ) {
		$message = __( "Translation uninstalled successfully.", 'bogo' );
	} elseif ( 'delete_failed' == $reason ) {
		$message = __( "Translation uninstall failed.", 'bogo' );
	} elseif ( 'enus_deactivated' == $reason ) {
		$message = __( "English (United States) deactivated.", 'bogo' );
	} elseif ( 'enus_activated' == $reason ) {
		$message = __( "English (United States) activated.", 'bogo' );
	} elseif ( 'translation_saved' == $reason ) {
		$message = __( "Translation saved.", 'bogo' );
	} elseif ( 'translation_failed' == $reason ) {
		$message = __( "Saving translation failed.", 'bogo' );
	} else {
		return false;
	}

	if ( '_failed' == substr( $reason, -7 ) ) {
		echo sprintf(
			'<div class="error notice notice-error is-dismissible"><p>%s</p></div>',
			esc_html( $message ) );
	} else {
		echo sprintf(
			'<div class="updated notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html( $message ) );
	}
}

<?php
/**
 * Super Editor.
 *
 * @package    WordPress
 * @subpackage Super_Editor
 * @author     Barry Ceelen <b@rryceelen.com>
 * @license    GPL-3.0+
 * @link       https://github.com/barryceelen/wp-super-editor
 * @copyright  2015 Barry Ceelen
 */

/**
 * Enable editors to add/edit users and edit theme options.
 *
 * Note: Editors can not add/edit/delete administrators, this is prevented
 *       by the filters hooked in to editable_roles and map_meta_cap.
 *
 * @since 1.0.0
 *
 * @param array $allcaps An array of all the user's capabilities.
 */
function super_editor_boost_editor( $allcaps ) {

	if ( isset( $allcaps['editor'] ) && true === $allcaps['editor'] ) {
		$arr = array(
			'list_users',
			'create_users',
			'edit_users',
			'promote_users',
			'delete_users',
			'remove_users',
			'edit_theme_options',
		);
		foreach ( $arr as $cap ) {
			$allcaps[ $cap ] = true;
		}
	}

	return $allcaps;
}

/**
 * Allow authors to add/edit pages.
 *
 * @since 1.0.0
 *
 * @param array $allcaps An array of all the user's capabilities.
 */
function super_editor_boost_author( $allcaps ) {

	if ( isset( $allcaps['author'] ) && true === $allcaps['author'] ) {
		$arr = array(
			'edit_pages',
			'edit_others_pages',
			'edit_published_pages',
			'publish_pages',
			'delete_pages',
			'delete_published_pages',
		);
		foreach ( $arr as $cap ) {
			$allcaps[ $cap ] = true;
		}
	}

	return $allcaps;
}

/**
 * Remove 'Administrator' from the list of editable roles if the current user is not an admin.
 *
 * @since 1.0.0
 *
 * @param array $roles List of roles.
 */
function super_editor_filter_editable_roles( $roles ) {
	if ( isset( $roles['administrator'] ) && ! current_user_can( 'manage_options' ) ) {
		unset( $roles['administrator'] );
	}
	return $roles;
}

/**
 * Prevent users without the 'manage_options' capability from editing or deleting administrators.
 *
 * @since 1.0.0
 *
 * @param array $caps    The user's actual capabilities.
 * @param str   $cap     Capability name.
 * @param int   $user_id The user ID.
 * @param array $args    Adds the context to the cap. Typically the object ID.
 */
function super_editor_filter_map_meta_cap( $caps, $cap, $user_id, $args ) {

	switch ( $cap ) {
		case 'edit_user' :
		case 'remove_user' :
		case 'promote_user' :
			if ( isset( $args[0] ) && $args[0] === $user_id ) {
				break;
			} elseif ( ! isset( $args[0] ) ) {
				$caps[] = 'do_not_allow';
			}
			$other = new WP_User( absint( $args[0] ) );
			if ( $other->has_cap( 'administrator' ) ) {
				if ( ! current_user_can( 'manage_options' ) ) {
					$caps[] = 'do_not_allow';
				}
			}
			break;
		case 'delete_user' :
		case 'delete_users' :
			if ( ! isset( $args[0] ) ) {
				break;
			}
			$other = new WP_User( absint( $args[0] ) );
			if ( $other->has_cap( 'administrator' ) ) {
				if ( ! current_user_can( 'manage_options' ) ) {
					$caps[] = 'do_not_allow';
				}
			}
			break;
		default:
			break;
	}

	return $caps;
}

/**
 * Remove 'Appearance' submenu pages for editors.
 *
 * @since 1.0.0
 */
function super_editor_remove_appearance_submenu_pages() {

	if ( current_user_can( 'author' ) || current_user_can( 'editor' ) ) {

		// Remove the theme selection submenu.
		remove_submenu_page( 'themes.php', 'themes.php' );

		// Remove the widgets submenu.
		// remove_submenu_page( 'themes.php', 'widgets.php' );

		/*
		 * Remove the customize submenu.
		 * See: http://stackoverflow.com/questions/25788511/remove-submenu-page-customize-php-in-wordpress-4-0
		 * Todo: Probably better to remove the 'customize' capability?
		 */
		$arr = array();
		$arr[] = 'customize.php';
		$url = add_query_arg( 'return', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 'customize.php' );
		$arr[] = $url;
		if ( current_theme_supports( 'custom-header' ) && current_user_can( 'customize' ) ) {
			$arr[] = add_query_arg( 'autofocus[control]', 'header_image', $url );
			$arr[] = 'custom-header';
		}
		if ( current_theme_supports( 'custom-background' ) && current_user_can( 'customize' ) ) {
			$arr[] = add_query_arg( 'autofocus[control]', 'background_image', $url );
			$arr[] = 'custom-background';
		}
		foreach ( $arr as $url ) {
			remove_submenu_page( 'themes.php', $url );
		}
	}
}

/**
 * Remove 'tools' page for non-admins.
 *
 * @todo If any plugin adds items to the tools menu, this would probably be a problem.
 */
function super_editor_remove_tools_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		remove_menu_page( 'tools.php' );
	}
}

/**
 * Wrapper for the wp_die() function.
 *
 * @since 1.0.0
 */
function super_editor_maybe_die() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
	}
}

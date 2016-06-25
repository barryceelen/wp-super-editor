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
 *
 * Plugin Name:       Super Editor
 * Plugin URI:        https://github.com/barryceelen/wp-super-editor
 * Description:       Allow editors to add and edit users and manage widgets and menus. Allow authors to take on the role of editors by letting them edit pages.
 * Version:           1.0.0
 * Author:            Barry Ceelen
 * Author URI:        https://github.com/barryceelen
 * Text Domain:       super-editor
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/barryceelen/wp-super-editor
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Allow editors to add and edit users, and manage menus.
 *
 * See: http://wordpress.stackexchange.com/questions/4479/editor-can-create-any-new-user-except-administrator
 *
 * We're allowing a user with the editor role to edit menus.
 * This requires the 'edit_theme_options' capabilities which also
 * allows the user to edit other theme options.
 *
 * Todo: For completeness, we'd also need to prevent the user from doing
 * anything the 'edit_theme_options' cap allows but is not menu related.
 */

if ( is_admin() ) {

	require_once( 'inc/functions.php' );

	// Enable editors to add/edit users and edit theme options.
	add_filter( 'user_has_cap', 'super_editor_boost_editor' );

	// Allow authors to add/edit pages.
	add_filter( 'user_has_cap', 'super_editor_boost_author' );

	// Remove 'Administrator' from the list of editable roles if the current user is not an admin.
	add_filter( 'editable_roles', 'super_editor_filter_editable_roles' );

	// Prevent users without the 'manage_options' capability from editing or deleting administrators.
	add_filter( 'map_meta_cap', 'super_editor_filter_map_meta_cap', 10, 4 );

	add_action( 'admin_menu', 'super_editor_remove_appearance_submenu_pages' );
	add_action( 'admin_menu', 'super_editor_remove_tools_page' );

	add_action( 'load-themes.php', 'super_editor_maybe_die' );
	add_action( 'load-widgets.php', 'super_editor_maybe_die' );
	add_action( 'load-widgets.php', 'super_editor_maybe_die' );
	add_action( 'load-customize.php', 'super_editor_maybe_die' );
	add_action( 'load-tools.php', 'super_editor_maybe_die' );
}


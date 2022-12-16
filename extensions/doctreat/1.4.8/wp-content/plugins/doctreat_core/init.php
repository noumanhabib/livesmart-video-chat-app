<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://themeforest.net/user/amentotech/portfolio
 * @since             1.0
 * @package           Doctreat Core
 *
 * @wordpress-plugin
 * Plugin Name:       Doctreat Core
 * Plugin URI:        https://themeforest.net/user/amentotech/portfolio
 * Description:       This plugin is used for creating custom post types and other functionality for Doctreat Theme
 * Version:           1.4.8
 * Author:            Amentotech
 * Author URI:        https://themeforest.net/user/amentotech
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       doctreat_core
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if( !function_exists( 'doctreat_core_load_last' ) ) {
	function doctreat_core_load_last() {
		$wp_path_to_this_file = preg_replace('/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR."/$2", __FILE__);
		$this_plugin = plugin_basename(trim($wp_path_to_this_file));
		$active_plugins = get_option('active_plugins');
		$this_plugin_key = array_search($this_plugin, $active_plugins);
			array_splice($active_plugins, $this_plugin_key, 1);
			array_push($active_plugins, $this_plugin);
			update_option('active_plugins', $active_plugins);
	}
	
	add_action("activated_plugin", "doctreat_core_load_last");
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-elevator-activator.php
 */
if( !function_exists( 'activate_doctreat' ) ) {
	function activate_doctreat() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-system-activator.php';
		Doctreat_Activator::activate();
		
	} 
}
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-elevator-deactivator.php
 */
if( !function_exists( 'deactivate_doctreat' ) ) {
	function deactivate_doctreat() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-system-deactivator.php';
		Doctreat_Deactivator::deactivate();
	}
}

register_activation_hook( __FILE__, 'activate_doctreat' );
register_deactivation_hook( __FILE__, 'deactivate_doctreat' );

/**
 * Plugin configuration file,
 * It include getter & setter for global settings
 */
require plugin_dir_path( __FILE__ ) . 'config.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-system.php';
require plugin_dir_path( __FILE__ ) . 'chat/class-chat-system.php';
include doctreat_template_exsits( 'hooks/hooks' );
include doctreat_template_exsits( 'helpers/EmailHelper' );
include doctreat_template_exsits( 'helpers/LiveSmartHelper' );
include doctreat_template_exsits( 'shortcodes/class-authentication' );
include doctreat_template_exsits( 'libraries/mailchimp/class-mailchimp' );

require plugin_dir_path( __FILE__ ) . 'widgets/config.php';
require plugin_dir_path( __FILE__ ) . 'elementor/base.php';
require plugin_dir_path( __FILE__ ) . 'elementor/config.php';

require plugin_dir_path( __FILE__ ) . 'libraries/mailchimp/class-mailchimp-oath.php';
require plugin_dir_path( __FILE__ ) . 'helpers/register.php';
require plugin_dir_path( __FILE__ ) . 'import-users/class-readcsv.php';
require plugin_dir_path( __FILE__ ) . 'admin/settings/settings.php';
include doctreat_template_exsits( 'import-users/class-import-user' );
require plugin_dir_path( __FILE__ ) . 'admin/metaboxes/classes/class-metaboxes.php';
require plugin_dir_path( __FILE__ ) . 'admin/metaboxes/classes/class-form-attributes.php';
require plugin_dir_path( __FILE__ ) . 'demo-import/importer.php';
require plugin_dir_path( __FILE__ ) . '/admin/theme-settings/init.php'; //Theme Settings

/**
 * Get template from plugin or theme.
 *
 * @param string $file  Template file name.
 * @param array  $param Params to add to template.
 *
 * @return string
 */
function doctreat_template_exsits( $file, $param = array() ) {
	extract( $param );
	if ( is_dir( get_stylesheet_directory() . '/extend/' ) ) {
		if ( file_exists( get_stylesheet_directory() . '/extend/' . $file . '.php' ) ) {
			$template_load = get_stylesheet_directory() . '/extend/' . $file . '.php';
		} else {
			$template_load = DoctreatGlobalSettings::get_plugin_path() . '/' . $file . '.php';
		}
	} else {
		$template_load = DoctreatGlobalSettings::get_plugin_path() . '/' . $file . '.php';
	}
	return $template_load;
}


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
if( !function_exists( 'run_Doctreat' ) ) {
	function run_Doctreat() {
	
		$plugin = new Doctreat_Core();
		$plugin->run();
	
	}
	
	run_Doctreat();
}

/**
 * @init            Save rewrite slugs
 * @package         Rewrite Slug
 * @subpackage      combo-wp-rewrite-slugs/admin/partials
 * @since           1.0
 * @desc            This Function Will Produce All Tabs View.
 */
if (!function_exists('doctreat_set_custom_rewrite_rule')) {
	function doctreat_set_custom_rewrite_rule() {
		global $wp_rewrite;
		$settings = (array) doctreat_get_theme_settings();
		
		if( !empty( $settings['post'] ) ){
			foreach ( $settings['post'] as $post_type => $slug ) {
				if(!empty( $slug )){
					$args = get_post_type_object($post_type);
					$args->rewrite["slug"] = $slug;
					register_post_type($args->name, $args);
				}
			}
		}

		if( !empty( $settings['term'] ) ){
			foreach ( $settings['term'] as $term => $slug ) {
				if(!empty( $slug ) ){
					$tax = get_taxonomy($term);
					$tax->rewrite["slug"] = $slug;
					register_taxonomy($term, $tax->object_type[0],(array)$tax);
				}
			}
		}

		$wp_rewrite->flush_rules();
	} 
	add_action('init', 'doctreat_set_custom_rewrite_rule');
}

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
add_action( 'init', 'doctreat_load_textdomain' );
function doctreat_load_textdomain() {
  load_plugin_textdomain( 'doctreat_core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}
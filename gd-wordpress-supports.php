<?php
/**
 * Plugin Name:     Gd Wordpress Supports
 * Plugin URI:      https://github.com/ihsanberahim/gd-wordpress-supports
 * Description:     Vendor for themes or plugins developed by GoalDriven.co
 * Author:          IhsanBerahim <ihsanberahim@gmail.com>
 * Author URI:      https://blog.ihsanberahim.com
 * Text Domain:     gd-wordpress-supports
 * Domain Path:     /languages
 * Version:         1.2.0
 *
 * @package         Gd_Wordpress_Supports
 */

try {
	require_once( __DIR__ . '/vendor/autoload.php' );
} catch ( Exception $e ) {
	return;
}

use \Illuminate\Support\Str;

define( 'GDWPS_ACTIVE', true );

/**
 * Sort this plugin to priorities
 */
add_filter( 'pre_update_option_active_plugins', function ( $plugins ) {
	return collect( $plugins )->sortBy( function ( $plugin, $key ) use ( $plugins ) {
		return Str::is( 'gd-wordpress-supports*', $plugin ) ? 0 : count( $plugins ) - $key;
	} )->toArray();
}, 11, 1 );

function gdwps_activation_hook() {
	update_option( 'active_plugins', get_option( 'active_plugins' ) );
}

register_activation_hook( __FILE__, 'gdwps_activation_hook' );


add_action( 'setup_theme', function () {
	/**
	 * Enable version checker
	 */
	if ( $checker = gd_setup_plugin_update_checker( __FILE__ ) ) {
		// $checker->setBranch( 'master' ); // temporary disabled as sometime it not work.
	}
} );

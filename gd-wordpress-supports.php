<?php
/**
 * Plugin Name:     Gd Wordpress Supports
 * Plugin URI:      https://goaldriven.co/?s=Gd+Wordpress+Supports
 * Description:     Vendor for themes or plugins developed by GoalDriven.co
 * Author:          IhsanBerahim <ihsanberahim@gmail.com>
 * Author URI:      https://blog.ihsanberahim.com
 * Text Domain:     gd-wordpress-supports
 * Domain Path:     /languages
 * Version:         1.0.1
 *
 * @package         Gd_Wordpress_Supports
 */

try {
	require_once( __DIR__ . '/vendor/autoload.php' );
} catch ( Exception $e ) {
	return;
}

define( 'GDWPS_ACTIVE', true );

use \Illuminate\Support\Str;

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


/**
 * Enable version checker
 */
$updateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/ihsanberahim/gd-wordpress-supports',
	__FILE__,
	'gd-wordpress-supports'
);

$updateChecker->setBranch('master');

<?php
/**
 * Plugin Name: Sell Your Phone
 * Plugin URI: 
 * Description: Easily create a phone resale system on your WordPress site. Allow users to submit device details, get instant quotes, and manage buyback requests seamlessly.
 * Version: 1.0.0
 * Author: PIXELCODE
 * Author URI: https://portfolio-client-y9gw.onrender.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pixelcode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

// Define constants for plugin paths
define( 'PIXELCODE_URL', plugin_dir_url( __FILE__ ) );
define( 'PIXELCODE_PATH', plugin_dir_path( __FILE__ ) );


// Activation hook: create tables
register_activation_hook( __FILE__, 'syp_create_tables' );
function syp_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Conditions table
    $conditions_table = $wpdb->prefix . 'pixelcode_phone_conditions';
    $sql1 = "CREATE TABLE $conditions_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(200) NOT NULL,
        slug varchar(200) NOT NULL,
        description text NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Carrier table
    $carrier_table = $wpdb->prefix . 'pixelcode_phone_carriers';
    $sql2 = "CREATE TABLE $carrier_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(200) NOT NULL,
        slug varchar(200) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Storage table
    $storage_table = $wpdb->prefix . 'pixelcode_phone_storage';
    $sql3 = "CREATE TABLE $storage_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(200) NOT NULL,
        slug varchar(200) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql1 );
    dbDelta( $sql2 );
    dbDelta( $sql3 );

    // Auto-generate slug for old rows (if any)
    $rows = $wpdb->get_results( "SELECT * FROM $conditions_table" );
    foreach ( $rows as $row ) {
        if ( empty( $row->slug ) ) {
            $wpdb->update(
                $conditions_table,
                [ 'slug' => sanitize_title( $row->title ) ],
                [ 'id'   => $row->id ],
                [ '%s' ],
                [ '%d' ]
            );
        }
    }
}


// Include the main plugin class
require_once PIXELCODE_PATH . 'includes/class-pixelcode-main.php';

// Include the product data tab php
require_once PIXELCODE_PATH . 'includes/class-product-data-tab.php';

// Include the functions php
require_once PIXELCODE_PATH . './functions.php';


// Initialize the plugin
function pixelcode_run_plugin() {
    $plugin = new Pixelcode_Main();
    $plugin->run();
}

pixelcode_run_plugin();

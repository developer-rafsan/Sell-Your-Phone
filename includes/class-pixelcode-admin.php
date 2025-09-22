<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pixelcode_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        // Hook into admin menu
        add_action( 'admin_menu', array( $this, 'add_buyback_menu' ), 99 );
        add_action( 'admin_menu', array( $this, 'remove_buyback_duplicate_submenu' ), 999 );
        add_action( 'admin_menu', array( $this, 'pixelcode_remove_wc_product_submenus' ), 999 );
    }


    /**
     * Remove WooCommerce Product submenus (Reviews & Attributes)
     */
    public function pixelcode_remove_wc_product_submenus() {
        // Remove Product Reviews
        remove_submenu_page(
            'edit.php?post_type=product',
            'product-reviews'
        );

        // Remove Product Attributes
        remove_submenu_page(
            'edit.php?post_type=product',
            'product_attributes'
        );
    }

    /**
     * Add Buy Back main menu with submenus
     */
    public function add_buyback_menu() {
        // Main menu - Buy Back
        add_menu_page(
            'Buy Back',
            'Buy Back',
            'manage_woocommerce',
            'pixelcode-buyback',
            array( $this, 'render_buyback_page' ),
            'dashicons-products',
            56
        );

        // Reports submenu
        add_submenu_page(
            'pixelcode-buyback',
            'Reports',
            'Reports',
            'manage_woocommerce',
            'pixelcode-reports',
            array( $this, 'render_reports_page' )
        );

        // Condition submenu
        add_submenu_page(
            'pixelcode-buyback',
            'Condition',
            'Condition',
            'manage_woocommerce',
            'pixelcode-condition',
            array( $this, 'render_condition_page' )
        );

        // Carrier submenu
        add_submenu_page(
            'pixelcode-buyback',
            'Carrier',
            'Carrier',
            'manage_woocommerce',
            'pixelcode-carrier',
            array( $this, 'render_carrier_page' )
        );

        // Storage submenu
        add_submenu_page(
            'pixelcode-buyback',
            'Storage',
            'Storage',
            'manage_woocommerce',
            'pixelcode-storage',
            array( $this, 'render_storage_page' )
        );
    }

    /**
     * Remove the default duplicate submenu
     */
    public function remove_buyback_duplicate_submenu() {
        remove_submenu_page( 'pixelcode-buyback', 'pixelcode-buyback' );
    }

    /**
     * Main Buy Back page callback
     */
    public function render_buyback_page() {
        echo '<div class="wrap"><h1>Buy Back Dashboard</h1><p>Welcome to the Buy Back management system.</p></div>';
    }

    /**
     * Condition page callback
     */
    public function render_condition_page() {
        $file = plugin_dir_path( __FILE__ ) . '../templates/condition.php';
        if ( file_exists( $file ) ) {
            include $file;
        } else {
            echo '<div class="wrap"><h1>Condition</h1><p>Template file not found.</p></div>';
        }
    }

    /**
     * Carrier page callback
     */
    public function render_carrier_page() {
        $file = plugin_dir_path( __FILE__ ) . '../templates/carrier.php';
        if ( file_exists( $file ) ) {
            include $file;
        } else {
            echo '<div class="wrap"><h1>Carrier</h1><p>Template file not found.</p></div>';
        }
    }

    /**
     * Storage page callback
     */
    public function render_storage_page() {
        $file = plugin_dir_path( __FILE__ ) . '../templates/storage.php';
        if ( file_exists( $file ) ) {
            include $file;
        } else {
            echo '<div class="wrap"><h1>Storage</h1><p>Template file not found.</p></div>';
        }
    }

    /**
     * Reports page callback
     */
    public function render_reports_page() {
        $file = plugin_dir_path( __FILE__ ) . '../templates/reports.php';
        if ( file_exists( $file ) ) {
            include $file;
        } else {
            echo '<div class="wrap"><h1>Reports</h1><p>Template file not found.</p></div>';
        }
    }
}
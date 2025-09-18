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
        add_action( 'admin_menu', array( $this, 'add_wc_submenu' ), 99 );
    }

    /**
     * Add submenu under WooCommerce Products menu
     */
    public function add_wc_submenu() {
        // Condition submenu
        add_submenu_page(
            'edit.php?post_type=product',      
            'Condition',                        
            'Condition',                        
            'manage_woocommerce',               
            'pixelcode-condition',             
            array( $this, 'render_condition_page' ) 
        );

        // Carrier submenu
        add_submenu_page(
            'edit.php?post_type=product',      
            'Carrier',                          
            'Carrier',                          
            'manage_woocommerce',               
            'pixelcode-carrier',               
            array( $this, 'render_carrier_page' ) 
        );

        // Storage submenu
        add_submenu_page(
            'edit.php?post_type=product',      
            'Storage',                          
            'Storage',                          
            'manage_woocommerce',               
            'pixelcode-storage',               
            array( $this, 'render_storage_page' ) 
        );
    }


    public function render_condition_page() {
        $file = plugin_dir_path( __FILE__ ) . '../templates/condition.php';

        if ( file_exists( $file ) ) {
            include $file;
        } else {
            echo '<div class="wrap"><h1>Condition</h1><p>Template file not found.</p></div>';
        }
    }

    public function render_carrier_page() {
        $file = plugin_dir_path( __FILE__ ) . '../templates/carrier.php';

        if ( file_exists( $file ) ) {
            include $file;
        } else {
            echo '<div class="wrap"><h1>Condition</h1><p>Template file not found.</p></div>';
        }
    }

    public function render_storage_page() {
        $file = plugin_dir_path( __FILE__ ) . '../templates/storage.php';

        if ( file_exists( $file ) ) {
            include $file;
        } else {
            echo '<div class="wrap"><h1>Condition</h1><p>Template file not found.</p></div>';
        }
    }
}
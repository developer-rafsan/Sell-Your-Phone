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
        add_submenu_page(
            'edit.php?post_type=product',      
            'Sell Your Phone',                  
            'Sell Your Phone',                 
            'manage_woocommerce',               
            'pixelcode-sell-your-phone',       
            array( $this, 'render_sell_phone_page' ) 
        );
    }

    /**
     * Callback function for page content
     */
    public function render_sell_phone_page() {
        ?>
        <div class="wrap">
            <h1>Sell Your Phone Submissions</h1>
            <p>Here you can manage phone resale requests and submissions.</p>
        </div>
        <?php
    }
}
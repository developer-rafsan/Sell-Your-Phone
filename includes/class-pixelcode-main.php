<?php

class Pixelcode_Main {

    public function run() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // Load other classes and files
        require_once PIXELCODE_PATH . 'includes/class-pixelcode-public.php';
        require_once PIXELCODE_PATH . 'includes/class-pixelcode-admin.php';

        new Pixelcode_Public();
        new Pixelcode_Admin();
    }

    public function enqueue_public_assets() {
        // Enqueue our renamed public stylesheet (can be empty or used for overrides)
        $css_file = PIXELCODE_PATH . 'assets/css/pixelcode-public.css';
        if (file_exists($css_file)) {
            wp_enqueue_style( 'pixelcode-public-style', PIXELCODE_URL . 'assets/css/pixelcode-public.css', array(), filemtime( $css_file ) );
        }

        // Enqueue our renamed public JS
        $js_file = PIXELCODE_PATH . 'assets/js/pixelcode-public.js';
        if (file_exists($js_file)) {
            wp_enqueue_script( 'pixelcode-public-script', PIXELCODE_URL . 'assets/js/pixelcode-public.js', array( 'jquery' ), filemtime( $js_file ), true );
            wp_localize_script( 'pixelcode-public-script', 'pixelcode_ajax', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'pixelcode_public_nonce' ),
            ) );
        }
    }

    public function enqueue_admin_assets() {
        // Enqueue our renamed admin CSS
        $css_file = PIXELCODE_PATH . 'assets/css/pixelcode-admin.css';
        if (file_exists($css_file)) {
            wp_enqueue_style( 'pixelcode-admin-style', PIXELCODE_URL . 'assets/css/pixelcode-admin.css', array(), filemtime( $css_file ) );
        }

        // Enqueue our renamed admin JS
        $js_file = PIXELCODE_PATH . 'assets/js/pixelcode-admin.js';
        if (file_exists($js_file)) {
            wp_enqueue_script( 'pixelcode-admin-script', PIXELCODE_URL . 'assets/js/pixelcode-admin.js', array( 'jquery' ), filemtime( $js_file ), true );
            wp_localize_script( 'pixelcode-admin-script', 'pixelcode_admin_ajax', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'pixelcode_admin_nonce' ),
            ) );
        }
    }
}
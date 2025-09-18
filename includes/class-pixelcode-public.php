<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pixelcode_Public {

    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode( 'pixelcode_sell_your_form', array( $this, 'render_form_shortcode' ) );
    }

    /**
     * Renders the form by loading the template.
     */
    public function render_form_shortcode( $atts ) {
        ob_start();
        include PIXELCODE_PATH . 'templates/public-form.php';
        return ob_get_clean();
    }
}
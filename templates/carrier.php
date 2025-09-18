<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$table = $wpdb->prefix . 'pixelcode_phone_carriers';

// Save new carrier
if ( isset($_POST['carrier_title']) && check_admin_referer('save_carrier','carrier_nonce') ) {

    $title = sanitize_text_field( $_POST['carrier_title'] );

    // Slug: user input or auto-generate
    $slug_input = isset($_POST['carrier_slug']) && !empty($_POST['carrier_slug']) 
                  ? sanitize_title( $_POST['carrier_slug'] ) 
                  : sanitize_title( $title );

    $wpdb->insert(
        $table,
        [
            'title' => $title,
            'slug'  => $slug_input,
        ],
        [ '%s', '%s' ]
    );

    echo '<div class="updated notice"><p>Carrier saved successfully!</p></div>';
}

// Auto-generate slug for old rows if missing
$rows = $wpdb->get_results( "SELECT * FROM $table" );
foreach ( $rows as $row ) {
    if ( empty( $row->slug ) ) {
        $wpdb->update(
            $table,
            [ 'slug' => sanitize_title( $row->title ) ],
            [ 'id'   => $row->id ],
            [ '%s' ],
            [ '%d' ]
        );
    }
}

// Fetch all carriers
$carriers = $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC" );
?>

<div class="wrap">
    <h1>Phone Carriers</h1>
    <div style="display: flex; gap: 30px; align-items: flex-start;">

        <!-- Left side form -->
        <div style="flex: 1; background: #fff; padding:20px; border:1px solid #ddd; border-radius:8px;">
            <h2>Add New Carrier</h2>
            <form method="post" action="">
                <?php wp_nonce_field( 'save_carrier', 'carrier_nonce' ); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="carrier_title">Carrier Title</label></th>
                        <td><input type="text" name="carrier_title" id="carrier_title" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="carrier_slug">Carrier Slug</label></th>
                        <td><input type="text" name="carrier_slug" id="carrier_slug" class="regular-text"
                                placeholder="Optional, leave blank to auto-generate"></td>
                    </tr>
                </table>

                <?php submit_button( 'Save Carrier' ); ?>
            </form>
        </div>

        <!-- Right side table -->
        <div style="flex: 2; background: #fff; padding:20px; border:1px solid #ddd; border-radius:8px;">
            <h2>Carrier List</h2>
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="40%">Title</th>
                        <th width="40%">Slug</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ( $carriers ) {
                        foreach ( $carriers as $carrier ) {
                            echo '<tr>';
                            echo '<td>' . esc_html( $carrier->id ) . '</td>';
                            echo '<td><strong>' . esc_html( $carrier->title ) . '</strong></td>';
                            echo '<td>' . esc_html( isset($carrier->slug) ? $carrier->slug : sanitize_title($carrier->title) ) . '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="3">No carriers found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
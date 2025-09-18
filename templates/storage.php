<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$table = $wpdb->prefix . 'pixelcode_phone_storage';

// Save new storage
if ( isset($_POST['storage_title']) && check_admin_referer('save_storage','storage_nonce') ) {

    $title = sanitize_text_field( $_POST['storage_title'] );

    // Slug: user input or auto-generate
    $slug_input = isset($_POST['storage_slug']) && !empty($_POST['storage_slug']) 
                  ? sanitize_title( $_POST['storage_slug'] ) 
                  : sanitize_title( $title );

    $wpdb->insert(
        $table,
        [
            'title' => $title,
            'slug'  => $slug_input,
        ],
        [ '%s', '%s' ]
    );

    echo '<div class="updated notice"><p>Storage option saved successfully!</p></div>';
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

// Fetch all storage options
$storages = $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC" );
?>

<div class="wrap">
    <h1>Phone Storage Options</h1>
    <div style="display: flex; gap: 30px; align-items: flex-start;">

        <!-- Left side form -->
        <div style="flex: 1; background: #fff; padding:20px; border:1px solid #ddd; border-radius:8px;">
            <h2>Add New Storage Option</h2>
            <form method="post" action="">
                <?php wp_nonce_field( 'save_storage', 'storage_nonce' ); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="storage_title">Storage Size</label></th>
                        <td><input type="text" name="storage_title" id="storage_title" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="storage_slug">Slug</label></th>
                        <td><input type="text" name="storage_slug" id="storage_slug" class="regular-text"
                                placeholder="Optional, leave blank to auto-generate"></td>
                    </tr>
                </table>

                <?php submit_button( 'Save Storage' ); ?>
            </form>
        </div>

        <!-- Right side table -->
        <div style="flex: 2; background: #fff; padding:20px; border:1px solid #ddd; border-radius:8px;">
            <h2>Storage List</h2>
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="40%">Storage Size</th>
                        <th width="40%">Slug</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ( $storages ) {
                        foreach ( $storages as $storage ) {
                            echo '<tr>';
                            echo '<td>' . esc_html( $storage->id ) . '</td>';
                            echo '<td><strong>' . esc_html( $storage->title ) . '</strong></td>';
                            echo '<td>' . esc_html( isset($storage->slug) ? $storage->slug : sanitize_title($storage->title) ) . '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="3">No storage options found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
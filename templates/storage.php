<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$table = $wpdb->prefix . 'pixelcode_phone_storage';

// ==== DELETE ====
if ( isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) ) {

    if ( ! current_user_can('manage_options') ) {
        wp_die(__('You do not have permission to delete storage options.', 'textdomain'));
    }

    if ( check_admin_referer('delete_storage_' . intval($_GET['id'])) ) {
        $wpdb->delete( $table, [ 'id' => intval($_GET['id']) ], [ '%d' ] );
        echo '<div class="updated notice"><p>Storage option deleted successfully!</p></div>';
    }
}

// ==== EDIT (SAVE) ====
if ( isset($_POST['edit_storage_id']) ) {

    if ( ! current_user_can('manage_options') ) {
        wp_die(__('You do not have permission to edit storage options.', 'textdomain'));
    }

    if ( check_admin_referer('edit_storage','edit_storage_nonce') ) {
        $id          = intval($_POST['edit_storage_id']);
        $title       = sanitize_text_field( $_POST['storage_title'] );
        $slug_input  = isset($_POST['storage_slug']) && !empty($_POST['storage_slug']) 
                        ? sanitize_title( $_POST['storage_slug'] ) 
                        : sanitize_title( $title );

        $wpdb->update(
            $table,
            [
                'title'       => $title,
                'slug'        => $slug_input,
            ],
            [ 'id' => $id ],
            [ '%s', '%s' ],
            [ '%d' ]
        );

        echo '<div class="updated notice"><p>Storage option updated successfully!</p></div>';
    }
}

// ==== ADD ====
if ( isset($_POST['storage_title']) && !isset($_POST['edit_storage_id']) ) {

    if ( ! current_user_can('manage_options') ) {
        wp_die(__('You do not have permission to add storage options.', 'textdomain'));
    }

    if ( check_admin_referer('save_storage','storage_nonce') ) {
        $title       = sanitize_text_field( $_POST['storage_title'] );
        $slug_input  = isset($_POST['storage_slug']) && !empty($_POST['storage_slug']) 
                        ? sanitize_title( $_POST['storage_slug'] ) 
                        : sanitize_title( $title );

        $wpdb->insert(
            $table,
            [
                'title'       => $title,
                'slug'        => $slug_input,
            ],
            [ '%s', '%s' ]
        );

        echo '<div class="updated notice"><p>Storage option saved successfully!</p></div>';
    }
}

// ==== AUTO SLUG GENERATE ====
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

// ==== FETCH ====
$storages = $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC" );

// ==== EDIT MODE (form fill) ====
$edit_item = null;
if ( isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']) ) {
    if ( current_user_can('manage_options') ) {
        $edit_item = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table WHERE id = %d", intval($_GET['id'])) );
    }
}
?>

<div class="wrap">
    <h1>Phone Storage Options</h1>
    <div id="col-container">

        <div id="col-left">
            <div class="col-wrap">
                <h2><?php echo $edit_item ? 'Edit Storage Option' : 'Add New Storage Option'; ?></h2>
                <form method="post" action="" class="wp-person-form">
                    <?php 
                        if ( $edit_item ) {
                            wp_nonce_field( 'edit_storage', 'edit_storage_nonce' );
                            echo '<input type="hidden" name="edit_storage_id" value="' . esc_attr($edit_item->id) . '">';
                        } else {
                            wp_nonce_field( 'save_storage', 'storage_nonce' );
                        }
                        ?>

                    <div class="form-field">
                        <label for="storage_title">Storage Size</label>
                        <input type="text" name="storage_title" id="storage_title" class="regular-text"
                            value="<?php echo $edit_item ? esc_attr($edit_item->title) : ''; ?>" required>
                    </div>

                    <div style="margin-top:10px;" class="form-field">
                        <label for="storage_slug">Slug</label>
                        <input type="text" name="storage_slug" id="storage_slug" class="regular-text"
                            value="<?php echo $edit_item ? esc_attr($edit_item->slug) : ''; ?>" readonly>
                    </div>
                    <div style="margin-top:20px;">
                        <?php submit_button( $edit_item ? 'Update Storage' : 'Save Storage' ); ?>
                    </div>
                </form>
            </div>
        </div>

        <div id="col-right">
            <div class="col-wrap">
                <h2>Storage List</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column column-name">Storage Size</th>
                            <th scope="col" class="manage-column column-slug">Slug</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if ( $storages ) {
                                foreach ( $storages as $storage ) {
                                    $delete_url = wp_nonce_url( admin_url( 'admin.php?page=pixelcode-storage&action=delete&id=' . $storage->id ), 'delete_storage_' . $storage->id );
                                    $edit_url   = admin_url( 'admin.php?page=pixelcode-storage&action=edit&id=' . $storage->id );

                                    echo '<tr class="iedit author-self level-0 type-post status-publish hentry">';
                                    echo '<td class="name column-name has-row-actions column-primary"><strong><a href="' . esc_url($edit_url) . '">' . esc_html( $storage->title ) . '</a></strong>
                                    <div class="row-actions"><span class="edit"><a href="' . esc_url($edit_url) . '">Edit</a> | </span><span class="trash"><a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Are you sure?\');">Delete</a></span></div></td>';
                                    echo '<td class="slug column-slug">' . esc_html( $storage->slug ) . '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="2">No storage options found.</td></tr>';
                            }
                            ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
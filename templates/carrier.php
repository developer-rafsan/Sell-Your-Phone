<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$table = $wpdb->prefix . 'pixelcode_phone_carriers';

// ==== DELETE ====
if ( isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) ) {

    if ( ! current_user_can('manage_options') ) {
        wp_die(__('You do not have permission to delete carriers.', 'textdomain'));
    }

    if ( check_admin_referer('delete_carrier_' . intval($_GET['id'])) ) {
        $wpdb->delete( $table, [ 'id' => intval($_GET['id']) ], [ '%d' ] );
        echo '<div class="updated notice"><p>Carrier deleted successfully!</p></div>';
    }
}

// ==== EDIT (SAVE) ====
if ( isset($_POST['edit_carrier_id']) ) {

    if ( ! current_user_can('manage_options') ) {
        wp_die(__('You do not have permission to edit carriers.', 'textdomain'));
    }

    if ( check_admin_referer('edit_carrier','edit_carrier_nonce') ) {
        $id          = intval($_POST['edit_carrier_id']);
        $title       = sanitize_text_field( $_POST['carrier_title'] );
        $slug_input  = isset($_POST['carrier_slug']) && !empty($_POST['carrier_slug']) 
                        ? sanitize_title( $_POST['carrier_slug'] ) 
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

        echo '<div class="updated notice"><p>Carrier updated successfully!</p></div>';
    }
}

// ==== ADD ====
if ( isset($_POST['carrier_title']) && !isset($_POST['edit_carrier_id']) ) {

    if ( ! current_user_can('manage_options') ) {
        wp_die(__('You do not have permission to add carriers.', 'textdomain'));
    }

    if ( check_admin_referer('save_carrier','carrier_nonce') ) {
        $title       = sanitize_text_field( $_POST['carrier_title'] );
        $slug_input  = isset($_POST['carrier_slug']) && !empty($_POST['carrier_slug']) 
                        ? sanitize_title( $_POST['carrier_slug'] ) 
                        : sanitize_title( $title );

        $wpdb->insert(
            $table,
            [
                'title'       => $title,
                'slug'        => $slug_input,
            ],
            [ '%s', '%s' ]
        );

        echo '<div class="updated notice"><p>Carrier saved successfully!</p></div>';
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
$carriers = $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC" );

// ==== EDIT MODE (form fill) ====
$edit_item = null;
if ( isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']) ) {
    if ( current_user_can('manage_options') ) {
        $edit_item = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table WHERE id = %d", intval($_GET['id'])) );
    }
}
?>

<div class="wrap">
    <h1>Phone Carriers</h1>
        <div id="col-container">

            <div id="col-left">
                <div class="col-wrap">
                    <h2><?php echo $edit_item ? 'Edit Carrier' : 'Add New Carrier'; ?></h2>
                    <form method="post" action="" class="wp-person-form">
                        <?php 
                        if ( $edit_item ) {
                            wp_nonce_field( 'edit_carrier', 'edit_carrier_nonce' );
                            echo '<input type="hidden" name="edit_carrier_id" value="' . esc_attr($edit_item->id) . '">';
                        } else {
                            wp_nonce_field( 'save_carrier', 'carrier_nonce' );
                        }
                        ?>

                        <div class="form-field">
                            <label for="carrier_title">Carrier Title</label>
                            <input type="text" name="carrier_title" id="carrier_title" class="regular-text"
                                value="<?php echo $edit_item ? esc_attr($edit_item->title) : ''; ?>" required>
                        </div>

                        <div style="margin-top:10px;" class="form-field">
                            <label for="carrier_slug">Carrier Slug</label>
                            <input type="text" name="carrier_slug" id="carrier_slug" class="regular-text"
                                value="<?php echo $edit_item ? esc_attr($edit_item->slug) : ''; ?>"
                                readonly>
                        </div>

                        <div style="margin-top:20px;">
                            <?php submit_button( $edit_item ? 'Update Carrier' : 'Save Carrier' ); ?>
                        </div>
                    </form>
                </div>
            </div>

            <div id="col-right">
                <div class="col-wrap">
                    <h2>Carrier List</h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th scope="col" class="manage-column column-name">Title</th>
                                <th scope="col" class="manage-column column-slug">Slug</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ( $carriers ) {
                                foreach ( $carriers as $carrier ) {
                                    $delete_url = wp_nonce_url( admin_url( 'admin.php?page=pixelcode-carrier&action=delete&id=' . $carrier->id ), 'delete_carrier_' . $carrier->id );
                                    $edit_url   = admin_url( 'admin.php?page=pixelcode-carrier&action=edit&id=' . $carrier->id );

                                    echo '<tr class="iedit author-self level-0 type-post status-publish hentry">';
                                    echo '<td class="name column-name has-row-actions column-primary"><strong><a href="' . esc_url($edit_url) . '">' . esc_html( $carrier->title ) . '</a></strong>
                                    <div class="row-actions"><span class="edit"><a href="' . esc_url($edit_url) . '">Edit</a> | </span><span class="trash"><a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Are you sure?\');">Delete</a></span></div></td>';
                                    echo '<td class="slug column-slug">' . esc_html( $carrier->slug ) . '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="2">No carriers found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
</div>

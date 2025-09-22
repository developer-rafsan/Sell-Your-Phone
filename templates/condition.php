<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$table = $wpdb->prefix . 'pixelcode_phone_conditions';

// ==== DELETE ====
if ( isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) ) {

    if ( ! current_user_can('manage_options') ) {
        wp_die(__('You do not have permission to delete conditions.', 'textdomain'));
    }

    if ( check_admin_referer('delete_condition_' . intval($_GET['id'])) ) {
        $wpdb->delete( $table, [ 'id' => intval($_GET['id']) ], [ '%d' ] );
        echo '<div class="updated notice"><p>Condition deleted successfully!</p></div>';
    }
}

// ==== EDIT (SAVE) ====
if ( isset($_POST['edit_condition_id']) ) {

    if ( ! current_user_can('manage_options') ) {
        wp_die(__('You do not have permission to edit conditions.', 'textdomain'));
    }

    if ( check_admin_referer('edit_condition','edit_condition_nonce') ) {
        $id          = intval($_POST['edit_condition_id']);
        $title       = sanitize_text_field( $_POST['condition_title'] );
        $slug_input  = isset($_POST['condition_slug']) && !empty($_POST['condition_slug']) 
                        ? sanitize_title( $_POST['condition_slug'] ) 
                        : sanitize_title( $title );
        $description = sanitize_textarea_field( $_POST['condition_description'] );

        $wpdb->update(
            $table,
            [
                'title'       => $title,
                'slug'        => $slug_input,
                'description' => $description,
            ],
            [ 'id' => $id ],
            [ '%s', '%s', '%s' ],
            [ '%d' ]
        );

        echo '<div class="updated notice"><p>Condition updated successfully!</p></div>';
    }
}

// ==== ADD ====
if ( isset($_POST['condition_title']) && !isset($_POST['edit_condition_id']) ) {

    if ( ! current_user_can('manage_options') ) {
        wp_die(__('You do not have permission to add conditions.', 'textdomain'));
    }

    if ( check_admin_referer('save_condition','condition_nonce') ) {
        $title       = sanitize_text_field( $_POST['condition_title'] );
        $slug_input  = isset($_POST['condition_slug']) && !empty($_POST['condition_slug']) 
                        ? sanitize_title( $_POST['condition_slug'] ) 
                        : sanitize_title( $title );
        $description = sanitize_textarea_field( $_POST['condition_description'] );

        $wpdb->insert(
            $table,
            [
                'title'       => $title,
                'slug'        => $slug_input,
                'description' => $description,
            ],
            [ '%s', '%s', '%s' ]
        );

        echo '<div class="updated notice"><p>Condition saved successfully!</p></div>';
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
$conditions = $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC" );

// ==== EDIT MODE (form fill) ====
$edit_item = null;
if ( isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']) ) {
    if ( current_user_can('manage_options') ) {
        $edit_item = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table WHERE id = %d", intval($_GET['id'])) );
    }
}
?>

<div class="wrap">
    <h1>Phone Conditions</h1>
    <div id="col-container">

        <div id="col-left">
            <div class="col-wrap">
                <h2><?php echo $edit_item ? 'Edit Condition' : 'Add New Condition'; ?></h2>
                <form method="post" action="" class="wp-person-form">
                    <?php 
                        if ( $edit_item ) {
                            wp_nonce_field( 'edit_condition', 'edit_condition_nonce' );
                            echo '<input type="hidden" name="edit_condition_id" value="' . esc_attr($edit_item->id) . '">';
                        } else {
                            wp_nonce_field( 'save_condition', 'condition_nonce' );
                        }
                        ?>

                    <div class="form-field">
                        <label for="condition_title">Condition Title</label>
                        <input type="text" name="condition_title" id="condition_title" class="regular-text"
                            value="<?php echo $edit_item ? esc_attr($edit_item->title) : ''; ?>" required>
                    </div>

                    <div style="margin-top:10px;" class="form-field">
                        <label for="condition_slug">Condition Slug</label>
                        <input type="text" name="condition_slug" id="condition_slug" class="regular-text"
                            value="<?php echo $edit_item ? esc_attr($edit_item->slug) : ''; ?>" readonly>
                    </div>

                    <div style="margin-top:10px;" class="form-field">
                        <label for="condition_description">Condition Description</label>
                        <textarea name="condition_description" id="condition_description" rows="5"
                            class="large-text"><?php echo $edit_item ? esc_textarea($edit_item->description) : ''; ?></textarea>
                    </div>
                    <div style="margin-top:20px;">
                        <?php submit_button( $edit_item ? 'Update Condition' : 'Save Condition' ); ?>
                    </div>
                </form>
            </div>
        </div>

        <div id="col-right">
            <div class="col-wrap">
                <h2>Condition List</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column column-name">Title</th>
                            <th scope="col" class="manage-column column-slug">Slug</th>
                            <th scope="col" class="manage-column column-description">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if ( $conditions ) {
                                foreach ( $conditions as $condition ) {
                                    $delete_url = wp_nonce_url( admin_url( 'admin.php?page=pixelcode-condition&action=delete&id=' . $condition->id ), 'delete_condition_' . $condition->id );
                                    $edit_url   = admin_url( 'admin.php?page=pixelcode-condition&action=edit&id=' . $condition->id );

                                    echo '<tr class="iedit author-self level-0 type-post status-publish hentry">';
                                    echo '<td class="name column-name has-row-actions column-primary"><strong><a href="' . esc_url($edit_url) . '">' . esc_html( $condition->title ) . '</a></strong>
                                    <div class="row-actions"><span class="edit"><a href="' . esc_url($edit_url) . '">Edit</a> | </span><span class="trash"><a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Are you sure?\');">Delete</a></span></div></td>';
                                    echo '<td class="slug column-slug">' . esc_html( $condition->slug ) . '</td>';
                                    echo '<td class="description column-description">' . esc_html( $condition->description ) . '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="3">No conditions found.</td></tr>';
                            }
                            ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
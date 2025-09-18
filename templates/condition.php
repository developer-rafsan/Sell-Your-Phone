<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$table = $wpdb->prefix . 'pixelcode_phone_conditions';

// Save new condition
if ( isset($_POST['condition_title']) && check_admin_referer('save_condition','condition_nonce') ) {

    $title = sanitize_text_field( $_POST['condition_title'] );
    $description = sanitize_textarea_field( $_POST['condition_description'] );

    // Slug: user input or auto-generate
    $slug_input = isset($_POST['condition_slug']) && !empty($_POST['condition_slug']) 
                  ? sanitize_title( $_POST['condition_slug'] ) 
                  : sanitize_title( $title );

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

// Fetch all conditions
$conditions = $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC" );
?>

<div class="wrap">
    <h1>Phone Conditions</h1>
    <div style="display: flex; gap: 30px; align-items: flex-start;">

        <!-- Left side form -->
        <div style="flex: 1; background: #fff; padding:20px; border:1px solid #ddd; border-radius:8px;">
            <h2>Add New Condition</h2>
            <form method="post" action="">
                <?php wp_nonce_field( 'save_condition', 'condition_nonce' ); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="condition_title">Condition Title</label></th>
                        <td><input type="text" name="condition_title" id="condition_title" class="regular-text"
                                required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="condition_slug">Condition Slug</label></th>
                        <td><input type="text" name="condition_slug" id="condition_slug" class="regular-text"
                                placeholder="Optional, leave blank to auto-generate"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="condition_description">Condition Description</label></th>
                        <td><textarea name="condition_description" id="condition_description" rows="5"
                                class="large-text"></textarea></td>
                    </tr>
                </table>

                <?php submit_button( 'Save Condition' ); ?>
            </form>
        </div>

        <!-- Right side table -->
        <div style="flex: 2; background: #fff; padding:20px; border:1px solid #ddd; border-radius:8px;">
            <h2>Condition List</h2>
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="20%">Title</th>
                        <th width="20%">Slug</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ( $conditions ) {
                        foreach ( $conditions as $condition ) {
                            echo '<tr>';
                            echo '<td>' . esc_html( $condition->id ) . '</td>';
                            echo '<td><strong>' . esc_html( $condition->title ) . '</strong></td>';
                            echo '<td>' . esc_html( isset($condition->slug) ? $condition->slug : sanitize_title($condition->title) ) . '</td>';
                            echo '<td>' . esc_html( $condition->description ) . '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="4">No conditions found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
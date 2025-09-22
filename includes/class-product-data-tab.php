<?php
// 1. Add three custom product data tabs
add_filter( 'woocommerce_product_data_tabs', 'pixelcode_add_three_custom_tabs' );
function pixelcode_add_three_custom_tabs( $tabs ) {
    $tabs['pixelcode_storage_tab'] = array(
        'label'    => __( 'Storage', 'pixelcode' ),
        'target'   => 'pixelcode_storage_panel',
        'class'    => array( 'show_if_simple', 'show_if_variable' ),
        'priority' => 21,
    );
    $tabs['pixelcode_carrier_tab'] = array(
        'label'    => __( 'Carrier', 'pixelcode' ),
        'target'   => 'pixelcode_carrier_panel',
        'class'    => array( 'show_if_simple', 'show_if_variable' ),
        'priority' => 22,
    );
    $tabs['pixelcode_condition_tab'] = array(
        'label'    => __( 'Condition', 'pixelcode' ),
        'target'   => 'pixelcode_condition_panel',
        'class'    => array( 'show_if_simple', 'show_if_variable' ),
        'priority' => 23,
    );
    return $tabs;
}

// 2. Add panels for Storage, Carrier, Condition
add_action( 'woocommerce_product_data_panels', 'pixelcode_add_tabs_content' );
function pixelcode_add_tabs_content() {
    global $wpdb, $post;

    $types = [
        'storage'   => $wpdb->prefix . 'pixelcode_phone_storage',
        'carrier'   => $wpdb->prefix . 'pixelcode_phone_carriers',
        'condition' => $wpdb->prefix . 'pixelcode_phone_conditions',
    ];

    foreach ( $types as $type => $table_name ) {

        // Fetch options from DB
        $results = $wpdb->get_results( "SELECT id, title FROM $table_name ORDER BY title ASC" );
        $options = [];
        if ( ! empty($results) ) {
            foreach ( $results as $r ) {
                $options[ $r->id ] = $r->title;
            }
        }

        // Get saved rows
        $meta_key    = "_pixelcode_{$type}_rows";
        $stored_data = get_post_meta( $post->ID, $meta_key, true );
        if ( ! is_array($stored_data) ) $stored_data = [];

        // Start panel
        echo "<div id='pixelcode_{$type}_panel' class='panel woocommerce_options_panel'>";

        // Toolbar
        echo "<div style='margin:20px 10px' class='toolbar toolbar-top'>";
        echo "<button type='button' class='button add_{$type}_row'>Add " . ucfirst($type) . " Option</button>";
        echo "</div>";

        // Table Header
        if ( $type === 'condition' ) {
            echo "<table class='wp-list-table widefat striped pixelcode_{$type}_table'>";
            echo "<thead><tr><th>Condition</th><th>Price</th><th>Action</th></tr></thead><tbody>";
        } else {
            echo "<table class='wp-list-table widefat striped pixelcode_{$type}_table'>";
            echo "<thead><tr><th>" . ucfirst($type) . "</th><th>Price</th><th>Action</th></tr></thead><tbody>";
        }

        // Table Rows
        if ( ! empty($stored_data) ) {
            foreach ( $stored_data as $index => $row ) {
                echo "<tr>";
                echo "<td><select name='_pixelcode_{$type}_rows[{$index}][{$type}]'>";
                foreach ( $options as $id => $title ) {
                    $selected = selected( $row[$type], $id, false );
                    echo "<option value='{$id}' {$selected}>{$title}</option>";
                }
                echo "</select></td>";

                echo "<td><input type='text' name='_pixelcode_{$type}_rows[{$index}][price]' value='" . esc_attr($row['price'] ?? '') . "' /></td>";

                echo "<td><button type='button' class='button remove_{$type}_row'>Remove</button></td>";
                echo "</tr>";
            }
        }

        echo "</tbody></table>";
        echo "</div>";

        // JS for dynamic rows
        ?>
        <script type="text/javascript">
        jQuery(function($) {
            var rowIndex<?php echo $type; ?> = <?php echo count($stored_data); ?>;

            $('.add_<?php echo $type; ?>_row').on('click', function() {
                var row = '<tr>';
                row += '<td><select name="_pixelcode_<?php echo $type; ?>_rows[' +
                    rowIndex<?php echo $type; ?> +
                    '][<?php echo $type; ?>]"><?php foreach ($options as $id => $title){ echo "<option value=\'$id\'>$title</option>"; } ?></select></td>';

                row += '<td><input type="text" name="_pixelcode_<?php echo $type; ?>_rows[' +
                    rowIndex<?php echo $type; ?> + '][price]" value="" /></td>';

                row += '<td><button type="button" class="button remove_<?php echo $type; ?>_row">Remove</button></td>';
                row += '</tr>';
                $('.pixelcode_<?php echo $type; ?>_table tbody').append(row);
                rowIndex<?php echo $type; ?>++;
            });

            $(document).on('click', '.remove_<?php echo $type; ?>_row', function() {
                $(this).closest('tr').remove();
            });
        });
        </script>
        <?php
    }
}


// 3. Save all tabs with title & description from DB
add_action( 'woocommerce_process_product_meta', 'pixelcode_save_all_rows' );
function pixelcode_save_all_rows( $post_id ) {
    global $wpdb;

    $tables = [
        'storage'   => $wpdb->prefix . 'pixelcode_phone_storage',
        'carrier'   => $wpdb->prefix . 'pixelcode_phone_carriers',
        'condition' => $wpdb->prefix . 'pixelcode_phone_conditions',
    ];

    foreach ( $tables as $type => $table_name ) {
        $meta_key = "_pixelcode_{$type}_rows";

        if ( isset($_POST[$meta_key]) && is_array($_POST[$meta_key]) ) {
            $rows = [];
            foreach ( $_POST[$meta_key] as $row ) {
                if ( ! empty($row[$type]) ) {
                    $item_id = absint($row[$type]);

                    // get title (+ description only for condition) from DB
                    if ( $type === 'condition' ) {
                        $data_db = $wpdb->get_row( $wpdb->prepare(
                            "SELECT title, description FROM $table_name WHERE id = %d",
                            $item_id
                        ));
                        $title       = $data_db->title ?? '';
                        $description = $data_db->description ?? '';
                    } else {
                        $title = $wpdb->get_var( $wpdb->prepare(
                            "SELECT title FROM $table_name WHERE id = %d",
                            $item_id
                        ));
                        $description = '';
                    }

                    $data = [
                        $type        => $item_id,
                        'title'      => sanitize_text_field($title),
                        'price'      => sanitize_text_field($row['price'] ?? ''),
                    ];

                    if ( $type === 'condition' ) {
                        $data['description'] = sanitize_text_field($description);
                    }

                    $rows[] = $data;
                }
            }
            update_post_meta( $post_id, $meta_key, $rows );
        } else {
            delete_post_meta( $post_id, $meta_key );
        }
    }
}
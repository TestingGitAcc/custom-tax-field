<?php
/**
 * Plugin Name: Custom tax field
 * Description: Simple custom tax field
 * Version: 1.0
 * Author: Sania
 */

/**
 * Add wp media
 */
function load_scripts_admin() {
    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'load_scripts_admin' );

/**
 * Image Uploader
 */
function custom_image_uploader( $name, $width, $height , $tag ) {

    $t_id = $tag->term_id;
    $options = get_option( "taxonomy_$t_id", '' );


    $default_image = plugins_url('img/default.png', __FILE__);

    if ( !empty( $options[$name] ) ) {
        $image_attributes = wp_get_attachment_image_src( $options[$name], array( $width, $height ) );
        $src = $image_attributes[0];
        $value = $options[$name];
    } else {
        $src = $default_image;
        $value = '';
    }

    $text = __( 'Upload');

    echo '
        <div class="upload">
            <img data-src="' . $default_image . '" src="' . $src . '" width="' . $width . 'px" height="' . $height . 'px" />
            <div>
                <input type="hidden"  name="term_meta[custom_image]" id="term_meta[custom_image]" value="' . $value . '" />
                <button type="submit" class="upload_image_button button">' . $text . '</button>
                <button type="submit" class="remove_image_button button">&times;</button>
            </div>
        </div>
    ';
}

add_action('admin_print_footer_scripts', 'add_custom_fields_javascript', 99);
function add_custom_fields_javascript() {
    ?>
    <script>
        // The "Upload" button
        $('.upload_image_button').click(function() {
            var send_attachment_bkp = wp.media.editor.send.attachment;
            var button = $(this);
            wp.media.editor.send.attachment = function(props, attachment) {
                $(button).parent().prev().attr('src', attachment.url);
                $(button).prev().val(attachment.id);
                wp.media.editor.send.attachment = send_attachment_bkp;
            };
            wp.media.editor.open(button);
            return false;
        });

        // The "Remove" button (remove the value from input type='hidden')
        $('.remove_image_button').click(function() {
            var answer = confirm('Are you sure?');
            if (answer == true) {
                var src = $(this).parent().prev().attr('data-src');
                $(this).parent().prev().attr('src', src);
                $(this).prev().prev().val('');
            }
            return false;
        });
    </script>
    <?php
}

/**
 * Add extra fields to custom taxonomy edit and add form callback functions
 */
function extra_edit_tax_fields($tag) {

    $t_id = $tag->term_id;
    $term_meta = get_option( "taxonomy_$t_id", '' );
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="cat_Image_url"><?php _e( 'Category Image' ); ?></label></th>
        <td>
            <?php custom_image_uploader( 'custom_image', $width = 115, $height = 115, $tag ); ?>
            <p class="description"><?php _e( 'Add img.' ); ?></p>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="cat_Image_url"><?php _e( 'Text' ); ?></label></th>
        <td>
            <?php
            $text = '';
            if( !isset($term_meta['text']) || $term_meta['text'] == false){
                $text = $term_meta['text'];
            } else {
                $text = 'default text';
            }
            ?>
            <input type="text" name="term_meta[text]" id="term_meta[text]" value="<?php echo $text; ?>">
            <p class="description"><?php _e( 'Enter text.' ); ?></p>
        </td>
    </tr>
    <?php
}
add_action( 'category_edit_form_fields', 'extra_edit_tax_fields', 10, 2 );

function save_extra_taxonomy_fields( $term_id ) {
    $t_id = $term_id;
    if ( isset( $_POST['term_meta'] ) ) {
        $term_meta = get_option( "taxonomy_$t_id" );
        $cat_keys = array_keys( $_POST['term_meta'] );
        foreach ( $cat_keys as $key ) {
            if ( isset ( $_POST['term_meta'][$key] ) ) {
                $term_meta[$key] = $_POST['term_meta'][$key];
                update_option( "taxonomy_$t_id", $term_meta );
            }
        }
    }
}
add_action( 'edited_category', 'save_extra_taxonomy_fields', 10, 2 );
add_action( 'create_category', 'save_extra_taxonomy_fields', 10, 2 );

function display_custom_tax_field(){
    ob_start();

    $queried_object = get_queried_object();
    $t_id = $queried_object->term_id;

    $term_meta = get_option( "taxonomy_$t_id", '' );

    if($term_meta){
        $text = '';
        if( !isset($term_meta['text']) || $term_meta['text'] == false){
            $text = $term_meta['text'];
        } else {
            $text = 'default text';
        }
        ?>
        <video src="<?php echo $text; ?>"></video>
        <?php
        if ( !empty( $term_meta['custom_image'] || $term_meta['text'] == false ) ) {
            $image_attributes = wp_get_attachment_image_src( $term_meta['custom_image'], array( 115, 115 ) );
            $src = $image_attributes[0];
            echo '<img src="' . $src . '" width="115px" height="115px" />';
        }
    }

    return ob_get_clean();
}
add_shortcode('display-custom-tax-field','display_custom_tax_field');

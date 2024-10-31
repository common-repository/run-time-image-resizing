<?php
    /**
    * Plugin Name: Run time Image resizing
    * Plugin URI: http://example.com
    * Description: This plugin resize the image after upload.
    * Version: 1.1
    * Author: Commercepundit
    * Author URI: http://www.commercepundit.com/
    */
?>

<?php
// Create a helper function for easy SDK access.
function rtir_fs() {
    global $rtir_fs;

    if ( ! isset( $rtir_fs ) ) {
        // Include Freemius SDK.
        require_once dirname(__FILE__) . '/freemius/start.php';

        $rtir_fs = fs_dynamic_init( array(
            'id'                => '483',
            'slug'              => 'run-time-image-resizing',
            'type'              => 'plugin',
            'public_key'        => 'pk_99b75a3dc0aa72f6f8c1d8aff8a75',
            'is_premium'        => false,
            'has_addons'        => false,
            'has_paid_plans'    => false,
            'menu'              => array(
                'slug'       => 'run-time-image-resizing',
                'support'    => false,
                'parent'     => array(
                    'slug' => 'options-general.php',
                ),
            ),
        ) );
    }

    return $rtir_fs;
}

// Init Freemius.
rtir_fs();

    add_action('admin_menu', 'rtir_imgresizer_create_menu');
    add_action('init','imageshortcode');  
    add_action('admin_enqueue_scripts','rtir_wp_uploader');
    function rtir_imgresizer_create_menu() {
        add_menu_page('Image Resizer Settings', 'Image Resizer Settings', 'manage_options', 'imgresizer-after-upload', 'imgresizer_plugin_settings_page');
    }

    function rtir_wp_uploader(){
        if (is_admin()){
            wp_enqueue_media();
            wp_register_script('imgresizer-custom-js', plugins_url('/assets/js/admin-script.js', __FILE__ ), '', '', true);
            wp_enqueue_script('imgresizer-custom-js');
        } 
    }

    function imageshortcode(){   
        if(!is_admin()){
            add_shortcode( 'resize_img', 'rtir_updateimage' ); 
        } 
    }

    function imgresizer_plugin_settings_page() {
        // General check for user permissions.
        if (!current_user_can('manage_options'))  {
            wp_die( __('You do not have sufficient permission to access this page.')    );
        }
        if (isset($_POST['cache_button']) && check_admin_referer('cache_button_clicked')) {
            // the button has been pressed AND we've passed the security check
            rtir_cache_button_action();
        }
        $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'developer_options';
        if( isset( $_GET[ 'tab' ] ) ) {
            $active_tab = $_GET[ 'tab' ];
        }

    ?>
    <h2 class="nav-tab-wrapper">
        <a href="?page=imgresizer-after-upload&tab=developer_options" class="nav-tab <?php echo $active_tab == 'developer_options' ? 'nav-tab-active' : ''; ?>">Developer Options</a>
        <a href="?page=imgresizer-after-upload&tab=user_options" class="nav-tab <?php echo $active_tab == 'user_options' ? 'nav-tab-active' : ''; ?>">User Options</a>
    </h2>
    <?php
        if($active_tab == 'developer_options'){
            rtir_developer_form();   
        }else{
            rtir_users_form();
        }  
    }

    function rtir_developer_form(){
    ?>
    <div class="wrap">
        <p>Place below Shortcode. By Default it will return resized image URL. If you want image then pass argument return in shortcode with "Image" value.<br><br>
            <strong>[resize_img width="new-width" height="new-height" imgurl="Url of Attachment" attach_id="Id of Attachment" picquality="jpeg pic quality" return="Image"]</strong></br>
            <p>When you use this shortcode in wordpress editor then you should use "return" attribute with "Image" value.</p>
        </p>
        <h2>Clear Cache from here</h2>
        <form action="options-general.php?page=imgresizer-after-upload" method="POST">
            <?php
                wp_nonce_field('cache_button_clicked');   
            ?>
            <input type="hidden" value="true" name="cache_button" />
            <?php
                submit_button('Clear Cache');
            ?>
        </form>
    </div>
    <?php  
    }
    function rtirplugin_activate() {
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir = $upload_dir . '/rtir-cache';
        if (! is_dir($upload_dir)) {
            mkdir( $upload_dir, 0700 );
            if ((!is_dir($upload_dir)) || (!is_writable($upload_dir))) {
                echo '<div class="updated notice is-dismissible" id="message">
                <p>Pls Check your Upload directory File Permissions.</p>
                <button class="notice-dismiss" type="button">
                <span class="screen-reader-text">Dismiss this notice.</span>
                </button></div>';
                return;
            }
        }
    }

    register_activation_hook( __FILE__, 'rtirplugin_activate' );

    function rtir_users_form(){
        if($_POST){

            $expr = '/^[0-9]*$/';
            $img_url = esc_url($_POST['image_url']);

            if(!empty($_POST['image_url']) && !empty($_POST['wt']) && !empty($_POST['ht'])){        
                $headers = get_headers($img_url, 1); 
                $type = $headers["Content-Type"];
                $pos = strpos($type, "image");
                if (((int)($_POST['wt'])) && ((int)($_POST['ht'])) && $pos !== false){
                    $resizedImg = rtir_updateimage($_POST);
                    echo '<img src="'.esc_url($resizedImg).'">
                    <p><strong>Right Click on the image with your mouse and select Save Picture As.. and save. then use it.</strong></p>';

                }else{
                    echo "<em style='color:red;font-weight:bold'>Validation errors occurred. Please confirm the fields and submit it again.</em>";
                } 
            }else{
                echo "<em style='color:red;font-weight:bold'>Validation errors occurred. Please fill all the fields and submit it again.</em>"; 
            }
        }   
    ?>
    <div class="wrap"> 
        <form class="form-horizontal" method="POST" action="options-general.php?page=imgresizer-after-upload&tab=user_options" enctype="multipart/form-data">
            <?php
                wp_nonce_field('imgresizer_user_options');   
            ?>
            <input type="hidden" value="imgresizer_for_user" id="imgresizer_for_user" name="imgresizer_for_user">
            <p>
                <input type="text" name="image_url" id="image_url" class="regular-text" value="<?php if(!empty($img_url)){ echo $img_url; } ?>">
                <input type="button" name="upload-btn" id="upload-btn" class="button-secondary" value="Choose File">
            </p>
            <p>
                <label for="width"><?php _e( 'Width : ', '' ); ?></label>
                <input type="number" id="inputWidth" class="required" name="wt" placeholder="width" value=<?php if(!empty($nwidth)) echo $nwidth; else echo "60"; ?> />
                <span class="add-on">PX</span>
            </p> 
            <p>
                <label for="height"><?php _e( 'Height : ', '' ); ?></label>
                <input type="number" id="inputHeight" class="required" name="ht" value=<?php if(!empty($nheight)) echo $nheight; else echo "60"; ?> placeholder="Height">
                <span class="add-on">PX</span>
            </p>
            <?php
                submit_button('Get Resized Image');
            ?> 
        </form>
    </div>
    <?php  
    }

    function rtir_cache_button_action(){
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $plugin_dir_path = $upload_dir . '/rtir-cache';
        $files = glob($plugin_dir_path.'/*');
        if(!empty($files)){ 
            foreach($files as $file){ // iterate files
                if(is_file($file))
                    unlink($file); // delete file
            }
            echo '<div class="updated notice is-dismissible" id="message">
            <p>All Files are Deleted Successfully From Cache.</p>
            <button class="notice-dismiss" type="button">
            <span class="screen-reader-text">Dismiss this notice.</span>
            </button></div>';
        }
    }    

    function rtir_updateimage($atts){
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_url = $upload['baseurl'];
        if($atts['imgresizer_for_user'] == "imgresizer_for_user"){
            $attach_id = "";
            $width =  $atts['wt'];
            $height =  $atts['ht'];
            $img_url = $atts['image_url'];
            $picQuality = 90;   
        }else{
            $attach_id = $atts['attach_id'];
            $width = $atts['width'];  
            $height = $atts['height'];
            $img_url = $atts['imgurl'];
            $picQuality = $atts['picquality'];   
        }   

        $crop = true;
        $plugin_dir_path = $upload_dir . '/rtir-cache';
        $plugins_url = $upload_url . '/rtir-cache/';
        if ((!is_dir($plugin_dir_path)) || (!is_writable($plugin_dir_path))) {
            echo "<h3>Pls Check your Upload directory File Permissions.</h3>"; 
            return;
        }
        // this is an attachment, so we have the ID
        if ( $attach_id ) {
            $image_src = wp_get_attachment_image_src( $attach_id, 'full' );
            $file_path = get_attached_file( $attach_id );
            // this is not an attachment, let's use the image url
        } elseif ( $img_url ) {  
            $img_url_arr = explode("/uploads",$img_url);    
            $file_path = parse_url( $img_url );
            $file_path = $upload['basedir'].end($img_url_arr);
            $file_path = str_replace("\\",'/',$file_path);            
            $orig_size = getimagesize( $file_path );
            $image_src[0] = $img_url;
            $image_src[1] = $orig_size[0];
            $image_src[2] = $orig_size[1];
        }

        $file_info = pathinfo( $file_path );
        // check if file exists
        if ( !file_exists($file_path) ) 
            return;
        $extension = '.'. $file_info['extension'];
        // the image path without the extension
        $no_ext_path = $plugin_dir_path.'/'.$file_info['filename'];
        $cropped_img_path = $no_ext_path.'-'.$width.'x'.$height.$extension;
        // checking if the file size is larger than the target size
        // if it is smaller or the same size, stop right here and return
        if ( $image_src[1] > $width ) {            
            // the file is larger, check if the resized version already exists (for $crop = true but will also work for $crop = false if the sizes match)
            if ( file_exists( $cropped_img_path ) ){ 
                $cropped_img_url =  $plugins_url.basename( $cropped_img_path );
                $resized_image = array (
                'url' => $cropped_img_url,
                'width' => $width,
                'height' => $height
                ); 
                if(($atts['return'] == "Image") || ($atts['return'] == "image")){
                    return '<img src="'.$resized_image['url'].'" width="'.$resized_image['width'].'" height="'.$resized_image['height'].'">';
                }else{
                    return $resized_image['url'];  
                }
            }
            // $crop = false or no height set
            if ( $crop == false OR !$height ) {
                // calculate the size proportionaly
                $proportional_size = wp_constrain_dimensions( $image_src[1], $image_src[2], $width, $height );
                $resized_img_path = $no_ext_path.'-'.$proportional_size[0].'x'.$proportional_size[1].$extension;                
                // checking if the file already exists
                if ( file_exists( $resized_img_path ) ) {
                    $resized_img_url = $plugins_url.basename( $resized_img_path );   
                    $resized_image = array (
                    'url' => $resized_img_url,
                    'width' => $proportional_size[0],
                    'height' => $proportional_size[1]
                    );
                    if(($atts['return'] == "Image") || ($atts['return'] == "image")){
                        return '<img src="'.$resized_image['url'].'" width="'.$resized_image['width'].'" height="'.$resized_image['height'].'">';
                    }else{
                        return $resized_image['url'];  
                    }
                }
            }
            // check if image width is smaller than set width
            $img_size = getimagesize( $file_path );        
            if ( $img_size[0] <= $width ) $width = $img_size[0];
            // Check if GD Library installed

            if (!function_exists ('imagecreatetruecolor')) {
                echo 'GD Library Error: imagecreatetruecolor does not exist - please contact your webhost and ask them to install the GD library';
                return;
            }           
            // no cache files - let's finally resize it 

            $new_img_path = wp_get_image_editor( $file_path );

            if ( ! is_wp_error( $new_img_path ) ) {
                $resize = $new_img_path->resize( $width, $height, $crop );
                if(!empty($picQuality)){
                    $new_img_path->set_quality((int)$picQuality);
                }
                if ($resize !== FALSE) {

                    $new_size = $new_img_path->get_size();
                    $new_img_width = $new_size['width'];
                    $new_img_height = $new_size['height'];
                }

                $filename = $new_img_path->generate_filename( $new_img_width.'x'.$new_img_height,$plugin_dir_path, NULL  );
                $saved = $new_img_path->save( $filename );

            }

            $new_image_path = $saved['path'];
            $new_image_name = $saved['file'];
            $new_img =  $plugins_url.$new_image_name;   
            // resized output
            $resized_image = array (
            'url' => $new_img,
            'width' => $new_img_width,
            'height' => $new_img_height
            );
            if(($atts['return'] == "Image") || ($atts['return'] == "image")){
                return '<img src="'.$resized_image['url'].'" width="'.$resized_image['width'].'" height="'.$resized_image['height'].'">';
            }else{
                return $resized_image['url'];  
            }

        }
        // default output - without resizing
        $resized_image = array (
        'url' => $image_src[0],
        'width' => $width,
        'height' => $height
        );
        if(($atts['return'] == "Image") || ($atts['return'] == "image")){
            return '<img src="'.$resized_image['url'].'" width="'.$resized_image['width'].'" height="'.$resized_image['height'].'">';
        }else{
            return $resized_image['url'];  
        }
}?>

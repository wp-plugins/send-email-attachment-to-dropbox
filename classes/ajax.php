<?php
class MDROP_Ajax {

    /**
     * @var The single instance of the class
     * @since 0.1
     */
    protected static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function __construct() {
    	add_action( 'wp_ajax_check_mail', array( $this, 'check_mail' ) );
        add_action( 'wp_ajax_send_drop', array( $this, 'send_drop' ) );
        add_action( 'wp_ajax_message_delete', array( $this, 'message_delete' ) );
        add_action( 'wp_ajax_message_post', array( $this, 'message_post' ) );
    }

    function message_post() {
        check_ajax_referer('mdrop_nonce');
        if ( ! isset( $_POST['message_id'] ) ) {
            wp_send_json_error();
        }
        if ( isset( $_POST['message_id'] ) && ! count( $_POST['message_id'] ) ) {
            wp_send_json_error();
        }

        $mail_ids = $_POST['message_id'];

        foreach ( $mail_ids as $key => $post_id ) {
            $mail_post = get_post( $post_id );
            $attach_content = '';
            $get_attachemt_ids = get_post_meta( $post_id, '_wp_attachment_id' );
            foreach ( $get_attachemt_ids as $attach_key => $get_attachemt_id ) {
                $thumbs =  mdrop_get_file( $get_attachemt_id );
                $url = $thumbs['url'];
                $name = $thumbs['name'];
                $thumb = $thumbs['thumb'];
                
                $attach_content =    '<a href="'.$url.'" title="'.$name.'">
                        <img src="'.$thumb.'" class="mdrop-file">
                    </a>';
            }

            wp_insert_post(array(
                'post_type'    => 'post',
                'post_title'   => $mail_post->post_title,
                'post_content' => $mail_post->post_content . $attach_content,
                'post_status'  => 'publish'
            ));

        }

        wp_send_json_success();
    }

    function message_delete() {
        check_ajax_referer('mdrop_nonce');
        if ( ! isset( $_POST['message_id'] ) ) {
            wp_send_json_error();
        }
        if ( isset( $_POST['message_id'] ) && ! count( $_POST['message_id'] ) ) {
            wp_send_json_error();
        }

        $mail_ids = $_POST['message_id'];

        foreach ( $mail_ids as $key => $post_id ) {
            $get_attachemt_ids = get_post_meta( $post_id, '_wp_attachment_id' );
            foreach ( $get_attachemt_ids as $attach_key => $get_attachemt_id ) {
                 wp_delete_attachment( $get_attachemt_id, true );
            }

            wp_delete_post( $post_id, true );
        }

        wp_send_json_success();
    }

    function send_drop() {
        check_ajax_referer('mdrop_nonce');
        if ( ! isset( $_POST['message_id'] ) ) {
            wp_send_json_error();
        }
        if ( isset( $_POST['message_id'] ) && ! count( $_POST['message_id'] ) ) {
            wp_send_json_error();
        }

        $mail_ids = $_POST['message_id'];
        $mail_id = reset( $_POST['message_id'] );
        array_shift( $mail_ids );

        $get_attachemt_ids = get_post_meta( $mail_id, '_wp_attachment_id' );
        $drop = MDROP_Dropbox::instance();
        
        foreach ( $get_attachemt_ids as $key => $get_attachemt_id ) {
            $drop->upload_file_dropbox( $mail_id, $get_attachemt_id );
        }

        if ( $mail_ids ) {
            $runing_status = 1;
        } else {
            $runing_status = 0;
        }
        wp_send_json_success( array( 'runing_status' => $runing_status, 'message_id' => $mail_ids ) );
    }

    function check_mail() {
        check_ajax_referer('mdrop_nonce');
        
        $server = mdrop_connect_server();
        $mail_count = $server->check_num_msg();
        $message_number = $mail_count - ( $_POST['start'] - 1 );
        $end = $_POST['end'];

        if ( ! $mail_count ) {
          wp_send_json_success( array( 'request_status' => 0 ) );  
        }


            $server->new_attachment( $message_number ); 

        $new_message_number = $_POST['start'] + 1;   

        if ( $new_message_number > $end ) {
            wp_send_json_success( array( 'request_status' => 0 ) );
        }

        wp_send_json_success( array( 'request_status' => 1, 'new_message_number' => $new_message_number ) );
    }
}
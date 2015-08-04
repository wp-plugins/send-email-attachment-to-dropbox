<?php
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
ini_set('max_input_time', 300);
ini_set('max_execution_time', 300);
error_reporting(0);

class MDROP_Attachment_List {
    var $imap;
    var $header = array();
    var $fetchstructure = array();
    var $overviews = array();

    protected static $_instance = null;

    public static function instance( $server= null, $email = null, $password = null ) {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self( $server, $email, $password );
        }
        return self::$_instance;
    }

    function __construct( $server, $email, $password ) {

        $this->imap = imap_open ( $server, $email, $password ) or die('Cannot connect: ' . imap_last_error() );
    }


    function get_overviews( $msg_number ) {
        if ( isset( $this->overviews[$msg_number] ) ) {
            return $this->overviews[$msg_number];
        }
        $this->overviews[$msg_number] = imap_fetch_overview( $this->imap, $msg_number );
        return $this->overviews[$msg_number];
    }

    function urlsafe_b64decode($string) {
        $data = str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    //Check no.of.msgs
    function check_num_msg() {
        return imap_num_msg( $this->imap );
    }

    function get_header( $msg_number ) {
        if ( isset( $this->header[$msg_number] ) ) {
            return $this->header[$msg_number];
        }
        $this->header[$msg_number] = imap_headerinfo( $this->imap, $msg_number );
        return $this->header[$msg_number];
    }

    function get_subject( $msg_number ) {
        $this->get_header( $msg_number );
        return $this->header[$msg_number]->subject;
    }

    function get_senderaddress( $msg_number ) {
        $this->get_header( $msg_number );
        return $this->header[$msg_number]->senderaddress;
    }

    function get_fetchstructure( $msg_number ) {

        if ( isset( $this->fetchstructure[$msg_number] ) ) {
            return $this->fetchstructure[$msg_number];
        }

        $this->fetchstructure[$msg_number] = imap_fetchstructure( $this->imap, $msg_number );
        return $this->fetchstructure[$msg_number];
    }

    function get_body_text( $msg_number ) {

        $structures = $this->get_fetchstructure( $msg_number );    

        $section = false;

        if ( isset( $structures->parts ) && is_array( $structures->parts ) ) {
            $section = '1';
            $section = $this->get_section( $section, $structures->parts );
        }

        if ( $section ) {
            $fetchbody = imap_fetchbody( $this->imap, $msg_number, $section );
            return quoted_printable_decode( $fetchbody );
        }
        
        return false;
    }

    //dependency on get_body_text()
    function get_section( $section, $parts ) {
        $parts = reset( $parts );
        
        if ( isset( $parts->parts ) && is_array( $parts->parts ) ) {   
            $section = $section . '.1';
            $section = $this->get_section( $section, $parts->parts );
        }

        return $section;
    }

    function get_attachment_array( $msg_number ) {
        return $this->mail_mime_to_array( $msg_number );
    }

    function mail_mime_to_array( $mid, $parse_headers = false ) {
        $mail = $this->get_fetchstructure( $mid );

        $mail = $this->mail_get_parts( $mid, $mail, 0 );

        return $mail;
    }

    function mail_get_parts( $mid, $part, $prefix ) {   
        $attachments = array();
        $attachments[$prefix] = $this->mail_decode_part( $mid,$part,$prefix );

        if ( ! isset( $attachments[$prefix]['is_attachment'] ) ) {
            unset( $attachments[$prefix] );
        } else if ( isset( $attachments[$prefix]['is_attachment'] ) && ! $attachments[$prefix]['is_attachment'] ) {
            unset( $attachments[$prefix] );
        }

        if ( isset( $part->parts ) ) {
            $prefix = ( $prefix == "0" ) ? "" : "$prefix.";
            foreach ( $part->parts as $number => $subpart )
                $attachments = array_merge( $attachments, $this->mail_get_parts( $mid, $subpart, $prefix.( $number+1 ) ) );
        }

        return $attachments;
    }

    function mail_decode_part( $message_number, $part, $prefix ) {
        $attachment = array();

        if($part->ifdparameters) {

            foreach($part->dparameters as $object) {
                $attachment[strtolower($object->attribute)]=$object->value;
                if(strtolower($object->attribute) == 'filename') {
                    $attachment['is_attachment'] = true;
                    $attachment['filename'] = $object->value;
                }
            }
        }

        if($part->ifparameters) {
            foreach($part->parameters as $object) {

                $attachment[strtolower($object->attribute)]=$object->value;
                if(strtolower($object->attribute) == 'name') {
                    $attachment['is_attachment'] = true;
                    $attachment['name'] = $object->value;
                }
            }
        }

        $attachment['data'] = imap_fetchbody( $this->imap, $message_number, $prefix );

        if( $part->encoding == 3 ) { // 3 = BASE64
            $attachment['data'] = base64_decode($attachment['data']);
        
        } elseif( $part->encoding == 4 ) { // 4 = QUOTED-PRINTABLE
            $attachment['data'] = quoted_printable_decode($attachment['data']);
        }
        return($attachment);
    }

    function wp_update_attachment( $file_loc, $file_name, $mimetype, $post_id ) {

        $file_name = basename( $file_name );
        $file_type = wp_check_filetype( $file_name );

        $attachment = array(
            'post_mime_type' => $mimetype,
            'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment( $attachment, $file_loc );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file_loc );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        add_post_meta( $post_id, '_wp_attachment_id', $attach_id );
    }

    function new_attachment( $msg_num ) {
        $over_view = $this->get_overviews( $msg_num );

        if ( ! $over_view ) {
            return false;
        }

        $over_view = reset( $over_view );

        if ( $this->is_message_uid_exist( $over_view ) ) {
            return false;
        }

        
        $title = $over_view->subject;
        $content = $this->get_body_text( $msg_num );

        $arg = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_type' => 'mdrop_file',
            'post_status' => 'publish'
        );

        $post_id = wp_insert_post( $arg );

        if ( $post_id ) {
            $this->post_meta( $post_id, $over_view );
            $uid = $over_view->uid;
            $this->move_attachment( $post_id, $msg_num, $uid );
        }
    }

    function is_message_uid_exist( $over_view ) {
        $uid = $over_view->uid;

        $arg = array(
            'post_type' => 'mdrop_file',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key'     => '_uid',
                    'value'   => $uid,
                    'compare' => '=',
                )
            )
        );

        $results = new WP_Query( $arg );
        
        if ( $results->posts ) {
            return true;
        }

        return false;
    }

    function move_attachment( $post_id, $msg_num, $uid ) {
        
        $attachemts = $this->get_attachment_array( $msg_num );

        if ( ! $attachemts ) {
            update_post_meta( $post_id, '_attachment_exist', 0 );
            return;
        } 
        
        $upload = wp_upload_dir();
        $new_dir = $upload['path']  .'/'. $uid;
        
        if ( ! $this->new_directory_exist( $new_dir ) ) {
            if( ! mkdir( $new_dir, 0777, true ) ) {
                return false;
            }
        }
        update_post_meta( $post_id, '_attachment_exist', 1 );

        foreach ( $attachemts as $key => $attchemt_attr ) {
            $upload_dir = $upload['path']  .'/'. $uid .'/'. $attchemt_attr['name'];
            file_put_contents( $upload_dir,  $attchemt_attr['data'] );
            $mimetype = mime_content_type( $upload_dir ); 
            $this->wp_update_attachment( $upload_dir, $attchemt_attr['name'], $mimetype, $post_id );
        }
    }

    function post_meta( $post_id, $over_view ) {
        $from = $over_view->from;
        $uid = $over_view->uid;
        $message_id = $over_view->message_id;
        $date = $over_view->date;

        update_post_meta( $post_id, '_from', $from );
        update_post_meta( $post_id, '_uid', $uid );
        update_post_meta( $post_id, '_message_id', $message_id );
        update_post_meta( $post_id, '_date', $date );
    }

    /**
     * Checks if a folder exist and return canonicalized absolute pathname (long version)
     * @param string $folder the path being checked.
     * @return mixed returns the canonicalized absolute pathname on success otherwise FALSE is returned
     */
    function new_directory_exist($folder) {
        // Get canonicalized absolute pathname
        $path = realpath($folder);

        // If it exist, check if it's a directory
        if($path !== false AND is_dir($path)) {
            // Return canonicalized absolute pathname
            return $path;
        }

        // Path/folder does not exist
        return false;
    }
}
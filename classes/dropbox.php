<?php
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
ini_set('max_input_time', 300);
ini_set('max_execution_time', 300);

//Dropbox name space
use \Dropbox as dbx;

class MDROP_Dropbox {
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

         /**
     * Upload file to dropbox
     * @param  init $comment_id
     * @param  init $project_id
     * @param  array $commentdata
     * @return void
     */
    function upload_file_dropbox( $post_id = 0, $attachment_id ) {

        require_once MDROP_PATH . '/lib/dropbox/autoload.php';

        $accesstoken = mdrop_get_token( get_current_user_id() ); //'v1FIiYf35K4AAAAAAAADPLhrp-T8miNShTgeuJ5zrDmyb39gf411tgDJx22_ILSK'; //$this->dropbox_accesstoken;

        $dbxClient = new dbx\Client( $accesstoken, "PHP-Example/1.0" );
        $accountInfo = $dbxClient->getAccountInfo();
        $post = get_post( $post_id );
        $files = get_attached_file( $attachment_id );

        $file_name = basename( get_attached_file( $attachment_id ) );

        $drop_path = '/EmailAttachment/' . $post->post_title .'/'. $file_name;
        $shareableLink = $dbxClient->createShareableLink( $drop_path );
        
        if ( ! empty( $shareableLink ) ) {
            return;
        }


        $f = fopen( $files, "rb");
        $uploaded_file = $dbxClient->uploadFile( $drop_path , dbx\WriteMode::add(), $f);
        fclose($f);

        $dropbox_file_path = $uploaded_file['path'];
        $shareableLink = $dbxClient->createShareableLink( $drop_path );

        $this->update_dropbox_shareablelink( $attachment_id, $shareableLink );
        $this->update_dropbox_filepath( $attachment_id, $dropbox_file_path );
    }

    /**
     * Update dropbox shareable link
     * @param  init  $id
     * @param  array or string $data
     * @return void
     */
    function update_dropbox_shareablelink( $id, $data ) {
        update_post_meta( $id, 'dropbox_shareablelink', $data );
    }

    /**
     * Update dropbox file path
     * @param  init  $id
     * @param  array or string $data
     * @return void
     */
    function update_dropbox_filepath( $id, $data ) {
        update_post_meta( $id, 'dropbox_file_path', $data );
    }
}
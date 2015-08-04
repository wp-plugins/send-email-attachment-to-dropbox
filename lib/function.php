<?php

function mdrop_get_query_args() {

    $menu = mdrop_page();

    $page = isset( $_GET['page'] ) && !empty( $_GET['page'] ) ? $_GET['page'] : false;

    if ( !$page ) {
        $query = array(
            'page'   => false,
            'tab'    => false,
            'subtab' => false,
        );
        return apply_filters( 'zapbbp_query_var', $query );
    }

    if ( isset( $_GET['tab'] ) && !empty( $_GET['tab'] ) ) {
        $tab = $_GET['tab'];
    } else if ( isset( $menu[$page] ) && is_array( $menu[$page] ) ) {
        $tab = array_keys( $menu[$page] );
        $tab = reset( $tab );
        $tab = isset( $menu[$page]['tab'] ) && ( $menu[$page]['tab'] === false ) ? false : $tab;
    } else {
        $tab = false;
    }

    if ( !$tab ) {
        $query = array(
            'page' => $page,
            'tab'  => false,
            'subtab' => false,
        );

        return apply_filters( 'zapbbp_query_var', $query );
    }

    if ( isset( $_GET['sub_tab'] ) && !empty( $_GET['sub_tab'] ) ) {
        $subtab = $_GET['sub_tab'];
    } else if ( isset( $menu[$page][$tab]['submenu'] ) && count( $menu[$page][$tab]['submenu'] ) ) {
        $subtab = array_keys( $menu[$page][$tab]['submenu'] );
        $subtab = reset( $subtab );
    } else {
        $subtab = false;
    }
    if ( !$subtab ) {
        $query = array(
            'page'   => $page,
            'tab'    => $tab,
            'subtab' => false,
        );
        return apply_filters( 'zapbbp_query_var', $query );
    } else {
        $query = array(
            'page'   => $page,
            'tab'    => $tab,
            'subtab' => $subtab,
        );

        return apply_filters( 'zapbbp_query_var', $query );
    }
}

function mdrop_page_slug() {
    $menu = mdrop_menu_label();
    foreach ( $menu as $page_slug => $value ) {
        break;
    }

    return $page_slug ? $page_slug : false;
}

function mdrop_get_server( $user_id ) {
    return get_user_meta( $user_id, '_mdrop_mail_server', true );
    //return "{imap.gmail.com:993/imap/ssl}INBOX";
}

function mdrop_get_email( $user_id ) {
    return get_user_meta( $user_id, '_mdrop_email', true );
    //return "joy.mishu@gmail.com";
}

function mdrop_get_password( $user_id ) {
    return get_user_meta( $user_id, '_mdrop_password', true );
    //return "070944810";
}

function mdrop_get_token( $user_id ) {
    return get_user_meta( $user_id, '_mdrop_token', true );
    //return "070944810";
}

function mdrop_get_query_message( $limit = -1 ) {
    
    $pagenum = mdrop_pagenum();
    $offset  = ( $pagenum - 1 ) * $limit;
    
    $arg = array(
        'post_type' => 'mdrop_file',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'meta_query' => array(
            array(
                'key' => '_attachment_exist',
                'value' => '1',
                'compare' => '='
            )
        )
    );

    if ( $limit != '-1' ) {
        $arg['offset'] = $offset;
    }

    return new WP_Query( $arg );
}

function mdrop_pagination( $total, $limit = 1, $pagenum = false ) {

    $num_of_pages = ceil( $total / $limit );

    $page_links = paginate_links( array(
        'base'               => add_query_arg( 'pagenum', '%#%' ),
        'format'             => '',
        'prev_text'          => __( '&laquo;', 'aag' ),
        'next_text'          => __( '&raquo;', 'aag' ),
        'add_args'           => false,
        'total'              => $num_of_pages,
        'current'            => $pagenum,
        'before_page_number' => '<span class="button-secondary">',
        'after_page_number'  => '</span>'
    ) );

    if ( $page_links ) {
        return '<div class="hrm-pagination"><div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div></div>';
    }
}

function mdrop_pagenum() {
    return isset( $_REQUEST['pagenum'] ) ? intval( $_REQUEST['pagenum'] ) : 1;
}

/**
 * Get an attachment file
 *
 * @param int $attachment_id
 * @return array
 */
function mdrop_get_file( $attachment_id ) {
    $file = get_post( $attachment_id );

    if ( $file ) {

        $response = array(
            'id' => $attachment_id,
            'name' => get_the_title( $attachment_id ),
            'url' => wp_get_attachment_url( $attachment_id ),
        );

        if ( wp_attachment_is_image( $attachment_id ) ) {

            $thumb = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
            $response['thumb'] = $thumb[0];
            $response['type'] = 'image';
        } else {
            $response['thumb'] = wp_mime_type_icon( $file->post_mime_type );
            $response['type'] = 'file';
        }

        return $response;
    }

    return false;
}

function mdrop_connect_server() {
    $user_id = get_current_user_id();
    $server = mdrop_get_server( $user_id );
    $email = mdrop_get_email( $user_id );
    $password = mdrop_get_password( $user_id );
    return MDROP_Attachment_List::instance( $server, $email, $password );
}



<?php
function mdrop_page( $exclude = true ) {

    $path                  = MDROP_PATH . '/templates';
    $page                  = array();

    $mdrop_management        = mdrop_admin_page();
    $page[$mdrop_management] = mdrop_admin_page_items( $path, $mdrop_management, $exclude );

    return apply_filters( 'mdrop_menu_items', $page, $exclude );
}


function mdrop_admin_page_items( $path, $mdrop_management, $exclude ) {
    $admin = array();

    $admin['attachment-settings'] = array(
        'id'        => 'mdrop-attachment-settings',
        'title'     => __( 'Settings', 'mdrop' ),
        'file_slug' => 'attachment/settings',
        'file_path' => $path . '/attachment/settings.php',
    );

    $admin['attachment-lists'] = array(
        'id'        => 'mdrop-attachment-lists',
        'title'     => __( 'Attachment List', 'mdrop' ),
        'file_slug' => 'attachment/lists',
        'file_path' => $path . '/attachment/lists.php',
    );
    


    return apply_filters( 'mdrop_admin_page_items', $admin, $path, $mdrop_management );
}

function mdrop_admin_page() {
    return apply_filters( 'mdrop_admin_page_slug', 'mdrop' );
}

function mdrop_menu_label() {
    $labels = array(
        mdrop_admin_page()  => __( 'Admin', 'hrm' ),
    );
    return apply_filters( 'hrm_menu_lable', $labels );
}


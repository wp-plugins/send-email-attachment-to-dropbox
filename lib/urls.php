<?php
function mdrop_tab_menu_url( $tab = null, $page ) {
    $url = sprintf( '%1s?page=%2s&tab=%3s', admin_url( 'admin.php' ), $page, $tab );
    return apply_filters( 'mdrop_tab_menu_url', $url, $page, $tab );
}
<?php
/**
 * Plugin Name: E-mail attachment to dropbox
 * Description: Fetch your email attachment and send it to dropbox. You can make it also wp-post
 * Author: asaquzzaman
 * Version: 0.1
 * Author URI: http://mishubd.com
 * License: GPL2
 * TextDomain: mdrop
 */

/**
 * Copyright (c) 2013 Asaquzzaman Mishu (email: joy.mishu@gmail.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 * **********************************************************************
 */


class WP_Mdrop {

    /**
     * @var The single instance of the class
     * @since 0.1
     */
    protected static $_instance = null;

    static $mdrop_dependency;
    public $settings;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function __construct() {
        $this->initial();

        $this->instantiate();
        add_action( 'plugins_loaded', array( $this, 'load_textdomain') );
        add_action( 'admin_menu', array($this, 'admin_menu') );
        add_action( 'init', array($this, 'register_post_types') );
        add_action( 'admin_init', array($this, 'save_setting') );
    }

    function save_setting() {
      
        if ( ! isset( $_POST['mdrop_settings'] ) ) {
            return;
        }

        $user_id = get_current_user_id();

        update_user_meta( $user_id, '_mdrop_mail_server', trim( $_POST['mail_server'] ) );
        update_user_meta( $user_id, '_mdrop_email', trim( $_POST['email'] ) );
        update_user_meta( $user_id, '_mdrop_password', trim( $_POST['password'] ) );
        update_user_meta( $user_id, '_mdrop_token', trim( $_POST['drop_token'] ) );
    }

    function initial() {
        $this->define_constants();
        require_once MDROP_PATH . '/lib/function.php';
        require_once MDROP_PATH . '/lib/urls.php';
        require_once MDROP_PATH . '/lib/page.php';
        spl_autoload_register( array( __CLASS__, 'autoload' ) );

    }

    function autoload( $class ) {
        $class = str_replace( '_', '-', $class );
        $name = explode( '-', $class );

        if ( isset( $name[1] ) ) {
            unset( $name[0] );
            $name = implode( '-', $name );
            $class_name = strtolower( $name );
            $filename = dirname( __FILE__ ) . '/classes/' . $class_name . '.php';
            if ( file_exists( $filename ) ) {
                require_once $filename;
            }
        }
    }

    function register_post_types() {
        register_post_type( 'mdrop_file', array(
            'label'               => __( 'File', 'hrm' ),
            'public'              => false,
            'show_in_admin_bar'   => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'show_in_admin_bar'   => false,
            'show_ui'             => false,
            'show_in_menu'        => false,
            'capability_type'     => 'post',
            'hierarchical'        => false,
            'rewrite'             => array('slug' => ''),
            'query_var'           => true,
            'supports'            => array('title', 'editor'),

        ) );
    }

    private function define_constants() {
        $this->define( 'MDROP_VERSION', '0.1' );
        $this->define( 'MDROP_DB_VERSION', '0.1' );
        $this->define( 'MDROP_PATH', dirname( __FILE__ ) );
        $this->define( 'MDROP_URL', plugins_url( '', __FILE__ ) );
    }

    /**
     * Define constant if not already set
     *
     * @since 1.1
     *
     * @param  string $name
     * @param  string|bool $value
     * @return type
     */
    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    /**
     * Load plugin textdomain
     *
     * @since 0.3
     */
    function load_textdomain() {
        load_plugin_textdomain( 'mdrop', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    static function admin_scripts() {

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'mdrop', plugins_url( '/assets/js/mdrop.js', __FILE__ ), array( 'jquery' ), false, true);
        wp_localize_script( 'mdrop', 'mdrop_ajax_data', array(
            'ajax_url'    => admin_url( 'admin-ajax.php' ),
            '_wpnonce'    => wp_create_nonce( 'mdrop_nonce' ),
        ));

        wp_enqueue_style( 'hrm-admin', plugins_url( '/assets/css/style.css', __FILE__ ), false, false, 'all' );

    }

    function instantiate() {
        $this->settings = MDROP_Settings::instance();
        MDROP_Ajax::instance();
    }

    function admin_menu() {
        $capability = 'read'; //minimum level: subscriber
        $label      = mdrop_menu_label();
        $mdrop_page_slug = mdrop_page_slug();
        if ( ! $mdrop_page_slug ) {
            return;
        }

        $menu  = add_menu_page( __( 'E-mail to Dropbox', 'mdrop' ), __( 'E-mail to Dropbox', 'mdrop' ), $capability, $mdrop_page_slug, array($this, 'admin_page_handler'), 'dashicons-images-alt2'  );

        foreach ( mdrop_menu_label() as $page_slug => $page_label ) {

            $style_slug[$page_slug] = add_submenu_page( $mdrop_page_slug, $page_label, $page_label, $capability, $page_slug, array($this, 'admin_page_handler') );

        }

        if( isset( $style_slug[mdrop_admin_page()] ) ) {
            add_action( 'admin_print_styles-' . $style_slug[mdrop_admin_page()], array( $this, 'admin_scripts') );
        }

        do_action( 'mdrop_admin_menu', $this, $style_slug );

    }


    function admin_page_handler() {
        if( ! is_user_logged_in() ) {
            return;
        }

        $query_args = mdrop_get_query_args();
        $page       = $query_args['page'];
        $tab        = $query_args['tab'];
        $subtab     = $query_args['subtab'];

        echo '<div class="mdrop wrap" id="mdrop">';
        if ( $tab === false ) {
            $this->settings->show_page( $page );
        } else {
            $this->settings->show_tab_page( $page, $tab, $subtab );
        }

        echo '</div>';
    }
}

function mdrop() {
    return WP_Mdrop::instance();
}

mdrop();






<?php 
/*******************************************/
/* Register Plugin Public Scripts
/*******************************************/

if ( ! defined( 'ABSPATH' ) ) exit;//don't allow peekaboos

function ats_plugin_styles(){
    wp_enqueue_style( 'pluginfrontcss', ATS_PLUGIN_ROOT_URL . 'public/css/style.css');
}

function ats_plugin_scripts() {
	wp_enqueue_script( 'touchswipe', ATS_PLUGIN_ROOT_URL . 'public/js/jquery.touchswipe.min.js', array('jquery'), '', true );
	wp_enqueue_script( 'domreadypublic', ATS_PLUGIN_ROOT_URL . 'public/js/dom-ready-public.js', array('jquery'), '', true );
}

add_action( 'wp_enqueue_scripts', 'ats_plugin_styles' );
add_action( 'wp_enqueue_scripts', 'ats_plugin_scripts' );

/*******************************************/
/* Register Plugin Admin Scripts
/*******************************************/
function ats_plugin_admin_scripts() {
		if( $_GET['page'] == 'animated-twitter-slideshow' )	 {
			wp_enqueue_script( 'domreadyadmin', ATS_PLUGIN_ROOT_URL . 'admin/js/dom-ready-admin.js', array('jquery'), '', true );
		}
}
add_action( 'admin_enqueue_scripts', 'ats_plugin_admin_scripts' );
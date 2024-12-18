<?php 
define( 'RPE_URI', plugins_url( 'rest-api-endpoints' ) );
define( 'RPE_TEMPLATE_PATH', plugin_dir_path( __FILE__ ) . 'templates/' );
define( 'RPE_PLUGIN_PATH', __FILE__ );

include_once 'inc/custom-functions.php';
include_once 'inc/class-rae-customizer.php';
include_once 'inc/class-rae-register-text-widget.php';
include_once 'apis/class-rae-register-header-footer-api.php';
include_once 'apis/class-rae-register-auth-api.php';
include_once 'apis/class-rae-register-posts-api.php';
include_once 'apis/class-rae-register-get-posts-api.php';
include_once 'apis/class-rae-register-get-post-api.php';
include_once 'apis/class-rae-register-parse-blocks.php';
include_once 'apis/class-rae-test.php';

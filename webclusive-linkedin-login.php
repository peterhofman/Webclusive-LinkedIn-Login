<?php
/*
Plugin Name: Webclusive LinkedIn login
Plugin URI:
Description: Add LinkedIn login.
Version: 1.0
Author: WEBclusive
Author URI: http://www.webclusive.com
*/

function webclusive_linkedin_login_init()
{
        //first load translations
        webclusive_linkedin_login_load_translations();

        //now initialize the core
        include_once('lib/webclusiveOAuth.php');
        require_once('core.php');
}

function webclusive_linkedin_login_load_translations()
{    
    if (file_exists( WP_PLUGIN_DIR . "/webclusive-linkedin-login/languages/" . get_locale() . ".mo")) {
        load_textdomain('webclusive_linkedin_login_lang', WP_PLUGIN_DIR . "/webclusive-linkedin-login/languages/" . get_locale() . ".mo");
    }
}

add_action('init', 'webclusive_linkedin_login_init');
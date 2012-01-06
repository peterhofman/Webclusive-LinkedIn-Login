<?php
/**
 * This is the core file for LinkedIn login
 * 
 */
define('WEBCLUSIVE_LINKEDIN_LOGIN_VERSION', '1.0');
define('WEBCLUSIVE_LINKEDIN_LOGIN_IS_INSTALLED', 1);


/** 
 * Add css file 
 */

wp_register_style('webclusive-linkedin-login', plugins_url() . '/webclusive-linkedin-login/templates/style.css', false, WEBCLUSIVE_LINKEDIN_LOGIN_VERSION, 'screen');
wp_enqueue_style('webclusive-linkedin-login');

/**
 * Setup admin navigation
 *
 */
add_action('admin_menu', 'webclusive_linkedin_login_Admin');
add_action('network_admin_menu', 'webclusive_linkedin_login_Admin');

function webclusive_linkedin_login_Admin() 
{
    if (!is_super_admin()) {
    	return false;
    }

     add_menu_page(
        __('LinkedIn login', 'webclusive_linkedin_login_lang'), 
        __('LinkedIn login', 'webclusive_linkedin_login_lang'), 
        'manage_options',
        'webclusive_linkedin_login', 
        'webclusive_linkedin_login_settings',
        plugins_url('images/icon.png', __FILE__)
     );
}


/**
 * Settings page
 *
 */
function webclusive_linkedin_login_settings()
{
    include "templates/settings.php";   
}

/**
 * Function taht displays the login button.
 * 
 */

function webclusive_login_loginbutton(){
    
    if(get_site_option("webclusive_linkedin_consumer_key") && get_site_option("webclusive_linkedin_consumer_secret")){
       
        //call oAuth class
        $webclusiveOAuth = new webclusiveOAuth();
       
        //set api urls
        $webclusiveOAuth->setRequestTokenUrl('https://api.linkedin.com/uas/oauth/requestToken');
        $webclusiveOAuth->setAccessTokenUrl('https://api.linkedin.com/uas/oauth/accessToken');
        $webclusiveOAuth->setAuthorizeUrl('https://api.linkedin.com/uas/oauth/authorize');
        
        //set callback url and keys
        $webclusiveOAuth->setCallbackUrl(get_site_url() . '/?auth=linkedin');
        $webclusiveOAuth->setConsumerKey(get_site_option("webclusive_linkedin_consumer_key"));
        $webclusiveOAuth->setConsumerSecret(get_site_option("webclusive_linkedin_consumer_secret"));

       //get requesttoken and save it for later use (if not in session yet).
       @session_start();
       
       //expire time to update the session
       $expireTime = 0;
       $expireTime = date('dmyHi') - $_SESSION['webclusive_linkedin_token_stamp'];
       
       if(!isset($_SESSION['webclusive_linkedin_token_temp']) or $expireTime > 5){           
            $requestToken = $webclusiveOAuth->requestToken();
            
            $_SESSION['webclusive_linkedin_token_temp']       = $requestToken['oauth_token'];
            $_SESSION['webclusive_linkedin_tokensecret_temp'] = $requestToken['oauth_token_secret'];
            $_SESSION['webclusive_linkedin_token_stamp']      = date('dmyHi');
        }
        
        //set the retrieved token and secret
        $webclusiveOAuth->setRequestToken($_SESSION['webclusive_linkedin_token_temp']);
        $webclusiveOAuth->setRequestTokenSecret($_SESSION['webclusive_linkedin_tokensecret_temp']);
        
        //get the redirect url for the user
        $redirectUrl = $webclusiveOAuth->getRedirectUrl();

        //echo the redirect url
        echo '<a href="' . $redirectUrl . '" class="webclusive_linkedin_login_button">Login met LinkedIn</a>';
    }
}


/*
 * Fetch callback from LinkedIn
 * 
 */

add_action('wp', 'webclusive_linkedin_login_callback',1);
function webclusive_linkedin_login_callback() {
    
    if(isset($_GET['auth']) && $_GET['auth'] == "linkedin"){
        
        //back from linkedIn get the accessToken
        $webclusiveOAuth = new webclusiveOAuth();
        $webclusiveOAuth->setRequestTokenUrl('https://api.linkedin.com/uas/oauth/requestToken');
        $webclusiveOAuth->setAccessTokenUrl('https://api.linkedin.com/uas/oauth/accessToken');
        $webclusiveOAuth->setAuthorizeUrl('https://api.linkedin.com/uas/oauth/authorize');
        
        //set callback and keys
        $webclusiveOAuth->setCallbackUrl(get_site_url().'/?auth=linkedin');
        $webclusiveOAuth->setConsumerKey(get_site_option("webclusive_linkedin_consumer_key"));
        $webclusiveOAuth->setConsumerSecret(get_site_option("webclusive_linkedin_consumer_secret"));
        
        //set the tokens from the session
        @session_start();
        $webclusiveOAuth->setRequestToken($_SESSION['webclusive_linkedin_token_temp']);
        $webclusiveOAuth->setRequestTokenSecret($_SESSION['webclusive_linkedin_tokensecret_temp']);
       
        //dump session
        unset($_SESSION['webclusive_linkedin_token_temp']);
        unset($_SESSION['webclusive_linkedin_tokensecret_temp']);
        
        //set some needed parameters from the request
        $webclusiveOAuth->setParameters(array('oauth_verifier' => esc_attr($_GET['oauth_verifier'])));
        $accessToken = $webclusiveOAuth->accessToken();
   
        //fetch the user info
        $webclusiveOAuth->setAccessToken($accessToken['oauth_token']);
        $webclusiveOAuth->setAccessTokenSecret($accessToken['oauth_token_secret']);
        
        //get the user profile and convert xml to a object
        $userData = $webclusiveOAuth->oAuthRequest('http://api.linkedin.com/v1/people/~:(id,first-name,last-name)');
        $userData = simplexml_load_string($userData);
        
        //if something is wrong and we dont have a userId redirect back to frontpage
        if(empty($userData->{'first-name'})){
            wp_redirect('/');
        }
        
        //check if the user is already a user in the system, if so login the user, if not create a user
        $existingUser = get_user_by_email($userData->id . '@linkedin.user');

        //we found a user login
        if($existingUser){
      
            wp_set_auth_cookie($existingUser->ID, true, false);
            wp_redirect('/');
            
        } else {
            
            //we don't found a user lets create a new one and login
            //check if username is taken
            $i        = '';
            $username = sanitize_user($userData->{'first-name'} . '-' . $userData->{'last-name'}, true);
            while(username_exists($username . $i)){
                    $i = absint($i);
                    $i++;
            }

            //this will be new user login name
            $username = $username . $i;
            
            //put everything a user array
            $newUserData = '';
            $newUserData = array(
                    'user_pass'     =>	wp_generate_password(),
                    'user_login'    =>	$username,
                    'user_nicename' =>	$username,
                    'user_email'    =>	$userData->id . '@linkedin.user',
                    'display_name'  =>	$userData->{'first-name'} . ' ' . $userData{'last-name'},
                    'nickname'      =>	$username,
                    'first_name'    =>	$userData->{'first-name'},
                    'last_name'     =>	$userData->{'last-name'},
                    'role'          =>	'subscriber'
            );

            //insert the user into the system
            $newUserId = wp_insert_user($newUserData);

            //login the new user, and redirect
            wp_set_auth_cookie($newUserId, true, false);
            wp_redirect('/');
        }
    }
}
<link rel="stylesheet" href="<?php echo plugins_url() . '/webclusive-linkedin-login/templates/admin-style.css';?>" type="text/css" />
<div class="webclusive_linkedin_login_intro">
    <img src="<?php echo plugins_url();?>/webclusive-linkedin-login/images/icon-big.png" height="32" align="left"/>
    <h3><?php _e("LinkedIn Login (by WEBclusive)","webclusive_linkedin_login_lang");?></h3>
    <?php _e("With this plugin user can login with LinkedIn.<br/>","webclusive_linkedin_login_lang");?>
</div>

<?php 
if($_POST){
    update_site_option('webclusive_linkedin_consumer_key',  esc_attr($_POST['webclusive_linkedin_consumer_key']));
    update_site_option('webclusive_linkedin_consumer_secret',  esc_attr($_POST['webclusive_linkedin_consumer_secret']));
    
    echo '<div class="webclusive_linkedin_login_info_box_green">'.__("Settings saved","webclusive_linkedin_login_lang").'</div>';
}

?>

<form action="" method="post">
    <table cellpadding="0" cellspacing="0" class="webclusive_linkedin_login_table">
        <tr class="header"><td colspan="2"><?php _e("Linkedin Login API","webclusive_linkedin_login_lang");?></td></tr>

            <tr>
                <td width="190"><?php _e("API key:","webclusive_linkedin_login_lang");?></td>
                <td><input type="text" name="webclusive_linkedin_consumer_key" value="<?php echo get_site_option('webclusive_linkedin_consumer_key'); ?>" size="30"/></td>
            </tr>
            <tr>
                <td width="190"><?php _e("API secret:","webclusive_linkedin_login_lang");?></td>
                <td><input type="text" name="webclusive_linkedin_consumer_secret" value="<?php echo get_site_option('webclusive_linkedin_consumer_secret'); ?>" size="30"/></td>
            </tr>

             <tr>
                <td colspan="2"><input type="submit" name="submit" value="<?php _e("Save settings","webclusive_linkedin_login_lang");?>"</td>
            </tr>       
    </table>
</form>
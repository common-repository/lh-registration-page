<?php
/**
 * Plugin Name: LH Registration Page
 * Plugin URI: https://lhero.org/portfolio/lh-registration-page/
 * Description: This plugin allows users to easily add a simple user registration form and login form  anywhere on their site using simple shortcode. And provide setting to 'Auto Login After Registration'. 
 * Author: Peter Shaw
 * Version: 1.02
 * Author URI: https://shawfactor.com/
*/

if (!class_exists('LH_Registration_page_plugin')) {


class LH_Registration_page_plugin {
    
    var $filename;
    var $options;
    var $opt_name = 'lh_registration_page-options';
    var $namespace = 'lh_registration_page';
    var $registration_page_id = 'lh_registration_page-registration_page_id';
    var $welcome_page_id = 'lh_registration_page-welcome_page_id';
    var $use_email_field_name = 'lh_registration_page-use_email';
    var $welcome_notification = 'lh_registration_page-welcome_notification';
    
    
    private static $instance;
    
    private function isValidURL($url){ 

    return (bool)parse_url($url);
        
    }
    
    
private function role_exists( $role ) {

  if( ! empty( $role ) ) {
    return $GLOBALS['wp_roles']->is_role( $role );
  }
  
  return false;
}

    
    /**
     * Helper function for registering and enqueueing scripts and styles.
     *
     * @name    The    ID to register with WordPress
     * @file_path        The path to the actual file
     * @is_script        Optional argument for if the incoming file_path is a JavaScript source file.
     */
    private function load_file( $name, $file_path, $is_script = false, $deps = array(), $in_footer = true, $atts = array() ) {
        $url  = plugins_url( $file_path, __FILE__ );
        $file = plugin_dir_path( __FILE__ ) . $file_path;
        if ( file_exists( $file ) ) {
            if ( $is_script ) {
                wp_register_script( $name, $url, $deps, filemtime($file), $in_footer ); 
                wp_enqueue_script( $name );
            }
            else {
                wp_register_style( $name, $url, $deps, filemtime($file) );
                wp_enqueue_style( $name );
            } // end if
        } // end if
	  
	  if (isset($atts) and is_array($atts) and isset($is_script)){
		
		
  $atts = array_filter($atts);

if (!empty($atts)) {

  $this->script_atts[$name] = $atts; 
  
}

		  
	 add_filter( 'script_loader_tag', function ( $tag, $handle ) {
	   

	   
if (isset($this->script_atts[$handle][0]) and !empty($this->script_atts[$handle][0])){
  
$atts = $this->script_atts[$handle];

$implode = implode(" ", $atts);
  
unset($this->script_atts[$handle]);

return str_replace( ' src', ' '.$implode.' src', $tag );

unset($atts);
usent($implode);

		 

	 } else {
	   
 return $tag;	   
	   
	   
	 }
	

}, 10, 2 );
 

	
	  
	}
		
    } // end load_file
    
    
private function uri_to_array($uri){

$uri = str_replace('&amp;', '&', $uri);

$url = parse_url($uri);
$query = array();
  
  if (isset($url['query'])){
 
parse_str($url['query'], $query);

return $query;
	
  } else {

return false;	
	
  }
}



private function overide_username( $user_id) {
    
$userdata = get_user_by( 'ID', $user_id );

if ( $userdata ){
    
global $wpdb;

// Update username!
		$sql = $wpdb->prepare( "UPDATE $wpdb->users SET user_login = %s WHERE ID = %s", $userdata->user_email, $user_id );
		$wpdb->query( $sql );
}
    
}
  
  
  
  
private function clean_username($username) {
      
      
$username = sanitize_user( trim($username));
$username = str_replace('@' , '', $username );
$username = str_replace('.' , '', $username );
$username = str_replace('+' , '', $username ) ;

return $username;
      
      
  }
  
 private function personalise_message($message, $user){
     

     
    


$message = str_replace('%user_email%', $user->user_email, $message);
$message = str_replace('%user_login%', $user->user_login, $message);
$message = str_replace('%display_name%', $user->display_name, $message);
$message = str_replace('%bloginfo_name%',get_bloginfo('name','display'), $message);

$message = str_replace('%first_name%', get_user_meta( $user->ID, 'first_name', true ), $message);
$message = str_replace('%last_name%', get_user_meta( $user->ID, 'last_name', true ), $message);

return $message;

}
  
 private function use_email_template( $subject, $message ) {

if (file_exists(get_stylesheet_directory().'/'.$this->namespace.'-template.php')){

ob_start();

include( get_stylesheet_directory().'/'.$this->namespace.'-template.php');

$message = ob_get_contents();

ob_end_clean();


} else {

ob_start();

include( plugin_dir_path( __FILE__ ).'/'.$this->namespace.'-template.php');

$message = ob_get_contents();

ob_end_clean();


}


if (!class_exists('LH_Css_To_Inline_Styles')) {


require_once('includes/lh-css-to-inline-styles-class.php');


}


$doc = new DOMDocument();

$doc->loadHTML($message);

// create instance
$lh_css_to_inline_styles = new LH_Css_To_Inline_Styles();

$lh_css_to_inline_styles->setHTML($message);

$lh_css_to_inline_styles->setCSS($doc->getElementsByTagName('style')->item(0)->nodeValue);

// output

$message = $lh_css_to_inline_styles->convert(); 

return $message;

}


public function filter_default_role( $role ) {
    
    if ($this->role_exists('unclaimed')){
     
    return 'unclaimed';   
        
    } else {

    return $role;
    
    }
} 
  
  
private function send_welcome($userid){
    
$user = get_user_by('ID', $userid );
    
    
$welcome_notification_option = get_option($this->welcome_notification);

if (isset($welcome_notification_option['subject']) and !empty($welcome_notification_option['subject'])){

$subject = $this->personalise_message($welcome_notification_option['subject'], $user);
     
}

if (isset($welcome_notification_option['message']) and !empty($welcome_notification_option['message'])){
    
$message = wpautop(do_shortcode($welcome_notification_option['message']));    
    
$message = $this->personalise_message($message, $user);

$body = $this->use_email_template($subject, $message);


}


$headers = array('Content-Type: text/html; charset=UTF-8');


if (isset($subject) && !empty($subject) && isset($body) && !empty($body)){
    
wp_mail( $user->user_email, $subject, $body, $headers);
    
    
}

}




public function wp_new_user_notification_email($wp_new_user_notification_email, $user, $blogname){
     
return false;
    
   
}
    // function to registration Shortcode
public function lh_registration_page_form_shortcode_output( $atts ) {
    global $wpdb, $user_ID; 
	$firstname='';
	$lastname='';
	$username='';
	$email='';
	
	ob_start();
	
	$return_string = '';
	
	//if logged in rediret to home page
	if ( is_user_logged_in() ) { 
	    wp_redirect( get_option('home') );// redirect to home page
		exit;
	}

	if(isset($_POST['lrp_submit']) and sanitize_text_field( $_POST['lrp_submit']) != ''){
	    
	    
add_filter( 'wp_new_user_notification_email', array($this,'wp_new_user_notification_email'), 10, 3 );

		$firstname = sanitize_text_field( $_REQUEST['lrp_firstname'] );
		$lastname = sanitize_text_field( $_REQUEST['lrp_lastname']);
		
		if (isset($_REQUEST['lrp_username']) and !empty($_REQUEST['lrp_username'])){
		    
		    $username = trim($_REQUEST['lrp_username']);
		} elseif (get_option($this->use_email_field_name) == 1){
		    
		    $username = $this->clean_username($_REQUEST['lrp_email']);

		} else {
		    
		    $errors = new WP_Error( 'username_missing', __( 'No username was specified', $this->namespace ) );
		
		    }
		    
		$email = sanitize_text_field(  $_REQUEST['lrp_email']  );
		$password = sanitize_text_field( $_REQUEST['lrp_password']);
		
		if (username_exists($username ) ){
		    
		   $errors = new WP_Error( 'username_exists', __( "Username or Email already registered. Please try another one", $this->namespace ) );

		    
		    
		} elseif (email_exists( $email ) ){
		    
		    $errors = new WP_Error( 'email_exists', __( "Username or Email already registered. Please try another one", $this->namespace ) );

		    
		    
		}
		

		


		
		if (!isset($errors) or !is_wp_error($errors))  {
		    
		//change the role used to unclaimed if that exists
		add_filter( 'option_default_role', array($this,'filter_default_role'), 10, 1 );
		
		$status = register_new_user($username,$email);
		
		} 
	  
		if (is_wp_error($status) or (isset($errors) && is_wp_error($errors)))  {
		    

    if (is_wp_error($errors)){		    
		    
		     $error_object = $errors;
		     
    } else {
        
        $error_object = $status;
        
        
    }
		     
		     
		     if (isset($_GET['redirect_to']) and ($this->isValidURL($_GET['redirect_to']))){
		         
		         $password_reset_url = wp_lostpassword_url($_GET['redirect_to']);
		         
		         } else {
		         
		         $password_reset_url = wp_lostpassword_url();
		         
		     }
		     
		     $password_reset_url = add_query_arg( 'reset_email', $email , $password_reset_url);
		     
		     $errors = $error_object->get_error_messages();
		     $error_msg = "";
		     
foreach ($errors as $error) {
        $error_msg .= $error; 
}
		     
		     
		     
		     $error_msg .= '<a href="'.$password_reset_url.'">';
		     
		     $error_msg .= __('reset your pasword', $this->namespace);
		     
		     $error_msg .= '</a>';
		} else {
			$user_id = $status;
			
			if (get_option($this->use_email_field_name) == 1){
			$this->overide_username( $user_id);
			}
			

//prevent the password change email as we are setting this automatically
add_filter( 'password_change_email', function( $pass_change_email, $user, $userdata) {

return false;

}, 10, 3 );

	
        $args = array(
		'ID' => $user_id,
		'user_pass' => $password,
		'first_name' => $firstname,
		'last_name' => $lastname,
		'nickname' => $firstname,
		'display_name' => $firstname.' '.$lastname
	);
	
	wp_update_user( $args );
	
			//the user has set a password so remove the password nag
			update_user_option($user_id, 'default_password_nag', false, true);
			
			$this->send_welcome($user_id);
			

			 $alar_enable_auto_login= 'true';
			
			
			if($alar_enable_auto_login == 'true'){
				if(!is_user_logged_in()){
					$secure_cookie = is_ssl();
					$secure_cookie = apply_filters('secure_signon_cookie', $secure_cookie, array());
					global $auth_secure_cookie;
					$auth_secure_cookie = $secure_cookie;
					wp_set_auth_cookie($user_id, true, $secure_cookie);
					$user_info = get_userdata($user_id);
					do_action('wp_login', $user_info->user_login, $user_info);
				}
			}
			//code to auto login end
			
			
if (isset($_GET['redirect_to']) and ($this->isValidURL($_GET['redirect_to']))){

$redirect_to = $_GET['redirect_to'];

  }	else {
      
      $redirect_to = '';
      
  }
      
$redirect_to = apply_filters( 'registration_redirect', $redirect_to);

if (empty($redirect_to) or !$this->isValidURL($redirect_to)){
    
$permalink = get_permalink(get_option($this->welcome_page_id));

if (isset($permalink) && !empty($permalink) && $this->isValidURL($permalink)){
  
    $redirect_to =  $permalink;  
    
} else {
    
    $redirect_to =  get_option('home');
    
}
    

    
  
    
}
        
        
        
		wp_redirect($redirect_to); exit();
		}  
	}
	
	
?>


		<?php if(isset($error_msg) and !empty($error_msg)) { ?><span class="lh_registration-error"><?php echo $error_msg; ?></span><?php }  ?>
		<form  name="form" id="registration"  method="post">
			<p>
			 <label for="lrp_firstname"><?php _e("First Name :",'');?></label> 
			 <input id="lrp_firstname" name="lrp_firstname" type="text" class="input" required="required" value="<?php echo $firstname; ?>" placeholder="Your First Name" autocomplete="fname" autocapitalize="words" /> 
			</p>
			<p>
			 <label for="lrp_lastname"><?php _e("Last name :",'');?></label>  
			 <input id="lrp_lastname" name="lrp_lastname" type="text" class="input" required="required" value="<?php echo $lastname; ?>" placeholder="Your Last Name" autocomplete="lname" autocapitalize="words" />
			</p>
			
<?php 

$use_email_field_name = get_option($this->use_email_field_name);

if (!isset($use_email_field_name) or ($use_email_field_name != 1)){ ?>
			<p>
			<label for="lrp_username"><?php _e("Username :",'');?> </label>
			 <input id="lrp_username" name="lrp_username" type="text" class="input" required="required" value="<?php echo $username; ?>" placeholder="a username" autocapitalize="none"  />
			</p>
<?php } ?>
			<p>
			<label for="lrp_email"><?php _e("E-mail :",'');?> </label>
			 <input id="lrp_email" name="lrp_email" type="email" class="input" required="required" value="<?php echo $email; ?>" placeholder="Your Email" autocomplete="email" />
			</p>

        <?php do_action( 'register_form' ); ?>
			<p>
			<label for="lrp_password"><?php _e("Password :",'');?></label>
			 <input id="lrp_password" name="lrp_password" type="password" required="required" class="input"  placeholder="Your Password" autocomplete="off" />
			 </p>
			 <p>
			 <label for="show_password">
	<input type="checkbox" name="show_password" id="show_password">
	Show Password
	</p>
</label>
	
	<input type="submit" name='lrp_submit' class="button"  value="Register"/>
	<p>
		</form>

<?php

    $array[] = 'id="'.$this->namespace.'-frontend-script"';
    $array[] = 'defer="defer"';
    $array[] = 'async="async"';

// include js to enhance funnctionality
$this->load_file( $this->namespace.'-functions-js', '/scripts/frontend-script.js', true, array(), true, $array );

$return_string .= ob_get_contents();
ob_end_clean();


return preg_replace('~>\s+<~', '><', preg_replace( "/\r|\n/", "", $return_string));

}

    
    
    public function register_shortcodes(){
        
        
//add registration shortcode
add_shortcode( 'lh_registration_page_form', array($this,"lh_registration_page_form_shortcode_output") );






}


public function plugin_menu() {
add_options_page(__('LH Registration Page Options', $this->namespace ), __('Registration Page', $this->namespace ), 'manage_options', $this->filename, array($this,"plugin_options"));

}

public function plugin_options() {

if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	
	
	 // See if the user has posted us some information
    // If they did, the nonce will be set

	if( isset($_POST[ $this->namespace."-backend_nonce" ]) && wp_verify_nonce($_POST[ $this->namespace."-backend_nonce" ], $this->namespace."-backend_nonce" )) {
	    
	    if (($_POST[ $this->registration_page_id ] != "") and ($page = get_page(sanitize_text_field($_POST[ $this->registration_page_id ])))){

if ( has_shortcode( $page->post_content, 'lh_registration_page_form' ) ) {

$registration_page_id_add = sanitize_text_field($_POST[ $this->registration_page_id ]);

if (update_option( $this->registration_page_id, $registration_page_id_add )){

$registration_page_id_option = get_option($this->registration_page_id);


?>
<div class="updated"><p><strong><?php _e('Registration Page ID saved', $this->namespace ); ?></strong></p></div>
<?php

}

} else {
    
_e("shortcode not found", $this->namespace );




}

}

if (($_POST[ $this->welcome_page_id ] != "") and ($page = get_page(sanitize_text_field($_POST[ $this->welcome_page_id ])))){

$welcome_page_id_add = sanitize_text_field($_POST[ $this->welcome_page_id ]);

if (update_option( $this->welcome_page_id, $welcome_page_id_add )){

$welcome_page_id_option = get_option($this->welcome_page_id);


?>
<div class="updated"><p><strong><?php _e('Welcome Page ID saved', $this->namespace ); ?></strong></p></div>
<?php

}

}



if (isset($_POST[$this->use_email_field_name]) and (($_POST[$this->use_email_field_name] == "0") || ($_POST[$this->use_email_field_name] == "1"))){
$use_email_field_name_add = $_POST[ $this->use_email_field_name ];


if (update_option( $this->use_email_field_name, $use_email_field_name_add )){

?>
<div class="updated"><p><strong><?php _e('Use email option updated', $this->namespace ); ?></strong></p></div>
<?php

} 
	    
	    
	}
	
	
	
	
if ($_POST[ $this->welcome_notification.'-subject'] != ""){
    
    $welcome_notification_add['subject'] = sanitize_text_field($_POST[ $this->welcome_notification.'-subject']);
    
}

if ($_POST[ $this->welcome_notification.'-message'] != ""){
    
    $welcome_notification_add['message'] = stripslashes(wp_filter_post_kses(addslashes($_POST[ $this->welcome_notification.'-message'])));
    
}

if (update_option( $this->welcome_notification, $welcome_notification_add, false )){





?>
<div class="updated"><p><strong><?php _e('Welcome Notification updated', $this->namespace ); ?></strong></p></div>
<?php

    } 


	
	}
	

$welcome_notification_option = get_option($this->welcome_notification);	
	
	    // Now display the settings editing screen

include ('partials/option-settings.php');
	
	
}

public function filter_register_url( $register_url ){

if ($this->options[$this->registration_page_id] and ( !is_user_logged_in() )){

$bits = $this->uri_to_array($register_url);

if ($bits['redirect_to']){

$register_url = add_query_arg( "redirect_to", urlencode($bits['redirect_to']), get_permalink(get_option($this->registration_page_id)) );

} else {

$register_url = get_permalink(get_option($this->registration_page_id));

}


}

return $register_url;

}


// add a settings link next to deactive / edit
public function add_settings_link( $links, $file ) {

	if( $file == $this->filename ){
		$links[] = '<a href="'. admin_url( 'options-general.php?page=' ).$this->filename.'">Settings</a>';
	}
	return $links;
}


    
      /**
     * Gets an instance of our plugin.
     *
     * using the singleton pattern
     */
    public static function get_instance(){
        if (null === self::$instance) {
            self::$instance = new self();
        }
 
        return self::$instance;
    }


public function __construct() {
    
    $this->filename = plugin_basename( __FILE__ );
    $this->options = get_option($this->opt_name);
    
    //register the shortcode
    add_action( 'init', array($this,"register_shortcodes"));
    
    //provide options for the plugin
    add_action('admin_menu', array($this,"plugin_menu"));
    
    
    //change the native register url
    add_filter( 'register_url', array($this,"filter_register_url"), 1000, 1);
    
    
    //add a link to settings from the plugins screen
    add_filter('plugin_action_links', array($this,"add_settings_link"), 10, 2);
    
    
    
}
    
    
}

$lh_registration_page_instance = LH_Registration_page_plugin::get_instance();

}
	
?>
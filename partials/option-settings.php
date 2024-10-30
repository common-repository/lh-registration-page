<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
<form name="lh_registration_page-backend_form" method="post" action="">
<?php wp_nonce_field( $this->namespace."-backend_nonce", $this->namespace."-backend_nonce", false ); ?>
<table class="form-table">
<tr valign="top">
<th scope="row"><label for="<?php echo $this->registration_page_id; ?>"><?php _e("Registration Page ID;", $this->namespace ); ?></label></th>
<td><input type="number" name="<?php echo $this->registration_page_id; ?>" id="<?php echo $this->registration_page_id; ?>" value="<?php echo get_option($this->registration_page_id); ?>" size="10" /><a href="<?php echo get_permalink(get_option($this->registration_page_id)); ?>">Link</a></td>
</tr>
<tr valign="top">
<th scope="row"><label for="<?php echo $this->welcome_page_id; ?>"><?php _e("Welcome Page ID;", $this->namespace ); ?></label></th>
<td><input type="number" name="<?php echo $this->welcome_page_id; ?>" id="<?php echo $this->welcome_page_id; ?>" value="<?php  echo get_option($this->welcome_page_id); ?>" size="10" /><a href="<?php echo get_permalink(get_option($this->welcome_page_id)); ?>">Link</a></td>
</tr>
<tr valign="top">
<th scope="row"><label for="<?php echo $this->use_email_field_name; ?>"><?php _e("Use email addresses:", $this->namespace ); ?></label></th>
<td><select name="<?php echo $this->use_email_field_name; ?>" id="<?php echo $this->use_email_field_name; ?>">
<option value="0" <?php if (get_option($this->use_email_field_name) == 0){ echo 'selected="selected"'; } ?> >No</option>
<option value="1" <?php if (get_option($this->use_email_field_name) == 1){ echo 'selected="selected"'; } ?> >Yes</option>
</select> - <?php  _e("Set this to yes if you want too use email addresses instead of usernames to log in.", $this->namespace );  ?></td>
</tr>

<tr valign="top">
<th scope="row"><label for="<?php echo $this->welcome_notification; ?>-subject"><?php _e("Notification Subject;", $this->namespace ); ?></label></th>
<td><input type="text" name="<?php echo $this->welcome_notification; ?>-subject" id="<?php echo $this->welcome_notification; ?>-subject" value="<?php echo $welcome_notification_option['subject']; ?>" size="30" /></td>
</tr>

<tr valign="top">
<th scope="row"><label for="<?php echo $this->welcome_notification; ?>-message"><?php _e('Notification Message: ', $this->namespace); ?></label></th>
<td><?php $settings = array( 'media_buttons' => true, 'textarea_rows' => 10);
 wp_editor( $welcome_notification_option['message'], $this->welcome_notification.'-message', $settings); ?>
  <p>Available placeholders: %first_name% %last_name%, %bloginfo_name%, and %user_email% </p>
</td>
</tr>




</table>
<?php submit_button( 'Save Changes' ); ?>
</form>
<?php
/* Copyright 2012 Unified Logging
*
*	This file is part of Unified Logging
*
*   Unified Logging is a service which collects data from your internet 
*	connected application.  This plugin enables information to be sent 
*	to Unified Logging using your credentials retrieve from the profile
*	page on Unified Logging.  Your data is sent over ssl and the secret
*	key is used to create a hash to make sure the data is not tampered
*	with.
*
*  	 This program is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program; if not, write to the Free Software
*    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*
*/ 

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	if( isset($_POST['info_update']) ) {
		$new_options = $_POST['unifiedlogging'];
		
		$bool_opts = array( 'UL_E_ERROR', 'UL_E_WARNING', 'UL_E_PARSE', 'UL_E_NOTICE', 'UL_E_CORE_ERROR', 'UL_E_CORE_WARNING', 'UL_E_COMPILE_ERROR', 'UL_E_COMPILE_WARNING', 'UL_E_USER_ERROR', 'UL_E_USER_WARNING', 'UL_E_USER_NOTICE', 'UL_E_STRICT', 'UL_E_RECOVERABLE_ERROR', 'UL_E_DEPRECATED', 'UL_E_USER_DEPRECATED' );
		foreach($bool_opts as $key) {
			if ( array_key_exists( $key, $new_options ) )
			{
				$new_options[$key] = $new_options[$key] ? true : false;
			}
			else
			{
				$new_options[$key] = false;
			}
		}
		
		//Update the UL_LEVEL with these new options
		$ul_updated_level = unifiedlogging::get_updated_level( $new_options );
		$new_options['UL_LEVEL'] = $ul_updated_level;
		//Make sure all options are set
		$new_options['UL_ACTIVE'] = true;
		
		update_option( 'plugin_unifieidlogging_settings', $new_options);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Settings saved.') . '</strong></p></div>';
	}
?>
<div class=wrap>
	<form method="post">
		<h2>Unified Logging Options</h2>
        <p>The submission url, access key and secret key can be retrieved from your <a href="https://portal.unifiedlogging.com/profile/" target="_blank">Unified Logging profile</a>.</p>
        <p>If you do not have a Unified Logging account <a href="https://portal.unifiedlogging.com/signup/" target="_blank">Go Sign Up and Get Collecting!</a></p>
		<fieldset name="Unified Logging Options">
			<ul style="list-style-type: none;">
				<li>
					<label for="UL_SUBMISSIONURL"><strong>Submission Url</strong></label><br/><input type="textbox" name="unifiedlogging[UL_SUBMISSIONURL]" id="UL_SUBMISSIONURL" value="<?php echo esc_html( unifiedlogging::get_submission_url() ); ?>" size="100" />
				</li>
				<li>
					<label for="UL_ACCESSKEY"><strong>Access Key</strong></label><br/><input type="textbox" name="unifiedlogging[UL_ACCESSKEY]" id="UL_ACCESSKEY" value="<?php echo esc_html( unifiedlogging::get_access_key() ); ?>" size="100" maxlength="30" />
				</li>
				<li>
					<label for="UL_SECRETKEY"><strong>Secret Key</strong></label><br/><input type="textbox" name="unifiedlogging[UL_SECRETKEY]" id="UL_SECRETKEY" value="<?php echo esc_html( unifiedlogging::get_secret_key() ); ?>" size="100" maxlength="50" />
				</li>
			</ul>
            <label><strong>Exclude</strong> these logging levels (the default is E_NOTICE and E_DEPRECATED PHP 5.3+, E_NOTICE and E_STRICT earlier). Having everything logged will slow down your site which is why E_NOTICE and E_DEPRECATED are excluded by default because many themes and plugins have a lot of these message log types being logged.</label>
			<ul style="list-style-type: none;">
                <li style="display: inline; list-style-type: none; padding-right: 20px; padding-top: 5px;">
					<input type="checkbox" name="unifiedlogging[UL_E_ERROR]" <?php if( unifiedlogging::get_error() ) echo 'checked'; ?> id="UL_E_ERROR" /><label style="padding-left: 2px" for="unifiedlogging[UL_E_ERROR]">E_ERROR</label>
				</li>
                <li style="display: inline; list-style-type: none; padding-right: 20px; padding-top: 5px;">
					<input type="checkbox" name="unifiedlogging[UL_E_WARNING]" <?php if( unifiedlogging::get_warning() ) echo 'checked'; ?> id="UL_E_WARNING" /><label style="padding-left: 2px" for="unifiedlogging[UL_E_WARNING]">E_WARNING</label>
				</li>
                <li style="display: inline; list-style-type: none; padding-right: 20px; padding-top: 5px;">
					<input type="checkbox" name="unifiedlogging[UL_E_PARSE]" <?php if( unifiedlogging::get_parse() ) echo 'checked'; ?> id="UL_E_PARSE" /><label style="padding-left: 2px" for="unifiedlogging[UL_E_PARSE]">E_PARSE</label>
				</li>
                <li style="display: inline; list-style-type: none; padding-right: 20px; padding-top: 5px;">
					<input type="checkbox" name="unifiedlogging[UL_E_NOTICE]" <?php if( unifiedlogging::get_notice() ) echo 'checked'; ?> id="UL_E_NOTICE" /><label style="padding-left: 2px" for="unifiedlogging[UL_E_NOTICE]">E_NOTICE</label>
				</li>
                 <li style="display: inline; list-style-type: none; padding-right: 20px; padding-top: 5px;">
					<input type="checkbox" name="unifiedlogging[UL_E_CORE_ERROR]" <?php if( unifiedlogging::get_core_error() ) echo 'checked'; ?> id="UL_E_CORE_ERROR" /><label style="padding-left: 2px" for="unifiedlogging[UL_E_CORE_ERROR]">E_CORE_ERROR</label>
				</li>
           </ul>
           <ul style="list-style-type: none;">
                <li style="display: inline; list-style-type: none; padding-right: 20px; padding-top: 5px;">
					<input type="checkbox" name="unifiedlogging[UL_E_CORE_WARNING]" <?php if( unifiedlogging::get_core_warning() ) echo 'checked'; ?> id="UL_E_CORE_WARNING" /><label style="padding-left: 2px" for="unifiedlogging[UL_E_CORE_WARNING]">E_CORE_WARNING</label>
				</li>
                <li style="display: inline; list-style-type: none; padding-right: 20px; padding-top: 5px;">
					<input type="checkbox" name="unifiedlogging[UL_E_COMPILE_ERROR]" <?php if( unifiedlogging::get_compile_error() ) echo 'checked'; ?> id="UL_E_COMPILE_ERROR" /><label style="padding-left: 2px" for="unifiedlogging[UL_E_COMPILE_ERROR]">E_COMPILE_ERROR</label>
				</li>
                <li style="display: inline; list-style-type: none; padding-right: 20px; padding-top: 5px;">
					<input type="checkbox" name="unifiedlogging[UL_E_COMPILE_WARNING]" <?php if( unifiedlogging::get_compile_warning() ) echo 'checked'; ?> id="UL_E_COMPILE_WARNING" /><label style="padding-left: 2px" for="unifiedlogging[UL_E_COMPILE_WARNING]">E_COMPILE_WARNING</label>
				</li>
            </ul>
            <ul style="list-style-type: none;">
                <li style="display: inline; list-style-type: none; padding-right: 20px; padding-top: 5px;">
					<input type="checkbox" name="unifiedlogging[UL_E_USER_ERROR]" <?php if( unifiedlogging::get_user_error() ) echo 'checked'; ?> id="UL_E_USER_ERROR" /><label style="padding-left: 2px" style="padding-left: 2px" for="unifiedlogging[UL_E_USER_ERROR]">E_USER_ERROR</label>
				</li>
                <li style="display: inline; list-style-type: none; padding-right: 20px; padding-top: 5px;">
					<input type="checkbox" name="unifiedlogging[UL_E_USER_WARNING]" <?php if( unifiedlogging::get_user_warning() ) echo 'checked'; ?> id="UL_E_USER_WARNING" /><label style="padding-left: 2px" style="padding-left: 2px" for="unifiedlogging[UL_E_USER_WARNING]">E_USER_WARNING</label>
				</li>
                <li style="display: inline; list-style-type: none; padding-right: 20px; padding-top: 5px;">
					<input type="checkbox" name="unifiedlogging[UL_E_USER_NOTICE]" <?php if( unifiedlogging::get_user_notice() ) echo 'checked'; ?> id="UL_E_USER_NOTICE" /><label style="padding-left: 2px" for="unifiedlogging[UL_E_USER_NOTICE]">E_USER_NOTICE</label>
				</li>
                <li style="display: inline; list-style-type: none; padding-right: 20px; padding-top: 5px;">
					<input type="checkbox" name="unifiedlogging[UL_E_STRICT]" <?php if( unifiedlogging::get_strict() ) echo 'checked'; ?> id="UL_E_STRICT" /><label style="padding-left: 2px" for="unifiedlogging[UL_E_STRICT]">E_STRICT</label>
				</li>
            </ul>
            <ul style="list-style-type: none;">
                <li style="display: inline; list-style-type: none; padding-right: 20px; padding-top: 5px;">
					<input type="checkbox" name="unifiedlogging[UL_E_RECOVERABLE_ERROR]" <?php if( unifiedlogging::get_recoverable_error() ) echo 'checked'; ?> id="UL_E_RECOVERABLE_ERROR" /><label style="padding-left: 2px" for="unifiedlogging[UL_E_RECOVERABLE_ERROR]">E_RECOVERABLE_ERROR</label>
				</li>
                <li style="display: inline; list-style-type: none; padding-right: 20px; padding-top: 5px;">
					<input type="checkbox" name="unifiedlogging[UL_E_DEPRECATED]" <?php if( unifiedlogging::get_deprecated() ) echo 'checked'; ?> id="UL_E_DEPRECATED" /><label style="padding-left: 2px" for="unifiedlogging[UL_E_DEPRECATED]">E_DEPRECATED</label>
				</li>
                <li style="display: inline; list-style-type: none; padding-right: 20px; padding-top: 5px;">
					<input type="checkbox" name="unifiedlogging[UL_E_USER_DEPRECATED]" <?php if( unifiedlogging::get_user_deprecated() ) echo 'checked'; ?> id="UL_E_USER_DEPRECATED" /><label style="padding-left: 2px" for="unifiedlogging[UL_E_USER_DEPRECATED]">E_USER_DEPRECATED</label>
				</li>
			</ul>
            <label><strong>Current Level: </strong><?php echo unifiedlogging::get_level(); ?></label>
		</fieldset>
		<div class="submit">
			<input type="submit" name="info_update" value="<?php _e('Update options', 'Unified Logging'); ?> &raquo;" />
		</div>
	</form>
</div>
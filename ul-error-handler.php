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
define( 'UL_MAX_LENGTH', 49152 );
$ul_level = unifiedlogging::get_level();
unifiedlogging::ul_debug( 'logging level: ' . $ul_level );
set_error_handler( 'ul_error_handler', $ul_level );
register_shutdown_function( 'ul_fatal_error_shutdown_handler' );

function ul_error_handler($code, $message, $file, $line) 
{		
	$fullMessage = "Severity: ".ul_friendly_error_type( $code ).
					"\r\nUrl: ".ul_get_url().
					"\r\nRemoteAddress: ".ul_get_ip().
					"\r\nMessage: ".$message.
					"\r\nFile: ".$file.
					"\r\nLine: ".$line.
					"\r\nUTC Time: ".gmdate("M d Y H:i:s");
					
	ul_log_message( $fullMessage );
}

function ul_fatal_error_shutdown_handler()
{
	$last_error = error_get_last();
  	if ( $last_error['type'] === E_ERROR ) {
    	// fatal error
    	ul_error_handler( E_ERROR, $last_error['message'], $last_error['file'], $last_error['line'] );
	}
}

/*
*ul_log_message: This function sends the messgae to unified logging as well as logs it in the error log
*You may call this anywhere you like as long as your access keys have been setup
*@param: $message - the message to be logged
*/
function ul_log_message( $message )
{	
	if ( strlen( $message ) > 0 )
	{
		error_log( $message );
		
		$url = unifiedlogging::get_submission_url();
		$access_key = unifiedlogging::get_access_key();
		$secret_key = unifiedlogging::get_secret_key();
		
		if ( strlen( $url ) > 0 && strlen( $access_key ) > 0 && strlen( $secret_key ) > 0 )
		{
			$ch = curl_init( $url );
			try
			{
				if ( strlen($message) > UL_MAX_LENGTH ) {
					$message = substr( $message, 0, UL_MAX_LENGTH - 1 );
				}
			
				$fields = array( 'Content' => $message );
				$json_data = json_encode( $fields );
				$validation_string = $message."|".$secret_key;
				$computed_hash = sha1( $validation_string , false );
						
				
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
				curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 5 ); 
				curl_setopt( $ch, CURLOPT_TIMEOUT, 10 ); //timeout in seconds
				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );
					curl_setopt( $ch, CURLOPT_HTTPHEADER,
						array(
							'Content-Type: application/json', 
							'Content-Length: '.strlen( $json_data ),
							'Authorization: '.$access_key,
							'MD5: '.$computed_hash ) );
				curl_setopt( $ch, CURLOPT_POST, 1 );  
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data );
		
						
				$result = curl_exec( $ch );
				 
				$returnCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
				
				if ($returnCode != 201 || $returnCode != '201')
				{
					$errors = curl_error( $ch );
					error_log( 'UnifiedLogging Submission Failed with Http Code '.$returnCode.'\r\nErrors:\r\n'.$errors );
				}
				
				curl_close( $ch );
				
				return $returnCode;
			}
			catch( Exception $e )
			{
				if (is_null( $ch ) == false){
					curl_close( $ch );
				}
				
				error_log( $e->getMessage() );
			}
		}
	}
	
}

function ul_friendly_error_type($type)
{
    switch($type)
    {
        case E_ERROR: // 1 //
            return 'E_ERROR';
        case E_WARNING: // 2 //
            return 'E_WARNING';
        case E_PARSE: // 4 //
            return 'E_PARSE';
        case E_NOTICE: // 8 //
            return 'E_NOTICE';
        case E_CORE_ERROR: // 16 //
            return 'E_CORE_ERROR';
        case E_CORE_WARNING: // 32 //
            return 'E_CORE_WARNING';
        case E_CORE_ERROR: // 64 //
            return 'E_COMPILE_ERROR';
        case E_CORE_WARNING: // 128 //
            return 'E_COMPILE_WARNING';
        case E_USER_ERROR: // 256 //
            return 'E_USER_ERROR';
        case E_USER_WARNING: // 512 //
            return 'E_USER_WARNING';
        case E_USER_NOTICE: // 1024 //
            return 'E_USER_NOTICE';
        case E_STRICT: // 2048 //
            return 'E_STRICT';
        case E_RECOVERABLE_ERROR: // 4096 //
            return 'E_RECOVERABLE_ERROR';
        case E_DEPRECATED: // 8192 //
            return 'E_DEPRECATED';
        case E_USER_DEPRECATED: // 16384 //
            return 'E_USER_DEPRECATED';
    }
	
    return "UNKNOWN";
} 

function ul_get_url()
{
	$url = "http" . (($_SERVER['SERVER_PORT']==443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	
	return $url;
}

function ul_get_ip() 
{ 
	$ip; 
	if (getenv("HTTP_CLIENT_IP")) 
		$ip = getenv("HTTP_CLIENT_IP"); 
	else if(getenv("HTTP_X_FORWARDED_FOR")) 
		$ip = getenv("HTTP_X_FORWARDED_FOR"); 
	else if(getenv("REMOTE_ADDR")) 
		$ip = getenv("REMOTE_ADDR"); 
	else 
		$ip = "UNKNOWN";
		
	return $ip; 
}

?>

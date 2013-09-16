=== Unified Logging ===
Contributors: unifiedlogging
Donate link: https://portal.unifiedlogging.com/signup/
Tags: log, admin, error, debug
Requires at least: 3.4
Tested up to: 3.6.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Unified Logging WordPress plugin enables php logging information to be sent to Unified Logging

== Description ==

Unified Logging is Logging as a Service (LaaS).  This plugin taps into php logging and sends the logging information to Unified Logging (http://www.unifiedlogging.com) so you can be notified if something goes wrong and then can review the messages.

To signup for a free account go to https://portal.unifiedlogging.com/signup/

After signing up visit your profile page (https://portal.unifiedlogging.com/profile/) and retrieve your submission url, access key and secret key then input these into the "Settings" page after the plugin is activated.

More info can be found at: http://blog.unifiedlogging.com/getting-started-with-unified-logging-wordpress-plugin/

Your data is sent over ssl and the secret key is used to create a hash to make sure the data is not tampered with.

NOTE: this plugin may work with earlier versions but has not been tested.

== Installation ==
1. Unpackage contents to wp-content/plugins/unifiedlogging so that unifiedlogging.php, ul-error-handler.php and unifiedlogging-ui.php are in a unifiedlogging folder under plugins
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Tune the settings at Settings->Unified Logging.

Alternatively you could upload the zip file via the add new plugin ui within wordpress.

Refer to the post:
http://blog.unifiedlogging.com/getting-started-with-unified-logging-wordpress-plugin/

For more information
=== Send email attachment to dropbox ===

Contributors: asaquzzaman
Tags: email to dropbox, email to wordpress, email to wp, email wp post, email wordpress post
Requires at least: 4.1
Tested up to: 4.1
Stable tag: trunk
License: GPLv2 or later

Send email attachment to dropbox

== Description ==
    Fetch your email attachment and send it to dropbox. You can make it also WP-post

== Installation ==
	1. Unzip and upload the hrm directory to /wp-content/plugins/
    2. Activate the plugin through the Plugins menu in WordPress

Browser Compatibility
    1. Google Chrome
    2. Firefox.

= Usage =
	Complete the settings form. All fields are required. Some requrements are

	enable from your php.in
	extension=php_openssl.dll
	extension=php_fileinfo.dll
	
	for gmail.
	Mail Server: {imap.gmail.com:993/imap/ssl}INBOX
	[IMAP Settings](https://support.google.com/mail/troubleshooter/1668960)
	[Less secure for apps](https://www.google.com/settings/security/lesssecureapps)	

	yahoo
	Mail Server: {imap.mail.yahoo.com:993/imap/ssl}INBOX

	hotmail
	Mail Server: {imap-mail.outlook.com:993/imap/ssl}INBOX

	others

	// To connect to an IMAP server running on port 143 on the local machine.
	Mail Server: {localhost:143}INBOX

	// To connect to a POP3 server on port 110 on the local server.
	Mail Server: {localhost:110/pop3}INBOX

	// To connect to an SSL IMAP or POP3 server, add /ssl after the protocol
	Mail Server: {localhost:993/imap/ssl}INBOX

	// To connect to an SSL IMAP or POP3 server with a self-signed certificate, add /ssl/novalidate-cert after the protocol specification
	Mail Server: {localhost:995/pop3/ssl/novalidate-cert}

	// To connect to an NNTP server on port 119 on the local server
	Mail Server: {localhost:119/nntp}comp.test

	// To connect to a remote server replace "localhost" with the name or the
	// IP address of the server you want to connect to.
	

== Screenshots ==


== Changelog ==


== Frequently Asked Questions ==

You can contact with me joy.mishu@gmail.com with this email address. You can ask me any kinds of question about this plugin.

== Upgrade Notice ==

Nothing to say

Thanks for beign with me.




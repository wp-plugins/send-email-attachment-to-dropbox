<?php
if (version_compare( PHP_VERSION, '5.4.7', '<') ) {
	echo '<div class="error"><p>';
    _e( 'Please update your PHP version. You are now using PHP ', 'mdrop' ); echo PHP_VERSION; _e( ' version ', 'mdrop' );
    _e( 'But required version 5.4.7', 'mdrop' );
    echo '</p></div>';
    return;
}

if ( ! extension_loaded('openssl')) {
	echo '<div class="error"><p>';
	_e( '<strong>Please enable openssl</strong><br>', 'mdrop' );
	_e( 'Open <em style="background: #eee; padding: 0px 5px;">php.ini</em> uncomment the following: <em style="background: #eee; padding: 0px 5px;">extension=php_openssl.dll</em>', 'mdrop' );
	echo '</p></div>';
    return;
}

if ( ! extension_loaded('openssl')) {
	echo '<div class="error"><p>';
	_e( '<strong>Please enable fileinfo</strong><br>', 'mdrop' );
	_e( 'Open <em style="background: #eee;">php.ini</em> uncomment the following:
		<em style="background: #eee;">extension=php_fileinfo.dll</em>', 'mdrop' );
	echo '</p></div>';
    return;
}

$header_path = dirname(__FILE__) . '/header.php';
$header_path = apply_filters( 'mdrop_header_path', $header_path, 'settings' );

if ( file_exists( $header_path ) ) {
	require_once $header_path;
}
$user_id     = get_current_user_id();
$mail_server = mdrop_get_server( $user_id );
$email       = mdrop_get_email( $user_id );
$password    = mdrop_get_password( $user_id );
$drop_token  = mdrop_get_token( $user_id );

?>

<div class="postbox">
	<h3 class="mdrop-h3-title"><?php _e( 'Settings', 'mdrop' ); ?></h3>
	<div class="mdrop-settings-content">
		<div id="mdrop-read-me-wrap" style="display: none; margin-bottom: 35px;">
			
			<p>
				<strong><?php _e( 'For Gmail', 'mdrop' ); ?></strong><br>
				<?php _e( 'Mail Server ', 'mdrop' ); ?> 
				<em style="background: #eee; padding: 0px 5px;">{imap.gmail.com:993/imap/ssl}INBOX</em><br>
				<?php _e( 'Active IMAP from your gmail account settings', 'mdrop' ); ?>
				<em style="background: #eee; padding: 0px 5px;">
					<a href="https://support.google.com/mail/troubleshooter/1668960" target="_blank"><?php _e( 'More details', 'mdrop' );?></a>
				</em>
				<?php _e( 'and', 'mdrop' ); ?>
				<em style="background: #eee; padding: 0px 5px;">
					<a href="https://www.google.com/settings/security/lesssecureapps" target="_blank"><?php _e( 'Less your privacy for app', 'mdrop' );?></a>
				</em>
			</p>

			<p>
				<strong><?php _e( 'For Yahoo', 'mdrop' ); ?></strong><br>
				<?php _e( 'Mail Server ', 'mdrop' ); ?> 
				<em style="background: #eee; padding: 0px 5px;">
					{imap.mail.yahoo.com:993/imap/ssl}INBOX
				</em>
			</p>

			<p>
				<strong><?php _e( 'For Hotmail', 'mdrop' ); ?></strong><br>
				<?php _e( 'Mail Server ', 'mdrop' ); ?> 
				<em style="background: #eee; padding: 0px 5px;">
					{imap-mail.outlook.com:993/imap/ssl}INBOX
				</em>
			</p>

			<p>
				<strong><?php _e( 'To connect to an IMAP server running on port 143 on the local machine', 'mdrop' ); ?></strong><br>
				<?php _e( 'Mail Server ', 'mdrop' ); ?> 
				<em style="background: #eee; padding: 0px 5px;">
					{localhost:143}INBOX
				</em>
			</p>

			<p>

				<strong><?php _e( 'To connect to a POP3 server on port 110 on the local server, use', 'mdrop' ); ?></strong><br>
				<?php _e( 'Mail Server ', 'mdrop' ); ?> 
				<em style="background: #eee; padding: 0px 5px;">
					{localhost:110/pop3}INBOX
				</em>
			</p>

			<p>
				<strong><?php _e( 'To connect to an SSL IMAP or POP3 server, add /ssl after the protocol', 'mdrop' ); ?></strong><br>
				<?php _e( 'Mail Server ', 'mdrop' ); ?> 
				<em style="background: #eee; padding: 0px 5px;">
					{localhost:993/imap/ssl}INBOX
				</em>
			</p>

			<p>
				<strong><?php _e( 'To connect to an SSL IMAP or POP3 server with a self-signed certificate, add /ssl/novalidate-cert after the protocol specification', 'mdrop' ); ?></strong><br>
				<?php _e( 'Mail Server ', 'mdrop' ); ?> 
				<em style="background: #eee; padding: 0px 5px;">
					{localhost:995/pop3/ssl/novalidate-cert}
				</em>
			</p>

			<p>
				<strong><?php _e( 'To connect to an NNTP server on port 119 on the local server, use', 'mdrop' ); ?></strong>
				<?php _e( 'Mail Server ', 'mdrop' ); ?> 
				<em style="background: #eee; padding: 0px 5px;">
					{localhost:119/nntp}comp.test
				</em>
			</p>

			<p>
				<strong><?php _e( ' To connect to a remote server replace "localhost" with the name or the IP address of the server you want to connect to.', 'mdrop' ); ?></strong>
			</p>
		</div>

		<form action="" method="post">
			<div class="mdrop-field-wrap">
				<label class="mdrop-label"><?php _e( 'Mail Server', 'mdrop' ); ?></label>
				<input class="mdrop-input" type="text" name="mail_server" value="<?php echo $mail_server; ?>" placeholder="Ex. {imap.gmail.com:993/imap/ssl}INBOX">
				<div class="mdrop-clear"></div>
			</div>

			<div class="mdrop-field-wrap">
				<label class="mdrop-label"><?php _e( 'E-mail', 'mdrop' ); ?></label>
				<input class="mdrop-input" type="email" name="email" value="<?php echo $email; ?>" >
				<div class="mdrop-clear"></div>
			</div>

			<div class="mdrop-field-wrap">
				<label class="mdrop-label"><?php _e( 'Password', 'mdrop' ); ?></label>
				<input class="mdrop-input" type="password" name="password" value="<?php echo $password; ?>">
				<div class="mdrop-clear"></div>
			</div>

			<div class="mdrop-field-wrap">
				<label class="mdrop-label"><?php _e( 'Dropbox Access Token', 'mdrop' ); ?></label>
				<input class="mdrop-input" type="text" name="drop_token" value="<?php echo $drop_token; ?>">
				<div class="mdrop-clear"></div>
			</div>

			<input type="submit" class="button button-primary" name="mdrop_settings" value="<?php _e( 'Save Settings', 'mdrop' ); ?>">
			<a href="#" class="button button-secondary mdrop-read-me"><?php _e( 'Please Readme', 'mdrop' ); ?></a>
		</form>

	</div>

</div>

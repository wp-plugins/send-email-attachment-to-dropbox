<?php
$header_path = dirname(__FILE__) . '/header.php';
$header_path = apply_filters( 'mdrop_header_path', $header_path, 'list' );

if ( file_exists( $header_path ) ) {
	require_once $header_path;
}

?>

<div id="mdrop-list">
<?php

//$drop = MDROP_Dropbox::instance();
//$drop->upload_file_dropbox( 56, 57 );

$connect = mdrop_connect_server();

if ( $connect ) {
	$number_msg = $connect->check_num_msg();
}
$qery_message = mdrop_get_query_message(10);

$messages = $qery_message->posts;
$total_msg = $qery_message->found_posts;
if ( $connect ) {
	?>
	<div class="postbox">
		<h3 class="mdrop-header-wrap"><?php _e( 'Total E-mail ', 'mdrop' ); echo $number_msg; ?>
			<span class="mdrop-fetch-msg" style="display: none;">
				<span class="mdrop-text-fetch"><?php _e('E-mail Attachment Fetch Start From '); ?></span>
				<span class="mdrop-fetch-start"></span>

				<span class="mdrop-text-fetch"><?php _e( 'Now E-mail Attachment Fetching Number ', 'mdrop'); ?></span>
				<span class="mdrop-fetch-end"></span><span class="mdrop-loading mdrop-spinner"></span>
			</span>
		</h3>
		<div class="mdrop-content">
			<form id="mdrop-mail-fetch-form">
				<input type="number" min="1" max="<?php echo $number_msg; ?>" required style="width: 250px; margin-right: 10px;" placeholder="<?php _e( 'Fetch E-mail Attachment From', 'mdrop' ); ?>" name="start">
				<input type="number" min="1" max="<?php echo $number_msg; ?>" required style="width: 250px; margin-right: 10px;" placeholder="<?php _e( 'Fetch E-mail Attachment To', 'mdrop' ); ?>" name="end">
				<input type="hidden" value="<?php echo $number_msg; ?>" name="total">
				<input type="submit" class="button button-primary" value="<?php _e( 'Fetch', 'mdrop' ); ?>" name="mdrop_fetch">
			</form>
		</div>

	</div>
	<?php
} else {
	?>
	<div class="postbox">
		<h3><?php _e( 'Connection Failed!', 'mdrop' ); ?></h3>
	</div>
	<?php
}
?>
<form id="mdrop-form-action">
	<div class="mdrop-action-wrap">
		<select class="mdrop-action-dropdown">
			<option value="-1"><?php _e( '-Select-', 'mdrop' ); ?></option>
			<option value="post"><?php _e( 'Post', 'mdrop' ); ?></option>
			<option value="dropbox"><?php _e( 'Move to Dropbox', 'mdrop' ); ?></option>
			<option value="delete"><?php _e( 'Delete', 'mdrop' ); ?></option>
		</select>
		<input type="submit" value="<?php _e( 'Apply', 'mdrop' ); ?>" class="button button-primarty mdrop-action-button">
		<span class="mdrop-multi-action-loading"></span>
	</div>
<table class="widefat">
	<thead>
		<th><input class="mdrop-all-checked" type="checkbox"></th>
		<th><?php _e( 'Title', 'mdrop' ); ?></th>

		<th><?php _e( 'Delivery Date', 'mdrop' ); ?></th>
		<th><?php _e( 'Attachment', 'mdrop' ); ?></th>

	</thead>

	<tbody>
		<?php
		foreach ( $messages as $key => $message ) {
			
			$get_attachemt_ids = get_post_meta( $message->ID, '_wp_attachment_id' );
			
			?>
			<tr class="mdrop-tr-wrap">
				<td class="mdrop-list-first-td">
					<input class="mdrop-single-checked" type="checkbox" name="message_id[]" value="<?php echo $message->ID; ?>">
				</td>
				<td class="mdrop-hover-action">
					<?php echo $message->post_title; ?>
					<span class="mdrop-single-action-loading"></span>
					<div class="mdrop-title-action">
						<a href="#" class="mdrop-send-to-post"  data-id="<?php echo $message->ID; ?>"><?php _e( 'Post', 'mdrop' ); ?></a>
						<a href="#" class="mdrop-send-to-drop"  data-id="<?php echo $message->ID; ?>"><?php _e( 'Move to Dropbox', 'mdrop' ); ?></a>
						<a href="#" class="mdrop-delete-single" data-id="<?php echo $message->ID; ?>"><?php _e( 'Delete', 'mdrop' ); ?></a>
					</div>
				</td>

				<td><?php echo get_post_meta( $message->ID, '_date', true ); ?></td>
				<td><?php 
					foreach ( $get_attachemt_ids as $snum => $attachemt_id ) {
						$thumbs =  mdrop_get_file( $attachemt_id );

						?>

							<a href="<?php echo $thumbs['url']; ?>" title="<?php echo $thumbs['name']; ?>">
								<img src="<?php echo $thumbs['thumb']; ?>" class="mdrop-file">
							</a>
						<?php
					}

				?></td>
			</tr>
			<?php
			
		}

		?>
	</tbody>
</table>
</form>
<?php
echo mdrop_pagination( $total_msg, 10, mdrop_pagenum() );

?>

</div>
        
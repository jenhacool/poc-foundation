<form action="" id="poc-foundation-campaign-form">
	<div style="overflow-x: auto">
		<table class="widefat striped" id="poc-foundation-lgs-table">
			<thead>
			<th><strong><?php echo __( 'Campaign Key', 'poc-foundation' ); ?></strong></th>
			<th><strong><?php echo __( 'Domain', 'poc-foundation' ); ?></strong></th>
			<th><strong><?php echo __( 'Register Page', 'poc-foundation' ); ?></strong></th>
			<th><strong><?php echo __( 'Success Page', 'poc-foundation' ); ?></strong></th>
			<th><strong><?php echo __( 'Chatbot Link', 'poc-foundation' ); ?></strong></th>
			<th><strong><?php echo __( 'Fanpage URL', 'poc-foundation' ); ?></strong></th>
			<th><strong><?php echo __( 'Fanpage ID', 'poc-foundation' ); ?></strong></th>
			<th><strong><?php echo __( 'Remove', 'poc-foundation' ); ?></strong></th>
			</thead>
			<tfoot>
			<tr>
				<td colspan="8">
					<button href="" class="button-primary save">Save</button>
					<button href="" class="button-secondary add">Add</button>
					<span class="spinner" style="float: none; margin: 0;"></span>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php foreach( $campaign_data as $index => $data ) : ?>
				<tr>
					<td>
						<input type="text" name="poc_foundation_campaign[<?php echo $index; ?>][campaign_key]" value="<?php echo $data['campaign_key']; ?>">
					</td>
					<td>
						<input type="text" name="poc_foundation_campaign[<?php echo $index; ?>][domain]" value="<?php echo $data['domain']; ?>">
					</td>
					<td>
						<select name="poc_foundation_campaign[<?php echo $index; ?>][redirect_page]" id="">
							<?php foreach ( get_pages() as $page ) : ?>
								<option value="<?php echo $page->ID; ?>" <?php echo ( $data['redirect_page'] == $page->ID ) ? 'selected' : ''; ?>><?php echo $page->post_title; ?></option>
							<?php endforeach; ?>
						</select>
					</td>
					<td>
						<select name="poc_foundation_campaign[<?php echo $index; ?>][success_page]" id="">
							<?php foreach ( get_pages() as $page ) : ?>
								<option value="<?php echo $page->ID; ?>" <?php echo ( $data['success_page'] == $page->ID ) ? 'selected' : ''; ?>><?php echo $page->post_title; ?></option>
							<?php endforeach; ?>
						</select>
					</td>
					<td>
						<input type="text" name="poc_foundation_campaign[<?php echo $index; ?>][chatbot_link]" value="<?php echo $data['chatbot_link']; ?>">
					</td>
					<td>
						<input type="text" name="poc_foundation_campaign[<?php echo $index; ?>][fanpage_url]" value="<?php echo $data['fanpage_url']; ?>">
					</td>
					<td>
						<input type="text" name="poc_foundation_campaign[<?php echo $index; ?>][fanpage_id]" value="<?php echo $data['fanpage_id']; ?>">
					</td>
					<td>
						<button href="" class="button-secondary remove">Remove</button>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</form>
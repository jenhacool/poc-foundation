<div class="wrap">
	<h1><?php echo __( 'License', 'poc-foundation' ); ?></h1>
	<div class="card">
		<?php if ( isset( $license_data['status'] ) && $license_data['status'] === 'Active' ) : ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Status</th>
					<td>
						<?php echo $license_data['status']; ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Registered Name</th>
					<td>
						<?php echo $license_data['registeredname']; ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Company Name</th>
					<td>
						<?php echo $license_data['companyname']; ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Email</th>
					<td>
						<?php echo $license_data['email']; ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Registered Date</th>
					<td>
						<?php echo $license_data['regdate']; ?>
					</td>
				</tr>
			</table>
		<?php else : ?>
			<form action="" id="poc-foundation-license-form">
				<p><?php echo __( 'Enter your license key here to start using this plugin.', 'poc-foundation' ); ?></p>
				<p><input type="text" name="license_key" id="license-key" style="width: 100%" /></p>
				<p><button type="submit" class="button button-primary">Check license</button></p>
			</form>
		<?php endif; ?>
	</div>
</div>
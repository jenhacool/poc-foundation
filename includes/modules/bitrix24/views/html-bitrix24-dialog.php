<div id="poc_foundation_bitrix24" class="hidden">
	<div class="poc_foundation_bitrix24_form">
		<p>
			You're going to send <span class="number_of_lead"></span> leads to Bitrix24
		</p>
		<p class="bitrix24_stage_select">
			<select name="poc_foundation_bitrix24_stage_select" id="poc_foundation_bitrix24_stage_select">
				<?php foreach ( $stages as $id => $stage ) : ?>
					<option value="<?php echo $id; ?>"><?php echo $stage; ?></option>
				<?php endforeach; ?>
			</select>
			<span class="bitrix24_stage_reload dashicons dashicons-update"></span>
		</p>
		<p>
			<button class="button send-button button-primary">Send</button>
		</p>
	</div>
	<div class="poc_foundation_bitrix24_status" style="display: none">
		<p style="color: #dc3232; font-weight: 600">Please do not close popup while running.</p>
		<p style="display: flex; justify-content: space-between">
			<span style="color: #0073aa;">Selected: <span class="queued"></span></span>
			<span style="color: #46b450;">Processed: <span class="processed">0</span></span>
			<span style="color: #dc3232;">Failed: <span class="failed">0</span></span>
		</p>
		<p class="poc_foundation_bitrix24_log" style="height: 100px; overflow-y: auto; background: #f7f7f7; padding: 5px;">
		</p>
		<p>
			<button class="button stop-button">Stop</button>
			<button style="display: none;" class="button button-primary restart-button">Restart</button>
		</p>
	</div>
</div>
<style>
	#poc_foundation_bitrix24 p{
		margin-top: 0;
	}

	#poc_foundation_bitrix24 p:last-child {
		margin-bottom: 0 !important;
	}

	#poc_foundation_bitrix24 .bitrix24_stage_select {
		display: flex;
		justify-content: center;
		align-items: center
	}

	#poc_foundation_bitrix24 .bitrix24_stage_reload {
		margin-left: 10px;
		cursor: pointer
	}
</style>
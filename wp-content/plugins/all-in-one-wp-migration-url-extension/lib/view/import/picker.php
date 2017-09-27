<div id="ai1wmle-import-modal" class="ai1wmle-modal ai1wm-not-visible">
	<div class="ai1wm-modal-action">
		<h2><?php _e( 'Import from URL', AI1WMLE_PLUGIN_NAME ); ?></h2>
		<p>
			<label for="ai1wmle-import-url">
				<?php _e( 'URL address', AI1WMLE_PLUGIN_NAME ); ?>
				<br />
				<input type="text" class="ai1wmle-import-url" id="ai1wmle-import-url" v-on="keyup: select" placeholder="<?php _e( 'Enter URL to WPRESS file', AI1WMLE_PLUGIN_NAME ); ?>" />
			</label>
		</p>
		<p>
			<span id="ai1wmle-download-file" class="ai1wmle-selected-file" v-if="selected_filename" v-animation>
				<i class="ai1wm-icon-file-zip"></i>
				{{selected_filename}}
			</span>
		</p>
		<p>
			<button type="button" class="ai1wm-button-red" id="ai1wmle-import-file-cancel" v-on="click: cancel">
				<i class="ai1wm-icon-notification"></i>
				<?php _e( 'Cancel', AI1WMLE_PLUGIN_NAME ); ?>
			</button>
			<button type="button" class="ai1wm-button-green" id="ai1wmle-import-file" v-if="selected_filename" v-on="click: import">
				<i class="ai1wm-icon-publish"></i>
				<?php _e( 'Import', AI1WMLE_PLUGIN_NAME ); ?>
			</button>
		</p>
	</div>
</div>

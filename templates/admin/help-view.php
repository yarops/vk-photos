<div class="wrap">
	<h2><?php _e( 'Help', 'vkp' ); ?></h2>

	<h3><?php _e( 'More options shortcode', 'vkp' ); ?> [vkalbum]</h3>
	<p>
		<b>template</b> - <?php _e( 'template gallery, the default value is "light" or specified in plugin settings.', 'vkp' ); ?><br>
		<b>count</b> - <?php _e( 'number of photos per load, the default value is 12', 'vkp' ); ?><br>
		<b>preview</b> - <?php _e( 'thumbnail size', 'vkp' ); ?> (src_small, src, src_big, src_xbig, src_xxbig, src_xxxbig)<br>
		<b>photo</b> - <?php _e( 'size photos', 'vkp' ); ?> (src_small, src, src_big, src_xbig, src_xxbig, src_xxxbig)<br>
		<b>cache</b> - <?php _e( 'photos from the cache, or from vk.com', 'vkp' ); ?> (yes,no)<br>
	</p>

	<br><h3><?php _e( 'For example:', 'vkp' ); ?></h3>
	<b>[vkalbum owner='-32878584' id='193472590']</b> - <font color='#cccccc'><?php _e( 'Will be used by the parameters set by default.', 'vkp' ); ?></font><br>
	<b>[vkalbum owner='-32878584' id='193472590' template='fresh']</b> - <font color='#cccccc'><?php _e( 'Gallery template-based', 'vkp' ); ?> 'fresh'</font><br>
	<b>[vkalbum owner='-32878584' id='193472590' count='24']</b> - <font color='#cccccc'><?php _e( 'Output to 24 photos.', 'vkp' ); ?></font><br>
	<b>[vkalbum owner='-32878584' id='193472590' cache='no' template='blocks' preview='src_small' photo='src_big']</b><br>

	<br><h3><?php _e( 'Image sizes', 'vkp' ); ?></h3>
	<b>src_small</b> — <?php _e( 'url photos with maximum size', 'vkp' ); ?> 75x75px;<br>
	<b>src</b> — <?php _e( 'url photos with maximum size', 'vkp' ); ?> 130x130px;<br>
	<b>src_big</b> — <?php _e( 'url photos with maximum size', 'vkp' ); ?> 604x604px;<br>
	<b>src_xbig</b> — <?php _e( 'url photos with maximum size', 'vkp' ); ?> 807x807px;<br>
	<b>src_xxbig</b> — <?php _e( 'url photos with maximum size', 'vkp' ); ?> 1280x1024px;<br>
	<b>src_xxxbig</b> — <?php _e( 'url photos with maximum size', 'vkp' ); ?> 2560x2048px;<br>

	<br><h3>VK application</h3>
	<p><?php _e( "To get 'access_token' you need to create the 'Standalone' type application, set it correctly (attention to the parameters highlighted in red) and add the 'access_token' parameter in the plugin settings.", 'vkp' ); ?></p>
	<img src="<?php echo esc_url( $this->get_plugin_url() . 'images/vkapp.png' ); ?>" alt="VK application settings">
</div>

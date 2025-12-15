<div class="wrap">
	<h2><?php _e( 'Templates', 'vkp' ); ?></h2>
	<?php
	$templates  = $this->get_templates();
	$plugin_url = \VkPhotos\Config::get( 'plugin.url', '' );
	foreach ( $templates as $template_name ) {
		$thumb_url = $plugin_url . 'templates/frontend/' . esc_attr( $template_name ) . '/thumb.jpg';
		?>
		<div style="float:left;margin:10px;">
			<h3><?php echo esc_html( $template_name ); ?></h3>
			<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $template_name ); ?>">
		</div>
		<?php
	}
	?>
</div>

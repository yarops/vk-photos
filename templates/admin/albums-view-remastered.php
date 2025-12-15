<?php
/**
 * Albums view (remastered).
 *
 * Expects $albums_view_data shaped by AlbumsViewData::get_view_data().
 */

if ( ! isset( $albums_view_data ) || ! is_array( $albums_view_data ) ) {
	echo '<div class="wrap"><p>' . esc_html__( 'No data to render.', 'vkp' ) . '</p></div>';
	return;
}

$page_title = $albums_view_data['pageTitle'] ?? __( 'Albums', 'vkp' );
$accounts   = $albums_view_data['accounts'] ?? array();
?>

<div class="wrap">
	<h2><?php echo esc_html( $page_title ); ?></h2>

	<?php if ( empty( $accounts ) ) : ?>
		<p><?php esc_html_e( 'No accounts configured.', 'vkp' ); ?></p>
	<?php else : ?>
		<?php foreach ( $accounts as $account ) : ?>
			<?php
			$display_name = $account['displayName'] ?? '';
			$account_link = $account['link'] ?? '';
			$error        = $account['error'] ?? null;
			$warnings     = $account['warnings'] ?? array();
			$albums       = $account['albums'] ?? array();
			?>

			<?php if ( $error ) : ?>
				<p><strong><?php echo esc_html( $display_name ?: __( 'Account', 'vkp' ) ); ?>:</strong> <span style="color:red;"><?php echo esc_html( $error ); ?></span></p>
				<?php continue; ?>
			<?php endif; ?>

			<h3>
				<?php if ( $account_link ) : ?>
					<a href="<?php echo esc_url( $account_link ); ?>" target="_blank" rel="noreferrer"><?php echo esc_html( $display_name ); ?></a>
				<?php else : ?>
					<?php echo esc_html( $display_name ); ?>
				<?php endif; ?>
			</h3>

			<?php if ( ! empty( $warnings ) ) : ?>
				<ul>
					<?php foreach ( $warnings as $warning ) : ?>
						<li style="color:orange;"><?php echo esc_html( $warning ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( empty( $albums ) ) : ?>
				<p><?php esc_html_e( 'No Albums Found (The user has no albums or they are not accessible with service token)', 'vkp' ); ?></p>
			<?php else : ?>
				<table class="wp-list-table widefat">
					<thead>
						<tr>
							<th>#</th>
							<th><?php esc_html_e( 'Title', 'vkp' ); ?></th>
							<th><?php esc_html_e( 'Created', 'vkp' ); ?></th>
							<th><?php esc_html_e( 'Updated', 'vkp' ); ?></th>
							<th><?php esc_html_e( 'Photos', 'vkp' ); ?></th>
							<th><?php esc_html_e( 'In cache', 'vkp' ); ?> (Mb)</th>
							<th><?php esc_html_e( 'Shortcode', 'vkp' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $albums as $index => $album ) : ?>
							<tr class="<?php echo 0 === $index % 2 ? 'alternate' : ''; ?>">
								<td class="vkp_td vkp_td_h"><?php echo esc_html( $index + 1 ); ?></td>
								<td class="vkp_td">
									<?php
									$album_url = $album['ownerLink'] ?? '';
									$title     = $album['title'] ?? '';
									$desc      = $album['description'] ?? '';
									?>
									<?php if ( $album_url ) : ?>
										<a target="_blank" href="<?php echo esc_url( $album_url ); ?>"><strong><?php echo esc_html( $title ); ?></strong></a>
									<?php else : ?>
										<strong><?php echo esc_html( $title ); ?></strong>
									<?php endif; ?>
									<br>
									<?php echo esc_html( $desc ); ?>
								</td>
								<td><?php echo esc_html( $album['createdAt'] ?? '' ); ?></td>
								<td><?php echo esc_html( $album['updatedAt'] ?? '' ); ?></td>
								<td class="vkp_td_h"><?php echo esc_html( $album['size'] ?? 0 ); ?></td>
								<td class="vkp_td_h">
									<?php
									$cache_mb = $album['cacheSizeMb'] ?? null;
									echo null === $cache_mb ? '' : esc_html( $cache_mb );
									?>
								</td>
								<td><nobr><?php echo esc_html( $album['shortcode'] ?? '' ); ?></nobr></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

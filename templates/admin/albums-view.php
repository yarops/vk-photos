<div class="wrap">
	<style>
		.vkp_td {vertical-align: top;}
		.vkp_td_h {text-align: right;}
	</style>
	<h2><?php _e( 'Albums', 'vkp' ); ?></h2>

	<?php
	for ( $i = 1; $i <= 11; $i++ ) {
		$_error = 0;
		// сделаем запрос в контакт и спроим id
		if ( isset( $this->vkpAccaunts[ $i ] ) && ( (int) $this->vkpAccaunts[ $i ] ) > 0 ) {
			// делаем запрос для пользователя
			if ( isset( $this->vkpAccaunts_type[ $i ] ) && $this->vkpAccaunts_type[ $i ] == 'user' ) {
				$resp = $this->VKP->api(
					'users.get',
					array(
						'access_token' => $this->vkpAccessToken,
						'user_id'      => $this->vkpAccaunts[ $i ],
					)
				);
				if ( isset( $resp['error'] ) && is_array( $resp['error'] ) ) {
					$_error = $resp['error']['error_msg'];
				} elseif ( isset( $resp['response'] ) && is_array( $resp['response'] ) && ! empty( $resp['response'][0] ) ) {
					$acc_type = isset( $this->vkpAccaunts_type[ $i ] ) && $this->vkpAccaunts_type[ $i ] == 'group' ? 'club' : 'id';
					$vk_name  = "<a href='http://vk.com/" . $acc_type . $this->vkpAccaunts[ $i ] . "' target='_blank'><font color='green'>" . $resp['response'][0]['first_name'] . ' ' . $resp['response'][0]['last_name'] . '</font></a>';
				} else {
					$_error = __( 'Error getting user info', 'vkp' );
				}
			}
			// запрос для группы
			elseif ( isset( $this->vkpAccaunts_type[ $i ] ) && $this->vkpAccaunts_type[ $i ] == 'group' ) {
				$resp = $this->VKP->api(
					'groups.getById',
					array(
						'access_token' => $this->vkpAccessToken,
						'group_id'     => $this->vkpAccaunts[ $i ],
					)
				);
				if ( isset( $resp['error'] ) && is_array( $resp['error'] ) ) {
					$_error = $resp['error']['error_msg'];
				} elseif ( isset( $resp['response']['groups'] ) && is_array( $resp['response']['groups'] ) && ! empty( $resp['response']['groups'][0] ) ) {
					$group = $resp['response']['groups'][0];
					// Используем screen_name если доступен, иначе id.
					$group_link = isset( $group['screen_name'] ) && ! empty( $group['screen_name'] ) ? $group['screen_name'] : ( isset( $group['id'] ) ? 'club' . $group['id'] : 'club' . $this->vkpAccaunts[ $i ] );
					$vk_name    = "<a href='http://vk.com/" . $group_link . "' target='_blank'><font color='green'>" . $group['name'] . '</font></a>';
				} else {
					$_error = __( 'Error getting group info', 'vkp' );
				}
			}

			if ( $_error == 0 ) {
				echo '<h3>' . $vk_name . '</h3>';
				// смотрим на альбомы пользователя
				$owner_id = ( isset( $this->vkpAccaunts_type[ $i ] ) && $this->vkpAccaunts_type[ $i ] == 'group' ? '-' . abs( $this->vkpAccaunts[ $i ] ) : $this->vkpAccaunts[ $i ] );
				$resp     = $this->VKP->api(
					'photos.getAlbums',
					array(
						'access_token' => $this->vkpAccessToken,
						'owner_id'     => $owner_id,
					)
				);
				// проверяем наличие ошибки в ответе
				if ( isset( $resp['error'] ) && is_array( $resp['error'] ) ) {
					echo "<p><font color='red'>" . __( 'Error getting albums:', 'vkp' ) . ' ' . $resp['error']['error_msg'] . ' (Error code: ' . $resp['error']['error_code'] . ')</font></p>';
				} elseif ( isset( $resp['response'] ) && is_array( $resp['response'] ) && isset( $resp['response']['items'] ) && is_array( $resp['response']['items'] ) && count( $resp['response']['items'] ) > 0 ) {

					echo "<table class='wp-list-table widefat'><tr><th>#</th><th>" . __( 'Title', 'vkp' ) . '</th><th>' . __( 'Created', 'vkp' ) . '</th><th>' . __( 'Updated', 'vkp' ) . '</th><th>' . __( 'Photos', 'vkp' ) . '</th><th>' . __( 'In cache', 'vkp' ) . ' (Mb)</th><th>' . __( 'Shortcode', 'vkp' ) . '</th></tr>';
						$countAlbums = 1;
						$jj          = 1;
					foreach ( $resp['response']['items'] as $key => $value ) {

							$owner_prefix_album = ( isset( $this->vkpAccaunts_type[ $i ] ) && $this->vkpAccaunts_type[ $i ] == 'group' ? '-' : '' );
							// Get album ID, use 'aid' if available, otherwise fallback to 'id'.
							$album_id = isset( $value['aid'] ) ? $value['aid'] : ( isset( $value['id'] ) ? $value['id'] : '' );
							echo '<tr' . ( $jj == 1 ? " class='alternate'" : $jj = 0 ) . ">
									<td class='vkp_td vkp_td_h'>" . $countAlbums . "</td>
									<td class='vkp_td'><a target='_blank' href='http://vk.com/album" . $owner_prefix_album . $this->vkpAccaunts[ $i ] . '_' . $album_id . "'><b>" . $value['title'] . '</b></a><br>
									' . $value['description'] . '
									</td>
									<td>' . date( 'd.m.Y', $value['created'] ) . '</td>
									<td>' . date( 'd.m.Y', $value['updated'] ) . "</td>
									<td class='vkp_td_h'>" . $value['size'] . "</td>
									<td class='vkp_td_h'>" . ( $this->vkpCalculateCache == 'yes' ? round( ( $this->dir_size( $this->dirForCache . $owner_prefix_album . $this->vkpAccaunts[ $i ] . '/' . $album_id ) / 1024 / 1024 ), 2 ) : '' ) . "</td>
									<td><nobr>[vkalbum owner='" . $value['owner_id'] . "' id='" . $value['id'] . "']</nobr></td>
									</tr>";

							++$countAlbums;
							++$jj;
					}
							echo '</table>';
				} elseif ( isset( $resp['response'] ) && is_array( $resp['response'] ) && isset( $resp['response']['items'] ) && is_array( $resp['response']['items'] ) && count( $resp['response']['items'] ) == 0 ) {
					echo '<p>' . __( 'No Albums Found', 'vkp' ) . ' ' . __( '(The user has no albums or they are not accessible with service token)', 'vkp' ) . '</p>';
				} else {
					echo "<p><font color='orange'>" . __( 'Unable to get albums.', 'vkp' ) . '</font></p>';
					if ( isset( $resp['error'] ) ) {
						echo "<p><font color='red'>" . __( 'API Error:', 'vkp' ) . ' ' . $resp['error']['error_msg'] . ' (Code: ' . $resp['error']['error_code'] . ')</font></p>';
					}
					_e( 'No Albums Found', 'vkp' );
				}
			} else {
				echo "<font color='red'>" . $_error . '</font>';
			}
		}
	}
	?>
</div>
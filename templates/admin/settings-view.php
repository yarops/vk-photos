<div class="wrap">
	<h2><?php _e( 'Settings VK gallery', 'vkp' ); ?></h2>
	<?php _e( '(default values)', 'vkp' ); ?>
	<form method="post" action="options.php">
		<table>
			<tr>
				<th align='left' valing='top'>access_token</th>
				<td>
					<input type='text' style='width:660px;' name='vkpAccessToken' value='<?php echo $this->access_token; ?>'>
				</td>
			</tr>
			<tr>
				<th align='left' valing='top'>button title "more"</th>
				<td>
					<input type='text' style='width:160px;' name='vkpMoreTitle' value='<?php echo $this->more_title; ?>'>
				</td>
			</tr>
			<tr>
				<th align='left' valing='top'><?php _e( 'Count photos to view', 'vkp' ); ?></th>
				<td>
					<input type='number' style='width:160px;' name='vkpCountPhotos' value='<?php echo $this->count_photos; ?>' max="10000" min='1'>
				</td>
			</tr>
			<tr>
				<th align='left' valing='top'><?php _e( 'Preview size', 'vkp' ); ?></th>
				<td>
					<select name='vkpPreviewSize'>
							<option value='photo_75' <?php selected( $this->preview_size, 'photo_75' ); ?>>src_small</option>
							<option value='photo_130' <?php selected( $this->preview_size, 'photo_130' ); ?>>src</option>
							<option value='photo_604' <?php selected( $this->preview_size, 'photo_604' ); ?>>src_big</option>
							<option value='photo_807' <?php selected( $this->preview_size, 'photo_807' ); ?>>src_xbig</option>
							<option value='photo_1280' <?php selected( $this->preview_size, 'photo_1280' ); ?>>src_xxbig</option>
							<option value='photo_2560' <?php selected( $this->preview_size, 'photo_2560' ); ?>>src_xxxbig</option>
					</select>
				</td>
			</tr>
			<tr>
				<th align='left' valing='top'><?php _e( 'Photo view size', 'vkp' ); ?></th>
				<td>
					<select name='vkpPhotoViewSize'>
							<option value='photo_75' <?php selected( $this->photo_view_size, 'photo_75' ); ?>>src_small</option>
							<option value='photo_130' <?php selected( $this->photo_view_size, 'photo_130' ); ?>>src</option>
							<option value='photo_604' <?php selected( $this->photo_view_size, 'photo_604' ); ?>>src_big</option>
							<option value='photo_807' <?php selected( $this->photo_view_size, 'photo_807' ); ?>>src_xbig</option>
							<option value='photo_1280' <?php selected( $this->photo_view_size, 'photo_1280' ); ?>>src_xxbig</option>
							<option value='photo_2560' <?php selected( $this->photo_view_size, 'photo_2560' ); ?>>src_xxxbig</option>
					</select>
				</td>
			</tr>
			<tr>
				<th align='left' valing='top'><?php _e( 'Template', 'vkp' ); ?></th>
				<td>
					<select name='vkpTemplate'>
					<?php
						// ищем список шаблонов
						$dir = VKP__PLUGIN_DIR . 'templates';
					if ( $dirstream = @opendir( $dir ) ) {
						while ( false !== ( $filename = readdir( $dirstream ) ) ) {
							if ( $filename != '.' && $filename != '..' ) {
								if ( is_dir( $dir . '/' . $filename ) and file_exists( $dir . '/' . $filename . '/item.html' ) ) {
									?>
										<option value='<?php echo $filename; ?>'<?php selected( $this->template, $filename ); ?>><?php echo $filename; ?></option>
										<?php
								}
							}
						}
						closedir( $dirstream );
					}
					?>
					</select>
				</td>
			</tr>
			<tr>
				<th align='left' valing='top'><?php _e( 'Viewer', 'vkp' ); ?></th>
				<td>
					<select name='vkpViewer'>
							<option value='none' <?php selected( $this->viewer, 'none' ); ?>></option>
							<option value='colorbox' <?php selected( $this->viewer, 'colorbox' ); ?>>colorbox</option>
							<option value='swipebox' <?php selected( $this->viewer, 'swipebox' ); ?>>swipebox</option>
					</select>
				</td>
			</tr>
			<tr>
				<th align='left' valing='top'><?php _e( 'Show title', 'vkp' ); ?></th>
				<td>
					<input type='checkbox' name='vkpShowTitle' value='yes' <?php checked( $this->show_title, 'yes' ); ?>>
					</td>
			</tr>
				<tr>
				<th align='left' valing='top'><?php _e( 'Show description', 'vkp' ); ?></th>
				<td>
					<input type='checkbox' name='vkpShowDescription' value='yes' <?php checked( $this->show_description, 'yes' ); ?>>
					</td>
			</tr>
			<tr>
				<th align='left' valing='top'><?php _e( 'Show signatures', 'vkp' ); ?></th>
				<td>
					<input type='checkbox' name='vkpShowSignatures' value='yes' <?php checked( $this->show_signatures, 'yes' ); ?>>
					</td>
			</tr>
			<tr>
				<th align='left' valing='top'><?php _e( 'Enable caching', 'vkp' ); ?></th>
				<td>
					<input type='checkbox' name='vkpEnableCaching' value='yes' <?php checked( $this->enable_caching, 'yes' ); ?>>
					( <?php echo ( $this->calculate_cache == 'yes' ? __( 'Сache size:', 'vkp' ) . '&nbsp;' . round( ( $this->dir_size( $this->dir_for_cache ) / 1024 / 1024 ), 2 ) . 'M' : '' ); ?> )
				</td>
			</tr>
			<tr>
				<th align='left' valing='top'><?php _e( 'Calculate the size of the cache ', 'vkp' ); ?></th>
				<td>
					<input type='checkbox' name='vkpCalculateCache' value='yes' <?php checked( $this->calculate_cache, 'yes' ); ?>><small><?php _e( '(slows down the admin albums)', 'vkp' ); ?></small>
				</td>
			</tr>
			<tr>
				<th align='left' valing='top'><?php _e( 'Lifetime of the cache (h)', 'vkp' ); ?></th>
				<td>
					<input type='number' style='width:160px;' min='0' max='1000000' name='vkpLifeTimeCaching' value='<?php echo $this->lifetime_caching; ?>'><br>
					<small><?php _e( 'If the value of "Lifetime of the cache" = 0, the files are cached indefinitely.', 'vkp' ); ?></small>

				</td>
			</tr>
			<tr>
				<th align='left' valing='top'><?php _e( 'Preview (only cached photos)', 'vkp' ); ?></th>
				<td>
					<select name='vkpPreviewType'>
						<option value='keep'<?php selected( $this->preview_type, 'keep' ); ?>><?php _e( 'keep the proportions', 'vkp' ); ?></option>
						<option value='square'<?php selected( $this->preview_type, 'square' ); ?>><?php _e( 'square', 'vkp' ); ?></option>
					</select>
				</td>
			</tr>
			</table>

			<br>
			<?php
			// FIXME: Здесь логику нужно убрать из шаблона и перенести в сервис.
				// есть ли директория для кеша, и если ее нет - создаем
				clearstatcache();
			if ( ! file_exists( $this->dir_for_cache ) ) {
				// проверим возможность на запись родительского каталога
				if ( is_writable( $this->upload_dir['basedir'] ) ) {
					if ( @mkdir( $this->dir_for_cache, 0770 ) ) {
						echo "<font color='green'>" . __( 'Folder for the cache, which is created:', 'vkp' ) . ' ' . $this->dir_for_cache . '</font>';
					} else {
						echo "<font color='red'>" . __( 'It is impossible to create a folder', 'vkp' ) . ' ' . $this->dir_for_cache . '</font>';
					}
				} else {
					echo "<font color='red'>" . __( 'Directory', 'vkp' ) . ' ' . $this->upload_dir['basedir'] . ' ' . __( 'protected to write.', 'vkp' ) . '</font>';
				}
			} elseif ( ! is_writable( $this->dir_for_cache ) ) {
					echo "<font color='red'>" . __( 'Directory', 'vkp' ) . ' <b>' . $this->dir_for_cache . '</b> ' . __( 'protected to write.', 'vkp' ) . '</font>';
			} else {
				echo "<font color='green'>" . __( 'Directory for cache: ', 'vkp' ) . ' <b>' . $this->dir_for_cache . '</b></font>';
			}
			?>

			<hr>
			<table>
			<tr>
				<th align='left' colspan='2'><?php _e( 'Accounts', 'vkp' ); ?></th>
			</tr>
				<?php
				// FIXME: Здесь логику нужно убрать из шаблона и перенести в сервис.
				for ( $i = 1; $i <= 10; $i++ ) {
					// сделаем запрос в контакт и спроим id
					if ( isset( $this->accounts[ $i ] ) && ( (int) $this->accounts[ $i ] ) != 0 ) {
						// делаем запрос для пользователя
						if ( isset( $this->accounts_type[ $i ] ) && $this->accounts_type[ $i ] == 'user' && ( (int) $this->accounts[ $i ] ) > 0 ) {
							$resp = $this->VKP->api(
								'users.get',
								array(
									'access_token' => $this->access_token,
									'user_id'      => $this->accounts[ $i ],
								)
							);
							if ( isset( $resp['error'] ) && is_array( $resp['error'] ) ) {
								$vk_name = "<font color='red'>" . $resp['error']['error_msg'] . '</font>';
							} elseif ( isset( $resp['response'] ) && is_array( $resp['response'] ) && ! empty( $resp['response'][0] ) ) {
								$acc_type = isset( $this->accounts_type[ $i ] ) && $this->accounts_type[ $i ] == 'group' ? 'club' : 'id';
								$vk_name  = "<a href='http://vk.com/" . $acc_type . $this->accounts[ $i ] . "' target='_blank'><font color='green'>" . $resp['response'][0]['first_name'] . ' ' . $resp['response'][0]['last_name'] . '</font></a>';
							} else {
								$vk_name = "<font color='red'>" . __( 'Error getting user info', 'vkp' ) . '</font>';
							}
						}
						// запрос для группы
						if ( isset( $this->accounts_type[ $i ] ) && $this->accounts_type[ $i ] == 'group' ) {
								$resp = $this->VKP->api(
									'groups.getById',
									array(
										'access_token' => $this->access_token,
										'group_id'     => abs( $this->accounts[ $i ] ),
									)
								);
							if ( isset( $resp['error'] ) && is_array( $resp['error'] ) ) {
								$vk_name = "<font color='red'>" . $resp['error']['error_msg'] . '</font>';
							} elseif ( isset( $resp['response']['groups'] ) && is_array( $resp['response']['groups'] ) && ! empty( $resp['response']['groups'][0] ) ) {
								$group = $resp['response']['groups'][0];
								// Используем screen_name если доступен, иначе id.
								$group_link = isset( $group['screen_name'] ) && ! empty( $group['screen_name'] ) ? $group['screen_name'] : ( isset( $group['id'] ) ? 'club' . $group['id'] : 'club' . abs( $this->accounts[ $i ] ) );
								$vk_name    = "<a href='http://vk.com/" . $group_link . "' target='_blank'><font color='green'>" . $group['name'] . '</font></a>';
							} else {
									$vk_name = "<font color='red'>" . __( 'Error getting group info', 'vkp' ) . '</font>';
							}
						}
					} else {
						$vk_name = '';
					}

					echo "\n<tr><td>#" . $i . "</td><td>\n";
					echo "<input type='number' style='width:160px;' name='vkpAccaunts[" . $i . "]' value='" . ( isset( $this->accounts[ $i ] ) ? $this->accounts[ $i ] : '' ) . "'>\n";
					echo "<input type='radio' name='vkpAccaunts_type[" . $i . "]' value='group'" . ( isset( $this->accounts_type[ $i ] ) && $this->accounts_type[ $i ] == 'group' ? ' checked' : '' ) . '> ' . __( 'group', 'vkp' ) . "\n";
					echo "<input type='radio' name='vkpAccaunts_type[" . $i . "]' value='user'" . ( isset( $this->accounts_type[ $i ] ) && $this->accounts_type[ $i ] == 'user' ? ' checked' : '' ) . '> ' . __( 'user', 'vkp' ) . "\n";
					echo '&nbsp;&nbsp;' . $vk_name . '</td><tr>';

				}
				?>
		</table>
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="vkpMoreTitle,vkpAccessToken,vkpCountPhotos,vkpAccaunts,vkpAccaunts_type,vkpEnableCaching,vkpLifeTimeCaching,vkpPreviewSize,vkpPhotoViewSize,vkpPreviewType,vkpShowTitle,vkpShowSignatures,vkpTemplate,vkpViewer,vkpCalculateCache,vkpShowDescription" />
	<?php
		wp_nonce_field( 'update-options' );
		settings_fields( 'VKPPhotosSettingsGroup' );
		submit_button( __( 'Save', 'vkp' ) );
	?>
	</form>
</div>
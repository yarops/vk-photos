<?php
class VKPhotos
{
	// Properties declaration to avoid PHP 8.2+ dynamic property deprecation warnings.
	public $VKP;
	public $upload_dir;
	public $dirForCache;
	public $urlForCache;
	public $arrayPictureSize;
	public $arrayPictureSizeDESC;
	public $vkpCountPhotos;
	public $vkpLifeTimeCaching;
	public $vkpCalculateCache;
	public $vkpEnableCaching;
	public $vkpPreviewSize;
	public $vkpPhotoViewSize;
	public $vkpShowTitle;
	public $vkpShowDescription;
	public $vkpShowSignatures;
	public $vkpAccessToken;
	public $vkpAccaunts;
	public $vkpAccaunts_type;
	public $vkpPreviewType;
	public $vkpTemplate;
	public $vkpViewer;
	public $vkpMoreTitle;

	public function __construct()
	{
		// подключаем класс VK
		require_once( VKP__PLUGIN_DIR . '/api/vkapi.class.php' );
		$this->VKP = new vkapi();

		//хуки и фильтры WP
		if (is_admin()){
			add_action('admin_menu', array($this, 'vkphotos_add_menu'));
		}

		add_shortcode('vkalbum', array($this, 'vk_album_shortcode'));
		//////////////////////////////////////////////////////////////////////
		// ПЕРЕМЕННЫЕ И КОНСТАНТЫ
		$this->upload_dir   = wp_upload_dir();
		$this->dirForCache  = $this->upload_dir['basedir']."/vk-photos-cache/";
		$this->urlForCache  = $this->upload_dir['baseurl']."/vk-photos-cache/";
		//////////////////////////////////////////////////////////////////////
		// массив размеров изображений (по мере возрастания размера)
		$this->arrayPictureSize = array ('0' => 'photo_75','1' => 'photo_130','2' => 'photo_604','3' => 'photo_807','4' => 'photo_1280','5' => 'photo_2560');
		//////////////////////////////////////////////////////////////////////
		// массив размеров изображений (по мере убывания размера)
		//krsort($this->arrayPictureSize);
		$this->arrayPictureSizeDESC = array ('5' => 'photo_2560','4' => 'photo_1280','3' => 'photo_807','2' => 'photo_604','1' => 'photo_130','0' => 'photo_75');
		//////////////////////////////////////////////////////////////////////
		// Опции
		// кол-во фоотографий для постраничного вывода (поумолчанию)
		$_vkpCountPhotos = get_option('vkpCountPhotos');
		$this->vkpCountPhotos  = (((int)$_vkpCountPhotos)>0 ? ((int)$_vkpCountPhotos) : 12);
		// время жизни кеша
		$_vkpLifeTimeCaching = get_option('vkpLifeTimeCaching');
		$this->vkpLifeTimeCaching  = (((int)$_vkpLifeTimeCaching)>0 ? ((int)$_vkpLifeTimeCaching) : 0);
		// подсчет размера кеша
		$this->vkpCalculateCache   = (get_option('vkpCalculateCache')=='yes' ? "yes" : "no");
		// включение кеша
		$this->vkpEnableCaching    = (get_option('vkpEnableCaching')=='yes' ? "yes" : "no");
		// размер миниатюр
		$_vkpPreviewSize = get_option('vkpPreviewSize');
		$this->vkpPreviewSize = (in_array($_vkpPreviewSize, $this->arrayPictureSize) ? $_vkpPreviewSize: "photo_130");
		// размер больших фотографий
		$_vkpPhotoViewSize = get_option('vkpPhotoViewSize');
		$this->vkpPhotoViewSize = (in_array($_vkpPhotoViewSize, $this->arrayPictureSize) ? $_vkpPhotoViewSize: "photo_807");
		// показывать заголовки
		$this->vkpShowTitle  = (get_option('vkpShowTitle')=='yes' ? "yes" : "no");
		// показывать описание альбома
		$this->vkpShowDescription  = (get_option('vkpShowDescription')=='yes' ? "yes" : "no");
		// показывать подписи
		$this->vkpShowSignatures  = (get_option('vkpShowSignatures')=='yes' ? "yes" : "no");
		// показывать подписи
		$this->vkpAccessToken  = get_option('vkpAccessToken');
		// аккаунты VK
		$_vkpAccaunts = get_option('vkpAccaunts');
		$this->vkpAccaunts = (is_array($_vkpAccaunts) ? $_vkpAccaunts : array());
		// типы аккаунтов
		$_vkpAccaunts_type = get_option('vkpAccaunts_type');
		$this->vkpAccaunts_type = (is_array($_vkpAccaunts_type) ? $_vkpAccaunts_type : array());
		// миниатюра для кеша
		$_vkpPreviewType = sanitize_text_field(get_option('vkpPreviewType'));
		$this->vkpPreviewType = (isset($_vkpPreviewType) ? $_vkpPreviewType : 'keep');
		// Шаблон
		$_vkpTemplate = sanitize_text_field(get_option('vkpTemplate'));
		$this->vkpTemplate = (isset($_vkpTemplate) ? $_vkpTemplate : 'light');
		// Просмотрщик
		$_vkpViewer =  sanitize_text_field(get_option('vkpViewer'));
		$this->vkpViewer = ($_vkpViewer ? $_vkpViewer : 'none');
		// Текст кнопки "далее"
		$_vkpMoreTitle =  sanitize_text_field(get_option('vkpMoreTitle'));
		$this->vkpMoreTitle = ($_vkpMoreTitle ? $_vkpMoreTitle : '[далее]');


	}

	/* admin_menu hook */

	public function vkphotos_add_menu() {
		add_menu_page(__('VK photos','vkp'), __('VK photos','vkp'), 'manage_options','vk-photos.php',array($this, 'vkphotos_options_page'), plugins_url('vk-photos/images/icon.png'), 86 );
		add_submenu_page( 'vk-photos.php', __('Albums online','vkp'), __('Albums online','vkp'), 'manage_options', 'vk-shortcode', array($this, 'vk_albums'));
		add_submenu_page( 'vk-photos.php', __('Albums on site','vkp'), __('Albums on site','vkp'), 'manage_options', 'vk-cache', array($this, 'vk_albums_on_cache'));
		add_submenu_page( 'vk-photos.php', __('Templates','vkp'), __('Templates','vkp'), 'manage_options', 'vk-templates', array($this, 'vk_templates'));
		add_submenu_page( 'vk-photos.php', __('Help','vkp'), __('Help','vkp'), 'manage_options', 'vk-help', array($this, 'vk_help'));
	}
	////////////////////////////////////////////////////////////////////////////////////////////
	// ПОМОЩЬ
	public function vk_help(){

		$help = "<div class='wrap'>
				<h2>".__('Help','vkp')."</h2>";

		$help.= "<h3>".__('More options shortcode','vkp')." [vkalbum]</h3>\n";
		$help.= "<p>";
		$help.= "<b>template</b> - ".__('template gallery, the default value is "light" or specified in plugin settings.','vkp')."<br>\n";
		$help.= "<b>count</b> - ".__('number of photos per load, the default value is 12','vkp')."<br>\n";
		$help.= "<b>preview</b> - ".__('thumbnail size','vkp')." (src_small, src, src_big, src_xbig, src_xxbig, src_xxxbig)<br>\n";
		$help.= "<b>photo</b> - ".__('size photos','vkp')." (src_small, src, src_big, src_xbig, src_xxbig, src_xxxbig)<br>\n";
		$help.= "<b>cache</b> - ".__('photos from the cache, or from vk.com','vkp')." (yes,no)<br>\n";

		$help.= "</p>\n";

		$help.= "<br><h3>".__('For example:','vkp')."</h3>\n";
		$help.= "<b>[vkalbum owner='-32878584' id='193472590']</b> - <font color='#cccccc'>".__('Will be used by the parameters set by default.','vkp')."</font><br>\n";
		$help.= "<b>[vkalbum owner='-32878584' id='193472590' template='fresh']</b> - <font color='#cccccc'>".__('Gallery template-based','vkp')." 'fresh'</font><br>\n";
		$help.= "<b>[vkalbum owner='-32878584' id='193472590' count='24']</b> - <font color='#cccccc'>".__('Output to 24 photos.','vkp')."</font><br>\n";
		$help.= "<b>[vkalbum owner='-32878584' id='193472590' cache='no' template='blocks' preview='src_small' photo='src_big']</b><br>\n";

		$help.= "<br><h3>".__('Image sizes','vkp')."</h3>\n";
		$help.= "<b>src_small</b> — ".__('url photos with maximum size','vkp')." 75x75px;<br>";
		$help.= "<b>src</b> — ".__('url photos with maximum size','vkp')." 130x130px;<br>";
		$help.= "<b>src_big</b> — ".__('url photos with maximum size','vkp')." 604x604px;<br>";
		$help.= "<b>src_xbig</b> — ".__('url photos with maximum size','vkp')." 807x807px;<br>";
		$help.= "<b>src_xxbig</b> — ".__('url photos with maximum size','vkp')." 1280x1024px;<br>";
		$help.= "<b>src_xxxbig</b> — ".__('url photos with maximum size','vkp')." 2560x2048px;<br>";
		$help.= "</div>";

		$help.= "<br><h3>VK application</h3>\n";
		$help.= "To get 'access_token' you need to create the 'Standalone' type application, set it correctly (attention to the parameters highlighted in red) and add the 'access_token' parameter in the plugin settings.\n";
		$help.= "<img src='".VKP__PLUGIN_URL.'images'."/vkapp.png"."'>";

		echo $help;
	}

	////////////////////////////////////////////////////////////////////////////////////////////
	// ШАБЛОНЫ
	public function vk_templates(){

		echo "<div class='wrap'><h2>".__('Templates','vkp')."</h2>";
		// ищем список шаблонов
		$dir = VKP__PLUGIN_DIR.'templates';
		if ($dirstream = @opendir($dir)) {
			while (false !== ($filename = readdir($dirstream))) {
				if ($filename!="." && $filename!=".."){
					if (is_dir($dir."/".$filename) and file_exists($dir."/".$filename."/item.html")){
						echo "<div style='float:left;margin:10px;'><h3>".$filename."<h3><img src='".VKP__PLUGIN_URL.'templates'."/".$filename."/thumb.jpg"."'></div>";
					}
				}
			}
		closedir($dirstream);
		}
		echo "</div>";

	}

	////////////////////////////////////////////////////////////////////////////////////////////
	// Общие настройки плагина

	public function vkphotos_options_page() {
		   ?>
			<div class="wrap">
				<h2><?php _e('Settings VK gallery','vkp'); ?></h2>
				<?php _e('(default values)','vkp'); ?>
				<form method="post" action="options.php">
					<table>
						<tr>
							<th align='left' valing='top'>access_token</th>
							<td>
								<input type='text' style='width:660px;' name='vkpAccessToken' value='<?php echo $this->vkpAccessToken; ?>'>
							</td>
						</tr>
						<tr>
							<th align='left' valing='top'>button title "more"</th>
							<td>
								<input type='text' style='width:160px;' name='vkpMoreTitle' value='<?php echo $this->vkpMoreTitle; ?>'>
							</td>
						</tr>
						<tr>
							<th align='left' valing='top'><?php _e("Count photos to view","vkp"); ?></th>
							<td>
								<input type='number' style='width:160px;' name='vkpCountPhotos' value='<?php echo $this->vkpCountPhotos; ?>' max="10000" min='1'>
							</td>
						</tr>
						<tr>
							<th align='left' valing='top'><?php _e("Preview size","vkp"); ?></th>
							<td>
								<select name='vkpPreviewSize'>
									 <option value='photo_75' <?php selected($this->vkpPreviewSize,'photo_75'); ?>>src_small</option>
									 <option value='photo_130' <?php selected($this->vkpPreviewSize,'photo_130'); ?>>src</option>
									 <option value='photo_604' <?php selected($this->vkpPreviewSize,'photo_604'); ?>>src_big</option>
									 <option value='photo_807' <?php selected($this->vkpPreviewSize,'photo_807'); ?>>src_xbig</option>
									 <option value='photo_1280' <?php selected($this->vkpPreviewSize,'photo_1280'); ?>>src_xxbig</option>
									 <option value='photo_2560' <?php selected($this->vkpPreviewSize,'photo_2560'); ?>>src_xxxbig</option>
								</select>
							</td>
						</tr>
						<tr>
							<th align='left' valing='top'><?php _e("Photo view size","vkp"); ?></th>
							<td>
								<select name='vkpPhotoViewSize'>
									 <option value='photo_75' <?php selected($this->vkpPhotoViewSize,'photo_75'); ?>>src_small</option>
									 <option value='photo_130' <?php selected($this->vkpPhotoViewSize,'photo_130'); ?>>src</option>
									 <option value='photo_604' <?php selected($this->vkpPhotoViewSize,'photo_604'); ?>>src_big</option>
									 <option value='photo_807' <?php selected($this->vkpPhotoViewSize,'photo_807'); ?>>src_xbig</option>
									 <option value='photo_1280' <?php selected($this->vkpPhotoViewSize,'photo_1280'); ?>>src_xxbig</option>
									 <option value='photo_2560' <?php selected($this->vkpPhotoViewSize,'photo_2560'); ?>>src_xxxbig</option>
								</select>
							</td>
						</tr>
						<tr>
							<th align='left' valing='top'><?php _e("Template","vkp"); ?></th>
							<td>
								<select name='vkpTemplate'>
								<?php
								// ищем список шаблонов
								$dir = VKP__PLUGIN_DIR.'templates';
								if ($dirstream = @opendir($dir)) {
									while (false !== ($filename = readdir($dirstream))) {
										if ($filename!="." && $filename!=".."){
											if (is_dir($dir."/".$filename) and file_exists($dir."/".$filename."/item.html")){
												?>
													<option value='<?php echo $filename; ?>'<?php selected($this->vkpTemplate,$filename); ?>><?php echo $filename; ?></option>
												<?php
											}
										}
									}
								closedir($dirstream);
								}
								?>
								</select>
							</td>
						</tr>
						<tr>
							<th align='left' valing='top'><?php _e("Viewer","vkp"); ?></th>
							<td>
								<select name='vkpViewer'>
									 <option value='none' <?php selected($this->vkpViewer,'none'); ?>></option>
									 <option value='colorbox' <?php selected($this->vkpViewer,'colorbox'); ?>>colorbox</option>
									 <option value='swipebox' <?php selected($this->vkpViewer,'swipebox'); ?>>swipebox</option>
								</select>
							</td>
						</tr>
						<tr>
							<th align='left' valing='top'><?php _e("Show title","vkp"); ?></th>
							<td>
								<input type='checkbox' name='vkpShowTitle' value='yes' <?php checked($this->vkpShowTitle,'yes'); ?>>
							 </td>
						</tr>
						 <tr>
							<th align='left' valing='top'><?php _e("Show description","vkp"); ?></th>
							<td>
								<input type='checkbox' name='vkpShowDescription' value='yes' <?php checked($this->vkpShowDescription,'yes'); ?>>
							 </td>
						</tr>
						<tr>
							<th align='left' valing='top'><?php _e("Show signatures","vkp"); ?></th>
							<td>
								<input type='checkbox' name='vkpShowSignatures' value='yes' <?php checked($this->vkpShowSignatures,'yes'); ?>>
							 </td>
						</tr>
						<tr>
							<th align='left' valing='top'><?php _e("Enable caching","vkp"); ?></th>
							<td>
								<input type='checkbox' name='vkpEnableCaching' value='yes' <?php checked($this->vkpEnableCaching,'yes'); ?>>
								( <?php echo ($this->vkpCalculateCache=='yes' ? __("Сache size:","vkp")."&nbsp;".round(($this->dir_size($this->dirForCache)/1024/1024),2)."M": ""); ?> )
							</td>
						</tr>
						<tr>
							<th align='left' valing='top'><?php _e("Calculate the size of the cache ","vkp"); ?></th>
							<td>
								<input type='checkbox' name='vkpCalculateCache' value='yes' <?php checked($this->vkpCalculateCache,'yes'); ?>><small><?php _e('(slows down the admin albums)','vkp'); ?></small>
							</td>
						</tr>
						<tr>
							<th align='left' valing='top'><?php _e("Lifetime of the cache (h)","vkp"); ?></th>
							<td>
								<input type='number' style='width:160px;' min='0' max='1000000' name='vkpLifeTimeCaching' value='<?php echo $this->vkpLifeTimeCaching; ?>'><br>
								<small><?php _e('If the value of "Lifetime of the cache" = 0, the files are cached indefinitely.','vkp'); ?></small>

							</td>
						</tr>
						<tr>
							<th align='left' valing='top'><?php _e("Preview (only cached photos)","vkp"); ?></th>
							<td>
								<select name='vkpPreviewType'>
									<option value='keep'<?php selected($this->vkpPreviewType,'keep'); ?>><?php _e('keep the proportions','vkp'); ?></option>
									<option value='square'<?php selected($this->vkpPreviewType,'square'); ?>><?php _e('square','vkp'); ?></option>
								</select>
							</td>
						</tr>
						</table>
						<br>
						<?php
						// есть ли директория для кеша, и если ее нет - создаем
						clearstatcache();
						if(!file_exists($this->dirForCache)){
							// проверим возможность на запись родительского каталога
							if(is_writable($this->upload_dir['basedir'])){
								if(@mkdir($this->dirForCache, 0770)){
									echo "<font color='green'>".__('Folder for the cache, which is created:','vkp')." ".$this->dirForCache."</font>";
								}else{
									echo "<font color='red'>".__('It is impossible to create a folder','vkp')." ".$this->dirForCache."</font>";
								}
							}else{
								echo "<font color='red'>".__('Directory','vkp')." ".$this->upload_dir['basedir']." ".__('protected to write.','vkp')."</font>";
							}
						}else{
							if(!is_writable($this->dirForCache)){
								echo "<font color='red'>".__('Directory','vkp')." <b>".$this->dirForCache."</b> ".__('protected to write.','vkp')."</font>";
							}else{
								echo "<font color='green'>".__('Directory for cache: ','vkp')." <b>".$this->dirForCache."</b></font>";
							}
						}
						?>

						<hr>
						<table>
						<tr>
							<th align='left' colspan='2'><?php _e("Accounts","vkp"); ?></th>
						</tr>
							<?php
									for ($i=1; $i <=10 ; $i++) {
										// сделаем запрос в контакт и спроим id
										if(isset($this->vkpAccaunts[$i]) && ((int)$this->vkpAccaunts[$i])>0){
											// делаем запрос для пользователя
											if(isset($this->vkpAccaunts_type[$i]) && $this->vkpAccaunts_type[$i]=='user'){
												$resp = $this->VKP->api('users.get', array('access_token'=> $this->vkpAccessToken,'user_id'=>$this->vkpAccaunts[$i]));
												if(isset($resp['error']) && is_array($resp['error'])){
													$vk_name = "<font color='red'>".$resp['error']['error_msg']."</font>";
												}elseif(isset($resp['response']) && is_array($resp['response']) && !empty($resp['response'][0])){
													$acc_type = isset($this->vkpAccaunts_type[$i]) && $this->vkpAccaunts_type[$i]=='group' ? "club":"id";
													$vk_name = "<a href='http://vk.com/".$acc_type.$this->vkpAccaunts[$i]."' target='_blank'><font color='green'>".$resp['response'][0]['first_name']." ".$resp['response'][0]['last_name']."</font></a>";
												}else{
													$vk_name = "<font color='red'>".__('Error getting user info','vkp')."</font>";
												}
											}
											// запрос для группы
											 if(isset($this->vkpAccaunts_type[$i]) && $this->vkpAccaunts_type[$i]=='group'){
												$resp = $this->VKP->api('groups.getById', array('access_token'=> $this->vkpAccessToken,'group_id'=>$this->vkpAccaunts[$i]));
												if(isset($resp['error']) && is_array($resp['error'])){
													$vk_name = "<font color='red'>".$resp['error']['error_msg']."</font>";
												}elseif(isset($resp['response']) && is_array($resp['response']) && !empty($resp['response'][0])){
													$vk_name = "<a href='http://vk.com/".$resp['response'][0]['screen_name']."' target='_blank'><font color='green'>".$resp['response'][0]['name']."</font></a>";
												}else{
													$vk_name = "<font color='red'>".__('Error getting group info','vkp')."</font>";
												}
											}
										}else{
											$vk_name = "";
										}

										echo "\n<tr><td>#".$i."</td><td>\n";
										echo "<input type='number' style='width:160px;' name='vkpAccaunts[".$i."]' value='".(isset($this->vkpAccaunts[$i]) ? $this->vkpAccaunts[$i] : '')."'>\n";
										echo "<input type='radio' name='vkpAccaunts_type[".$i."]' value='group'".(isset($this->vkpAccaunts_type[$i]) && $this->vkpAccaunts_type[$i]=='group' ? " checked" : "")."> ".__('group','vkp')."\n";
										echo "<input type='radio' name='vkpAccaunts_type[".$i."]' value='user'".(isset($this->vkpAccaunts_type[$i]) && $this->vkpAccaunts_type[$i]=='user' ? " checked" : "")."> ".__('user','vkp')."\n";
										echo "&nbsp;&nbsp;".$vk_name."</td><tr>";

									}
							?>
					</table>
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="vkpMoreTitle,vkpAccessToken,vkpCountPhotos,vkpAccaunts,vkpAccaunts_type,vkpEnableCaching,vkpLifeTimeCaching,vkpPreviewSize,vkpPhotoViewSize,vkpPreviewType,vkpShowTitle,vkpShowSignatures,vkpTemplate,vkpViewer,vkpCalculateCache,vkpShowDescription" />
				<?php
					wp_nonce_field('update-options');
					settings_fields('VKPPhotosSettingsGroup');
					submit_button(__("Save","vkp"));
				?>
				</form>


			</div>
			<?php
	}

	/***********************************************************************
	* Функция настроек для сети vk.com
	************************************************************************/
	public function vk_albums() {
	   ?>
			<div class="wrap">
				<style>
					.vkp_td {vertical-align: top;}
					.vkp_td_h {text-align: right;}
				</style>
				<h2><?php _e('Albums','vkp'); ?></h2>

							<?php
									for ($i=1; $i <=11 ; $i++) {
										$_error = 0;
										// сделаем запрос в контакт и спроим id
										if(isset($this->vkpAccaunts[$i]) && ((int)$this->vkpAccaunts[$i])>0){
											// делаем запрос для пользователя
											if(isset($this->vkpAccaunts_type[$i]) && $this->vkpAccaunts_type[$i]=='user'){
												$resp = $this->VKP->api('users.get', array('access_token'=> $this->vkpAccessToken,'user_id'=>$this->vkpAccaunts[$i]));
												if(isset($resp['error']) && is_array($resp['error'])){
													$_error = $resp['error']['error_msg'];
												}elseif(isset($resp['response']) && is_array($resp['response']) && !empty($resp['response'][0])){
													$acc_type = isset($this->vkpAccaunts_type[$i]) && $this->vkpAccaunts_type[$i]=='group' ? "club":"id";
													$vk_name = "<a href='http://vk.com/".$acc_type.$this->vkpAccaunts[$i]."' target='_blank'><font color='green'>".$resp['response'][0]['first_name']." ".$resp['response'][0]['last_name']."</font></a>";
												}else{
													$_error = __('Error getting user info','vkp');
												}
											}
											// запрос для группы
											 if(isset($this->vkpAccaunts_type[$i]) && $this->vkpAccaunts_type[$i]=='group'){
												$resp = $this->VKP->api('groups.getById', array('access_token'=> $this->vkpAccessToken,'group_id'=>$this->vkpAccaunts[$i]));
												if(isset($resp['error']) && is_array($resp['error'])){
													$_error = $resp['error']['error_msg'];
												}elseif(isset($resp['response']) && is_array($resp['response']) && !empty($resp['response'][0])){
													$vk_name = "<a href='http://vk.com/".$resp['response'][0]['screen_name']."' target='_blank'><font color='green'>".$resp['response'][0]['name']."</font></a>";
												}else{
													$_error = __('Error getting group info','vkp');
												}
											}

											if($_error==0){
												echo "<h3>".$vk_name."</h3>";
												// смотрим на альбомы пользователя
												$owner_prefix = (isset($this->vkpAccaunts_type[$i]) && $this->vkpAccaunts_type[$i]=='group' ? "-":"");
												$resp = $this->VKP->api('photos.getAlbums', array('access_token'=> $this->vkpAccessToken,'owner_id'=>$owner_prefix.$this->vkpAccaunts[$i]));
												// проверяем наличие ошибки в ответе
												if(isset($resp['error']) && is_array($resp['error'])){
													echo "<p><font color='red'>".__('Error getting albums:','vkp')." ".$resp['error']['error_msg']." (Error code: ".$resp['error']['error_code'].")</font></p>";
												}elseif(isset($resp['response']) && is_array($resp['response']) && isset($resp['response']['items']) && is_array($resp['response']['items']) && count($resp['response']['items']) > 0){


													echo "<table class='wp-list-table widefat'><tr><th>#</th><th>".__('Title','vkp')."</th><th>".__('Created','vkp')."</th><th>".__('Updated','vkp')."</th><th>".__('Photos','vkp')."</th><th>".__('In cache','vkp')." (Mb)</th><th>".__('Shortcode','vkp')."</th></tr>";
														$countAlbums=1;
														$jj = 1;
														foreach ($resp['response']['items'] as $key => $value) {

															$owner_prefix_album = (isset($this->vkpAccaunts_type[$i]) && $this->vkpAccaunts_type[$i]=='group' ? "-":"");
															// Get album ID, use 'aid' if available, otherwise fallback to 'id'.
															$album_id = isset($value['aid']) ? $value['aid'] : (isset($value['id']) ? $value['id'] : '');
															echo "<tr".($jj==1 ? " class='alternate'": $jj=0).">
															<td class='vkp_td vkp_td_h'>".$countAlbums."</td>
															<td class='vkp_td'><a target='_blank' href='http://vk.com/album".$owner_prefix_album.$this->vkpAccaunts[$i]."_".$album_id."'><b>".$value['title']."</b></a><br>
															".$value['description']."
															</td>
															<td>".date("d.m.Y", $value['created'])."</td>
															<td>".date("d.m.Y", $value['updated'])."</td>
															<td class='vkp_td_h'>".$value['size']."</td>
															<td class='vkp_td_h'>".($this->vkpCalculateCache=='yes' ? round(($this->dir_size($this->dirForCache.$owner_prefix_album.$this->vkpAccaunts[$i]."/".$album_id)/1024/1024),2):"")."</td>
															<td><nobr>[vkalbum owner='".$value['owner_id']."' id='".$value['id']."']</nobr></td>
															</tr>";

															$countAlbums++;
															$jj++;
														}
													echo "</table>";
												}elseif(isset($resp['response']) && is_array($resp['response']) && isset($resp['response']['items']) && is_array($resp['response']['items']) && count($resp['response']['items']) == 0){
													echo "<p>".__('No Albums Found','vkp')." ".__('(The user has no albums or they are not accessible with service token)','vkp')."</p>";
												}else{
													echo "<p><font color='orange'>".__('Unable to get albums.','vkp')."</font></p>";
													if(isset($resp['error'])){
														echo "<p><font color='red'>".__('API Error:','vkp')." ".$resp['error']['error_msg']." (Code: ".$resp['error']['error_code'].")</font></p>";
													}
													_e('No Albums Found','vkp');
												}
											}else{
												echo "<font color='red'>".$_error."</font>";
											}

										}

									}
							?>
			</div>

		<?php

	}

	/////////////////////////////////////////////////////////////////////////////////////
	// список альбомов в кеше

	public function vk_albums_on_cache(){
				/*
					1. Смотрим на директорию кеша vk-photos
					2. Находим владельцев
					3. У владельцев находим альбомы
					4. Считаем, показываем (какие миниатюры какие в кеше - при просмотре альбома), предоставляем действия (удалить/обновить)

				*/
				  //  $this->dirForCache



		?>
			<div class='wrap'>
				<style>
					.vkp_td {vertical-align: top;}
					.vkp_td_h {text-align: right;}
				</style>
				<h2><?php echo __('Albums on site','vkp')." ".__('(in cache)','vkp'); ?></h2>
				<?php
					if ($dirstream = @opendir($this->dirForCache)) {
						echo "<table  class='wp-list-table widefat' cellpadding=5>
						<tr><th></th><th>".__('Name','vkp')."</th><th>".__('Shortcode','vkp')."</th><th>".__('Size','vkp')." (Mb)</th><th>".__('Action','vkp')."</th></tr>
						";
						while (false !== ($filename = readdir($dirstream))) {

							if(!is_dir($this->dirForCache.$filename)) continue;

							$vk_name1 = "";
							$vk_name2 = "";


							if ($filename!="." && $filename!=".."){
								// на этом уровне находятся пользователи и группы
								if(($filename*1)>0){
									// положительный идентификатор - пользователи
									$resp = $this->VKP->api('users.get', array('access_token'=> $this->vkpAccessToken,'user_id'=>$filename,'fields'=>'photo_50'));
									if(isset($resp['error']) && is_array($resp['error'])){
										$vk_name1 = "";
										$vk_name2 = "<font color='red'>".$resp['error']['error_msg']."</font>";
									}elseif(isset($resp['response']) && is_array($resp['response']) && !empty($resp['response'][0])){
										$vk_name1 = "<img src='".$resp['response'][0]['photo_50']."' style='float:left;border:0px;margin:3px;width:24px;'>";
										$vk_name2 = "<big><a href='http://vk.com/id".$resp['response'][0]['uid']."' target='_blank'><font color='green'>".$resp['response'][0]['first_name']." ".$resp['response'][0]['last_name']."</font></a></big>";
									}else{
										$vk_name1 = "";
										$vk_name2 = "<font color='red'>".__('Error getting user info','vkp')."</font>";
									}
								}
								if(($filename*1)<0){
									// отрицательный идентификатор - группы
									$resp = $this->VKP->api('groups.getById', array('access_token'=> $this->vkpAccessToken,'group_id'=>abs($filename)));
									if(isset($resp['error']) && is_array($resp['error'])){
										$vk_name1 = "";
										$vk_name2 = "<font color='red'>".$resp['error']['error_msg']."</font>";
									}elseif(isset($resp['response']) && is_array($resp['response']) && !empty($resp['response'][0])){
										$vk_name1 = "<img src='".$resp['response'][0]['photo']."' style='float:left;border:0px;margin:3px;width:24px;'>";
										$vk_name2 = "<big><a href='http://vk.com/club".$resp['response'][0]['gid']."' target='_blank'><font color='green'>".$resp['response'][0]['name']."</font></a></big>";
									}else{
										$vk_name1 = "";
										$vk_name2 = "<font color='red'>".__('Error getting group info','vkp')."</font>";
									}
								}

								echo "<tr><td>".$vk_name1."</td><td>".$vk_name2."</td><td></td><td class='vkp_td_h'><big><b>".($this->vkpCalculateCache=='yes' ? round(($this->dir_size($this->dirForCache.$filename)/1024/1024),2):"")."</b></big></td><td><a href='".$_SERVER['REQUEST_URI']."&clearcache=".$filename."'><font color='red'>[".__('delete')."]</font></a></td></tr>";
								// начинаем искать альбомы
								if ($albumstream = @opendir($this->dirForCache.$filename)) {
									while (false !== ($albumname = readdir($albumstream))) {
										if ($albumname!="." && $albumname!=".."){
											if (is_dir($this->dirForCache.$filename."/".$albumname)){
												if($album = file_get_contents($this->dirForCache.$filename."/".$albumname."/description.album")){
													$album = unserialize($album);
													echo "<tr>
															<td></td>
															<td><b>".$album['response'][0]['title']."</b><br><em>".$album['response'][0]['description']."</em></td>
															<td><nobr>[vkalbum owner='".$filename."' id='".$albumname."']</nobr></td>
															<td class='vkp_td_h'>".($this->vkpCalculateCache=='yes' ? round(($this->dir_size($this->dirForCache.$filename."/".$albumname)/1024/1024),2):"")."</td>
															<td><a href='".network_site_url()."/wp-admin/admin.php?page=vk-cache&clearcache=".$filename."|".$albumname."'><font color='red'>[".__('delete')."]</font></a></td>
														</tr>
														";
												}
											}

										}
									}
								}
								closedir($albumstream);


							}

						}
						echo "</table>";
					}
					closedir($dirstream);

				?>

			</div>
		<?php
	}
	/////////////////////////////////////////////////////////////////////////////////////
	// шорткод
	public function vk_album_shortcode($atts){

			extract(shortcode_atts(array(
			  'cache' => $this->vkpEnableCaching
			), $atts));

			if($cache=='yes'){
				// проверка и создание кеша
				$this->vk_album_create_cache($atts);
				// отображение альбома из кеша
				$output = $this->show_album_from($atts,'cache');
			}else{
				$output = $this->show_album_from($atts,'vk');
			}
		return $output;
	}

	/////////////////////////////////////////////////////////////////////////////////////
	// создание кеша альбома
	public function vk_album_create_cache($atts) {

			//получение параметров
			extract(shortcode_atts(array(
			  'id' => "no",
			  'owner' => "no"
			), $atts));

			$_cache1 = false;   // флаг для папки владельца
			$_cache2 = false;   // флаг для папки альбома

			// ждем от параметров $owner,$id только правильных значений, поэтому повторно не проверяем


				// не обращаем внимание на включенность кеша  and $this->vkpEnableCaching=='yes'

				if(file_exists($this->dirForCache) and is_writable($this->dirForCache)){
					// директория для кеша существует и доступна для записи
					// проверим существует ли директория пользователя $owner
					$ownerDirForCache = $this->dirForCache.$owner."/";
					// проверим есть ли папка с идентификатором пользователя/группы
					if(file_exists($ownerDirForCache) and is_writable($ownerDirForCache)){
							$_cache1 = true;
					}else{
						if(mkdir($ownerDirForCache, 0770)){
							$_cache1 = true;
						}
					}
					// проверим есть ли папка альбома и если нет - создадим
					if(file_exists($ownerDirForCache.$id."/") and is_writable($ownerDirForCache.$id."/")){
							$_cache2 = true;
					}else{
						if(mkdir($ownerDirForCache.$id."/", 0770)){
							$_cache2 = true;
						}
					}

					//кешируем
					if($_cache1 == true and $_cache2 == true){
						$albumCache = $ownerDirForCache."album_".$id.".cache";
						if(file_exists($albumCache) and filesize($albumCache)>100){
							//если существует - проверим возраст кеша
							if((time()-filemtime($albumCache))>$this->vkpLifeTimeCaching*3600 and ($this->vkpLifeTimeCaching*1)>0){
								// получим данные по фотографиям
								$photos = $this->VKP->api('photos.get', array('access_token'=> $this->vkpAccessToken,'album_id'=>$id,'owner_id'=>$owner));

								// запишем в кеш
								file_put_contents($albumCache,serialize($photos));
								// получим данные об альбоме
								$album = $this->VKP->api('photos.getAlbums', array('access_token'=> $this->vkpAccessToken,'album_ids'=>$id,'owner_id'=>$owner));
								 // запишем в кеш
								file_put_contents($ownerDirForCache.$id."/description.album",serialize($album));

							}else{
								// Возьмем из кеша
								$photos = @file_get_contents($albumCache);
								$photos = unserialize($photos);
							}
						}else{
								// получим данные
								$photos = $this->VKP->api('photos.get', array('access_token'=> $this->vkpAccessToken,'album_id'=>$id,'owner_id'=>$owner));

								// запишем в кеш
								file_put_contents($albumCache,serialize($photos));
								// получим данные об альбоме
								$album = $this->VKP->api('photos.getAlbums', array('access_token'=> $this->vkpAccessToken,'album_ids'=>$id,'owner_id'=>$owner));
								 // запишем в кеш
								file_put_contents($ownerDirForCache.$id."/description.album",serialize($album));
						}



					}

				}

				// что-то идет не так - выходим
				if($_cache1 == false or $_cache2 == false){
					return;
				}
				// конец процедуры кеширования и получения массива данных

				// отсортируем массив в обратном порядке по ключу


				krsort($this->arrayPictureSize);

				if(!is_array($photos)) return;


				foreach ($photos['response']['items'] as $key => $value) {

						// кешируем фотографии

						$_vkpPreviewSize = get_photo_size($this->vkpPreviewSize,$value);
						$_vkpPhotoViewSize = get_photo_size($this->vkpPhotoViewSize,$value);

						if($_vkpPreviewSize!=false and $_vkpPhotoViewSize!=false){

							$filenamePreview                = "thumb_".$value['id'].".".pathinfo($value[$_vkpPreviewSize], PATHINFO_EXTENSION);
							$filenamePhoto                  = "photo_".$value['id'].".".pathinfo($value[$_vkpPhotoViewSize], PATHINFO_EXTENSION);
							$filenamePhotoFullPath          = $this->urlForCache.$owner."/".$id."/".$filenamePhoto;
							$filenamePreviewFullPath        = $this->urlForCache.$owner."/".$id."/".$filenamePreview;
							$filenamePhotoFullPathDir       = $this->dirForCache.$owner."/".$id."/".$filenamePhoto;
							$filenamePreviewFullPathDir     = $this->dirForCache.$owner."/".$id."/".$filenamePreview;
							// большие картинки
							if(!file_exists($filenamePhotoFullPathDir) and !empty($filenamePhotoFullPathDir)){
								file_put_contents($filenamePhotoFullPathDir, file_get_contents($value[$_vkpPhotoViewSize]));
							}else{
							// проверим не протух ли файл
								if(!empty($filenamePhotoFullPathDir)){
									if((time()-filemtime($filenamePhotoFullPathDir))>$this->vkpLifeTimeCaching*3600 and ($this->vkpLifeTimeCaching*1)>0){
										// файл сущеcтвует но время кеша вышло
										file_put_contents($filenamePhotoFullPathDir, file_get_contents($value[$_vkpPhotoViewSize]));
									}
								}
							}

							// миниатюры

							if(!file_exists($filenamePreviewFullPathDir) and !empty($filenamePreviewFullPathDir)){
								if($this->vkpPreviewType == 'square'){
									$this->image_crop (200,200, $this->dirForCache.$owner."/".$id."/", $value['pid'], $filenamePhotoFullPathDir );
								}else{
									echo $value[$_vkpPreviewSize]."<br>";
									file_put_contents($filenamePreviewFullPathDir, file_get_contents($value[$_vkpPreviewSize]));
								}
							}else{
							 // проверим не протух ли файл
								if(!empty($filenamePreviewFullPathDir)){
									if((time()-filemtime($filenamePreviewFullPathDir))>$this->vkpLifeTimeCaching*3600 and ($this->vkpLifeTimeCaching*1)>0){
										// файл сущеcтвует но время кеша вышло
										if($this->vkpPreviewType == 'square'){
											$this->image_crop (200,200, $this->dirForCache.$owner."/".$id."/", $value['pid'], $filenamePhotoFullPathDir );
										}else{
											file_put_contents($filenamePreviewFullPathDir, file_get_contents($value[$_vkpPreviewSize]));
										}
									}
								}
							}
						}


				}



		return $output;
	}
 /**

	//////////////////////////////////////////////////////////
	// функция отображения альбома, из кеша и из контакта
 **/
	public function show_album_from($atts,$from){


		//получение параметров и определение переменных
		extract(shortcode_atts(array(
			'id' => "no",
			'owner' => "no",
			'template' => $this->vkpTemplate,
			'viewer' => $this->vkpViewer,
			'sign' => $this->vkpShowSignatures,
			'count' => $this->vkpCountPhotos,
			'preview' => $this->vkpPreviewSize,
			'photo' => $this->vkpPhotoViewSize
		), $atts));


		$preview = trPictureSize($preview);
		$photo = trPictureSize($photo);

		if(!in_array($preview,$this->arrayPictureSize)){
			$preview = $this->vkpPreviewSize;
		}

		if(!in_array($photo,$this->arrayPictureSize)){
			$photo = $this->vkpPhotoViewSize;
		}

		$templateViewer = "";
		$output = "";

// проверяем а не с мобильного ли устройства зашел пользователь
// использовать специальный просмотрщик для мобильных платформ

//            if(wp_is_mobile()){
//
//               }else{
					// инициируем колорбокс
					if($viewer=='colorbox'){
						wp_enqueue_script( 'vkp_colorbox' );
						wp_enqueue_style( 'vkp_colorbox' );
						$templateViewer = ' class="vkpcolorbox"';
					}
					if($viewer=='swipebox'){
						wp_enqueue_script( 'vkp_swipebox' );
						wp_enqueue_style( 'vkp_swipebox' );
						$templateViewer = ' class="swipebox"';
					}

 //               }

// заголовок галереи показываем или описание?
		if($this->vkpShowTitle=='yes' or $this->vkpShowDescription=='yes'){

			// получаем альбом(файл описания) из кеша или из vk.com
			if($from == 'cache'){
				$album = @file_get_contents($this->dirForCache.$owner."/".$id."/description.album");
				$album = unserialize($album);
			}
			if($from == 'vk'){
				$album = $this->VKP->api('photos.getAlbums', array('access_token'=> $this->vkpAccessToken,'album_ids'=>$id,'owner_id'=>$owner));
			}


			if(is_array($album) && isset($album['response']) && isset($album['response']['items']) && is_array($album['response']['items']) && !empty($album['response']['items'][0])){
				if($this->vkpShowTitle=='yes'){
					$output.= "<h2>".$album['response']['items'][0]['title']."</h2>";
				}
				if($this->vkpShowDescription=='yes'){
					$output.=  $album['response']['items'][0]['description']."<br>";
				}
			}
		 }


// получим части шаблона
		$templateStyle  = @file_get_contents(VKP__PLUGIN_URL.'templates/'.$template."/style.html");
		$output.= str_replace("[[ID]]", $id, $templateStyle);
		$output = str_replace("[[DIRECTORY_PLUGIN]]",VKP__PLUGIN_URL, $output);
		$output.= "
			<script>
				function nextPage_".$id."(page){
					jQuery.post('".get_home_url()."', {
						vkp: 'next-page',
						from: '".$from."',
						id: '".$id."',
						owner: '".$owner."',
						template: '".$template."',
						sign: '".$sign."',
						viewer: '".$viewer."',
						page: page,
						count: '".$count."',
						vkpShowTitle: '".$this->vkpShowTitle."',
						vkpShowDescription: '".$this->vkpShowDescription."',
						templateViewer: '".$templateViewer."',
						vkpPreviewSize: '".$preview."',
						vkpPhotoViewSize: '".$photo."',
						more: '".__('more','vkp')."'
					},
					function(data){
						if (data) {
							document.getElementById('more".$owner.$id."').outerHTML = data;
						";

		if($template=='fresh'){
			// таблетка для шаблона FRESH (переинициализация после ajax)
			$output.= "
				var mosaic = jQuery( '.mosaicflow' ).mosaicflow( {
					itemSelector: '.mosaicflow__item',
					minItemWidth: 220
				});
				mosaic.mosaicflow('refill');
				";
		}

		if($viewer=='colorbox'){
			// таблетка для colorbox (переинициализация после ajax)
			$output.= "
				jQuery('.vkpcolorbox').colorbox({rel:'vkpcolorbox', slideshow:false});
			";
		}
		if($viewer=='swipebox'){
			// таблетка для swipebox
			$output.= "
			;( function( $ ) {
				jQuery( '.swipebox' ).swipebox(
					{
						useSVG : false,
						ideBarsDelay : 9000,
						useCSS : true
					}
					);

				} )( jQuery );
			";
		}

		$output.= "
					}
				}, 'html');
			}
			nextPage_".$id."(1);

			</script>
			";

		 $output.= "<div id='more".$owner.$id."'></div>";

		return $output;
	}

	//////////////////////////////////////////////////////////////////////////////////////////////////
	// функция создания миниатюр
	public function image_crop ($width = 200, $height = 200, $dir, $id, $file){

		$PrevFileName = $dir."thumb_".$id.".".pathinfo($file, PATHINFO_EXTENSION);

		// Получаем размеры сторон исходного изображения
		$size = getimagesize($file);

		// $size[0] ширина
		// $size[1] высота
		// Экиз-квадрат, вписываем его в область исходного изображения


				if ($size[0] > $size[1]) {
					/* ---- Если изображение горизонтальное ----- */
					$x_e = ceil(($size[0] - $size[1]) / 2);
					$y_e = 0;
					$mx = $size[1];
					$my = $size[1];
					$or = 'Гор';
				} else {
					/* ---- Если изображение вертикальное ----- */
					$x_e = 0;
					$y_e = ceil(($size[1] - $size[0]) / 6);  // разделим на 6 чтобы немного приподнять квадрат при кадрировании
					$mx = $size[0];
					$my = $size[0];
					$or = 'Верт';
				}

		// процесс ресайза, кропа и т.д...
		$image_p = imagecreatetruecolor($width, $height);
		$image = imagecreatefromjpeg($file);
		imagecopyresampled(
				$image_p,       // Ресурс нового изображения
				$image,         // Ресурс исходного изображения
				0, 0,           // Координаты (x,y) верхнего левого угла области в новом изображении
				$x_e, $y_e,     // Координаты (x,y) верхнего левого угла области копируемого исходного изображения
				$width,         // Ширина копируемой области
				$height,        // Высота копируемой области
				$mx,            // Ширина исходной копируемой области
				$my             // Высота исходной копируемой области
		);


		// Сохранение в файл
		imagejpeg($image_p, $PrevFileName, 80);
		imagedestroy($image_p);
		imagedestroy($image);
		return $PrevFileName;


	}
	///////////////////////////////////////////////////////////////////////////////////////////////////
	/* install actions (when activate first time) */
	static function install() {

	}


	/* uninstall hook */
	static function uninstall() {

	}
	//////////////////////////////////////////////////////////////////////////////////
	// функция подсчета размера директории
	public function dir_size($dir) {

		if(!file_exists($dir))return;

		if($this->vkpCalculateCache!='yes')return;

		$totalsize=0;

		if ($dirstream = @opendir($dir)) {
			while (false !== ($filename = readdir($dirstream))) {
			if ($filename!="." && $filename!=".."){
				if (is_file($dir."/".$filename))
					$totalsize+=filesize($dir."/".$filename);

				if (is_dir($dir."/".$filename))
					 $totalsize+=$this->dir_size($dir."/".$filename);
			}
			}
		}
		closedir($dirstream);
	return $totalsize;
	}


}
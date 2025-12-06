<?php
/*
Plugin Name: vk-photos
Plugin URI: http://photo-family.ru/vk-photos
Description: Photo gallery from vk.com
Author: volod1n <ivan.volodin@gmail.com>
Author URI: http://photo-family.ru
Version: 1.5
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

define( 'VKP__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'VKP__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

$upload_dir = wp_upload_dir();

load_plugin_textdomain("vkp", false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

@require_once( VKP__PLUGIN_DIR . 'inc/class.main.php' );

add_action( 'admin_init', 'VKPPhotosRegisterSettings' );


// зарегистрируем триггер
add_filter('query_vars','vkp_add_trigger');
add_action('template_redirect', 'vkp_next_page');


//////////////////////////////////////////////////////////
// поиcк ближайшей картинки
function get_photo_size($size,$value){
		$arrayPictureSizeDESC = array ('5' => 'photo_2560', '4' => 'photo_1280','3' => 'photo_807','2' => 'photo_604','1' => 'photo_130','0' => 'photo_75');
				$_key_photo = array_search($size, $arrayPictureSizeDESC );   // от этого ключа и ниже будем искать ближайшую годную миниатюру
				foreach ($arrayPictureSizeDESC as $mkey => $mvalue) {
						if($mkey<=$_key_photo){
								// проверим есть ли картинка
								if(isset($value[$mvalue])){
										// если есть картинка - покидаем цикл по массиву
										return $mvalue;
								}
						}
				}
				return false;
}

// перевод размеров старого api в новое

function trPictureSize($oldsize){
		$arSizes = array(
				'src_small' => 'photo_75',
				'src' => 'photo_130',
				'src_big' => 'photo_604',
				'src_xbig' => 'photo_807',
				'src_xxbig' => 'photo_1280',
				'src_xxxbig' => 'photo_2560',
				'photo_75' => 'photo_75',
				'photo_130' => 'photo_130',
				'photo_604' => 'photo_604',
				'photo_807' => 'photo_807',
				'photo_1280' => 'photo_1280',
				'photo_2560' => 'photo_2560',
			);

		return $arSizes[$oldsize];
}

////////////////////////////////////////////////////////////////////
// нормализация структуры фотографий из нового формата VK API в старый формат
function vkp_normalize_photos_response($photos){
	if(!is_array($photos) || !isset($photos['response']) || !is_array($photos['response']) || !isset($photos['response']['items'])){
		return array();
	}

	// Convert items to array if it's an object (new VK API format).
	$items = $photos['response']['items'];
	if(!is_array($items)){
		$items = (array)$items;
	}
	// Remove numeric string keys and reindex if needed (convert object-like array to regular array).
	$items = array_values($items);

	// Normalize photo structure: convert new format (sizes array) to old format (direct photo_* fields).
	foreach($items as $key => $photo){
		if(isset($photo['sizes']) && is_array($photo['sizes'])){
			// Map VK API size types to old format.
			$sizeMap = array(
				's' => 'photo_75',
				'm' => 'photo_130',
				'x' => 'photo_604',
				'y' => 'photo_807',
				'z' => 'photo_1280',
				'w' => 'photo_2560',
				'o' => 'photo_75',
				'p' => 'photo_130',
				'q' => 'photo_604',
				'r' => 'photo_807',
				'base' => 'photo_2560'
			);
			// Convert sizes array to direct fields.
			foreach($photo['sizes'] as $sizeItem){
				if(isset($sizeItem['type']) && isset($sizeItem['url'])){
					$oldKey = isset($sizeMap[$sizeItem['type']]) ? $sizeMap[$sizeItem['type']] : null;
					if($oldKey && !isset($items[$key][$oldKey])){
						$items[$key][$oldKey] = $sizeItem['url'];
					}
				}
			}
			// Also try to get the largest size from orig_photo if available.
			if(isset($photo['orig_photo']['url'])){
				$items[$key]['photo_2560'] = $photo['orig_photo']['url'];
			}
		}
	}

	return $items;
}

////////////////////////////////////////////////////////////////////
// триггер
function vkp_add_trigger($vars) {
		$vars[] = 'vkp';
		return $vars;
}

function vkp_next_page(){
	if(isset($_POST['vkp'])){
		if($_POST['vkp']=='next-page'){
			require_once( VKP__PLUGIN_DIR . 'inc/next-page.php' );
			exit();
		}
	}
}
////////////////////////////////////////////////////////////////////

// register_uninstall_hook( __FILE__, array('VKPhotos', 'uninstall'));
// register_activation_hook( __FILE__, array('VKPhotos', 'install') );


// инициализация
function VKPPhotosRegisterSettings(){

		// не удаление ли кеша ?
		if(is_admin() and isset($_GET['clearcache'])){
				$clearcache = explode("|",$_GET['clearcache']);
				if(isset($clearcache[0])){$clearcache_owner = ($clearcache[0]*1);}
				if(isset($clearcache[1])){$clearcache_id = ($clearcache[1]*1);}
				vkp_delete_cache($clearcache_owner,$clearcache_id);
		}

	register_setting( 'VKPPhotosSettingsGroup', 'vkpCountPhotos' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpAccaunts' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpAccaunts_type' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpEnableCaching' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpAccessToken' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpLifeTimeCaching' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpPreviewSize' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpPhotoViewSize' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpPreviewType' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpShowTitle' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpShowSignatures' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpTemplate' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpViewer' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpCalculateCache' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpShowDescription' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpMoreTitle' );




}

/////////////////////////////////////////////////////////////////////////////
// удаление кеша
function vkp_delete_cache($owner,$id){
		$upload_dir = wp_upload_dir();
		$dirForCache  = $upload_dir['basedir']."/vk-photos-cache/";
		if(file_exists($dirForCache.$owner.'/'.$id)){
				if(isset($id)){@unlink($dirForCache.$owner.'/album_'.$id.".cache");}
				@vkp_removeDir($dirForCache.$owner.'/'.$id);
		}
		wp_redirect($_SERVER['SCRIPT_NAME']."?page=vk-cache");
}

// удаление лиректории
function vkp_removeDir($path) {
		if (is_file($path)) {
				@unlink($path);
		} else {
				array_map('vkp_removeDir',glob($path.'/*')) == @rmdir($path);
		}
}

// регистрация библиотек
function vkp_scripts_register() {
		// Подключаем jQuery.
		wp_enqueue_script( 'jquery' );
		// colorbox
		wp_register_script( 'vkp_colorbox', VKP__PLUGIN_URL."js/jquery.colorbox-min.js");
		wp_register_style( 'vkp_colorbox', VKP__PLUGIN_URL."css/colorbox.css");
		// swipebox
		wp_register_script( 'vkp_swipebox', VKP__PLUGIN_URL."js/jquery.swipebox.min.js");
		wp_register_style( 'vkp_swipebox', VKP__PLUGIN_URL."css/swipebox.css");
}

add_action('wp_enqueue_scripts', 'vkp_scripts_register');

if (class_exists("VKPhotos")) {
		$module_obj = new VKPhotos();
}

if (isset($module_obj)) {


} // if (isset($module_obj))



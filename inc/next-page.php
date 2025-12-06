<?php

// получаем переменные

$sizeArray = array ('photo_2560','photo_1280','photo_807','photo_604','photo_130','photo_75');

$from               = ((isset($_POST['from']) and $_POST['from']=='cache') ? 'cache':'vk');
$id                 = $_POST['id']*1;
$owner              = $_POST['owner']*1;
$template           = (isset($_POST['template']) ? stripslashes(strip_tags($_POST['template'])):'light');
$viewer             = (isset($_POST['viewer']) ? stripslashes(strip_tags($_POST['viewer'])):'');
$sign               = ((isset($_POST['sign']) and $_POST['sign']=='yes') ? 'yes':'no');
$page               = (isset($_POST['page']) ? (int)$_POST['page']:1);
$count              = (isset($_POST['count']) ? (int)$_POST['count']:12);

$upload_dir         = wp_upload_dir();
$dirForCache        = $upload_dir['basedir']."/vk-photos-cache/";
$urlForCache        = $upload_dir['baseurl']."/vk-photos-cache/";
$directory_plugin   = VKP__PLUGIN_DIR;


$vkpShowTitle       = ((isset($_POST['vkpShowTitle']) and $_POST['vkpShowTitle']=='yes') ? 'yes':'no');
$vkpShowDescription = ((isset($_POST['vkpShowDescription']) and $_POST['vkpShowDescription']=='yes') ? 'yes':'no');

$templateViewer     = (isset($_POST['templateViewer']) ? stripslashes(strip_tags($_POST['templateViewer'])):'');
$vkpPreviewSize     = ((isset($_POST['vkpPreviewSize']) and in_array(trPictureSize($_POST['vkpPreviewSize']),$sizeArray)) ? stripslashes(strip_tags($_POST['vkpPreviewSize'])):'photo_130');
$vkpPhotoViewSize   = ((isset($_POST['vkpPhotoViewSize']) and in_array(trPictureSize($_POST['vkpPhotoViewSize']),$sizeArray)) ? stripslashes(strip_tags($_POST['vkpPhotoViewSize'])):'photo_807');
$more               = __('more','vkp');

$output             = "";
$ownerDirForCache   = $dirForCache.$owner."/";
$albumDirForCache   = $ownerDirForCache.$id."/";
$albumCache         = $ownerDirForCache."album_".$id.".cache";

$acces_token = get_option('vkpAccessToken');

// сделать верификацию переменных и отображение ошибок пользователю

// Убеждаемся, что функции доступны.
if (!function_exists('trPictureSize') || !function_exists('get_photo_size')) {
	require_once( VKP__PLUGIN_DIR . 'vk-photos.php' );
}

// Инициализируем класс VK API.
require_once( VKP__PLUGIN_DIR . 'api/vkapi.class.php' );
$VKP = new vkapi();

// получаем альбом из кеша или из vk.com
        if($from == 'cache'){
            $photos = @file_get_contents($albumCache);
            $photos = unserialize($photos);
        }
        if($from == 'vk'){
            $photos = $VKP->api('photos.get', array('album_id'=>$id,'owner_id'=>$owner, 'access_token'=>$acces_token));
        }


		if(!is_array($photos)) return;


// получим части шаблона

            $templateHeader = @file_get_contents($directory_plugin.'templates/'.$template."/header.html");
            $templateItem   = @file_get_contents($directory_plugin.'templates/'.$template."/item.html");
            $templateFooter = @file_get_contents($directory_plugin.'templates/'.$template."/footer.html");

// заменяем шаблонные вставки [[ID]],[[VIEWER]],[[DIRECTORY_PLUGIN]],[[PHOTO]],[[SIGNATURES]],[[PREVIEW]]

            $output.= str_replace("[[ID]]", $id, $templateHeader);
            $templateItem = str_replace("[[VIEWER]]", $templateViewer, $templateItem);
			$templateItem = str_replace("[[ID]]", $id, $templateItem);

			$allCount = count($photos['response']['items']);

			$photos = array_slice($photos['response']['items'],(($page-1)*$count),$count);

                foreach ($photos as $key => $value) {

                        $_vkpPreviewSize = get_photo_size($vkpPreviewSize,$value);
                        $_vkpPhotoViewSize = get_photo_size($vkpPhotoViewSize,$value);


                        if($_vkpPreviewSize!=false and $_vkpPhotoViewSize!=false){

                            if($from=='cache'){
                                $filenamePreview                = "thumb_".$value['id'].".".pathinfo($value[$_vkpPreviewSize], PATHINFO_EXTENSION);
                                $filenamePhoto                  = "photo_".$value['id'].".".pathinfo($value[$_vkpPhotoViewSize], PATHINFO_EXTENSION);
                                $filenamePhotoFullPath          = $urlForCache.$owner."/".$id."/".$filenamePhoto;
                                $filenamePreviewFullPath        = $urlForCache.$owner."/".$id."/".$filenamePreview;
                                $filenamePhotoFullPathDir       = $dirForCache.$owner."/".$id."/".$filenamePhoto;
                                $filenamePreviewFullPathDir     = $dirForCache.$owner."/".$id."/".$filenamePreview;

                                    // большие картинки
                                    if(file_exists($filenamePhotoFullPathDir)){
                                        $_bigPhoto = $filenamePhotoFullPath;
                                    }else{
                                        // попробуем взять в онлане
                                        $_bigPhoto = $value[$_vkpPhotoViewSize];
                                    }

                                    // превью
                                    if(file_exists($filenamePreviewFullPathDir)){
                                        $_smallPhoto = $filenamePreviewFullPath;
                                    }else{
                                        // попробуем взять в онлайне
                                        $_smallPhoto = $value[$_vkpPreviewSize];
                                    }
                            }else{
                                $_bigPhoto = $value[$_vkpPhotoViewSize];
                                $_smallPhoto = $value[$_vkpPreviewSize];
                            }



                                // заменяем значения в шаблоне и прибавляем к основному стеку $output

                                // data-title="My caption" - подпись для lightbox

                                $output_temp = str_replace('[[PHOTO]]', $_bigPhoto, $templateItem );

                                if($sign=='yes'){
                                    $output_temp = str_replace('[[SIGNATURES]]', strip_tags($value['text']) , $output_temp);
                                    // подпись для лайтбокса
                                    if($viewer=='colorbox' or $viewer=='swipebox'){
                                        $output_temp = str_replace('[[VIEWERSIGN]]', " title='".strip_tags($value['text'])."'", $output_temp);
                                    }
                                }else{

                                }

                                $output.= str_replace('[[PREVIEW]]', $_smallPhoto, $output_temp);
                        }

                }


                 // подчищаем остатки
                 $output = str_replace('[[VIEWER]]', '', str_replace('[[SIGNATURES]]', '', str_replace('[[VIEWERSIGN]]', "", $output)));
                 $output.= str_replace("[[ID]]", $id, $templateFooter);
				 $output.= "<div id='more".$owner.$id."'>";
				 if(ceil($allCount/$count)>$page){
				 	$output.= "<center><a href='javascript:void(0)' onclick='nextPage_".$id."(".($page+1).")'>".get_option('vkpMoreTitle')."</a></center>";
				 }

                 echo $output;

?>
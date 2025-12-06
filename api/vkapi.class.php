<?php

/**
 * class for vk.com social network
 *
 * @package server API methods
 * @link http://vk.com/developers.php
 * @autor Ivan Volodin
 * @version 1.0
 */

class vkapi {
	public $access_token;
	public $api_url;

	public function __construct($api_url = 'api.vk.com/method/') {
		if (!strstr($api_url, 'https://')) $api_url = 'https://'.$api_url;
		$this->api_url = $api_url;
	}

	public function api($method,$params=false) {
		if (!$params) $params = array();

			$params["v"] =  "5.199";

			ksort($params);
			$sig = '';
			foreach($params as $k=>$v) {
				$sig .= $k.'='.$v;
			}

		$query = $this->api_url.$method.'?'.$this->params($params);

		if (function_exists('curl_init')) {
			$ch = curl_init();
			@curl_setopt($ch,CURLOPT_URL,$query);
			@curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			@curl_setopt($ch,CURLOPT_TIMEOUT, 2);
			//задает время на соединение с сервером
			@curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 2);
			//я не скрипт, я браузер опера
			@curl_setopt($ch, CURLOPT_USERAGENT, 'Opera 10.00');
			//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			@curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
			@curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$res = @curl_exec($ch);
			@curl_close($ch);
			// контрольный выстрел в голову
			if(empty($res)){
				$res = @file_get_contents($query);
			}
		}else{
		   $res = @file_get_contents($query);
		}

		return @json_decode($res, true);
	}

	public function params($params) {
		$pice = array();
		foreach($params as $k=>$v) {
			$pice[] = $k.'='.urlencode($v);
		}
		return implode('&',$pice);
	}
}

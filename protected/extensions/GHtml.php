<?php
class GHtml extends CApplicationComponent {
	/**
	 * 判断是否是html格式代码
	 * @param $code
	 * @return int
	 */
	public static function isHtml($code) {
		// 有标签即认为是html
		return !!preg_match('/<(!|\/)?\w+(\s((.|\n)*?"\')?)?\s*>/',$code);
	}

	/**
	 * 检测富媒体格式
	 * @param $code
	 * @return null|string
	 */
	public static function richType($code){
		$type = null;
		if(strpos($code,'mraid.js')){
			$type = 'MRAID1.0';
			$mraid2 = array(
				'createCalendarEvent',
				'playVideo',
				'getCurrentPosition',
				'getDefaultPosition',
				'getMaxSize',
				'setResizeProperties',
				'storePicture',
				'getResizeProperties',
				'supports',
				'getScreenSize',
				'sizeChange'
			);
			foreach($mraid2 as $value){
				if(strpos($code,$value)) return 'MRAID2.0';
			}
			return $type;
		}
		//ORMMA 支持mraid1.0，但无需引入mraid.js
		if(strpos($code,'ormma') || strpos($code,'mraid')) return 'ORMMA';
		return $type;
	}

	/**
	 * 判断链接是否支持爬虫
	 * @param $url
	 * @return bool
	 */
	public static function supportCrawler($url){
		$htmlInstance = Http::factory(Http::TYPE_CURL);
		try{
			$respond = $htmlInstance->get($url, array(), array(
				'HTTP_USER_AGENT'=>'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'
			),'',30);
		}catch(Exception $e){
			return false;
		}

		return $respond ? true : false;
	}
}

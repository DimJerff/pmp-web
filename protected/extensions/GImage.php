<?php
class GImage extends CApplicationComponent {
	static private $_AllowType = array('*','jpg', 'jpeg', 'gif', 'png');
	/*
	 * 判断是否是图片
	 * 根据文件扩展名判断
	 */
	static public function isImage($filename){
		$ext = strtolower(trim(substr(strrchr($filename, '.'), 1)));
		return array_search($ext,self::$_AllowType);
	}
	/*
	 * 创建缩略图，根据服务器上所支持的创建缩略图
	 * option:4、最佳缩放模式（自动判断缩放），8、宽度最佳缩放，16、高度最佳缩放
	 * cutmode:0、默认模式（不裁剪），1、左或上裁剪模式，2、中间裁剪模式，3、右或下裁剪模式
	 */
	static public function thumb($source_file,$target_file,$width,$height,$option=4,$cutmode=0){
		$opnotkeepscale = 4;
		$opbestresizew = 8;
		$opbestresizeh = 16;
		$startx = 0;
		$starty = 0;
		$dstW = intval($width);
		$dstH = intval($height);
		if (!file_exists($source_file)) return false;

		$data = self::getImageInfo($source_file);
		if(empty($data) || !is_array($data)) return '';


		$func_create = "imagecreatefrom".$data['type'];
		if (!function_exists ($func_create)) return '';

		$func_output = 'image'.$data['type'];
		if (!function_exists ($func_output)) return '';

		$im = $func_create($source_file);
		$srcW = $data[0];
		$srcH = $data[1];
		$srcX = 0;
		$srcY = 0;
		$dstX = 0;
		$dstY = 0;

		//SIZE
		if($srcW < $dstW) $dstW = $srcW;
		if($srcH < $dstH) $dstH = $srcH;

		if ($option & $opbestresizew) {
			$dstH = round($dstW * $srcH / $srcW);
		}
		if ($option & $opbestresizeh) {
			$dstW = round($dstH * $srcW / $srcH);
		}

		$fdstW = $dstW;
		$fdstH = $dstH;

		if ($cutmode != 0) {
			$srcW -= $startx;
			$srcH -= $starty;
			if ($srcW*$dstH > $srcH*$dstW) {
				$testW = round($dstW * $srcH / $dstH);
				$testH = $srcH;
			} else {
				$testH = round($dstH * $srcW / $dstW);
				$testW = $srcW;
			}
			switch ($cutmode) {
				case 1: $srcX = 0; $srcY = 0;
					break;
				case 2: $srcX = round(($srcW - $testW) / 2);
					$srcY = round(($srcH - $testH) / 2);
					break;
				case 3: $srcX = $srcW - $testW;
					$srcY = $srcH - $testH;
					break;
			}
			$srcW = $testW;
			$srcH = $testH;
			$srcX += $startx;
			$srcY += $starty;
		} else {
			if (!($option & $opnotkeepscale)) {
				if ($srcW*$dstH > $srcH*$dstW) {
					$fdstH = round($srcH*$dstW/$srcW);
					$dstY = floor(($dstH-$fdstH)/2);
					$fdstW = $dstW;
				} else {
					$fdstW = round($srcW*$dstH/$srcH);
					$dstX = floor(($dstW-$fdstW)/2);
					$fdstH = $dstH;
				}
				$dstX=($dstX<0)?0:$dstX;
				$dstY=($dstX<0)?0:$dstY;
				$dstX=($dstX>($dstW/2))?floor($dstW/2):$dstX;
				$dstY=($dstY>($dstH/2))?floor($dstH/2):$dstY;
			}
		}
		//echo $dstW.'<br/>'.$dstH.'<br />'.$srcX.'<br/>'.$srcY.'<br/>'.$fdstW.'<br/>'.$fdstH.'<br />'.$srcW.'<br/>'.$srcH;
		if(function_exists("imagecopyresampled") and function_exists("imagecreatetruecolor")) {
			$func_create = "imagecreatetruecolor";
			$func_resize = "imagecopyresampled";
		} elseif (function_exists("imagecreate") and function_exists("imagecopyresized")) {
			$func_create = "imagecreate";
			$func_resize = "imagecopyresized";
		} else {
			return '';
		}
		$newim = $func_create($dstW,$dstH);
		$black = imagecolorallocate($newim, 0,0,0);
		$back = imagecolortransparent($newim, $black);
		imagefilledrectangle($newim,0,0,$dstW,$dstH,$black);
		$func_resize($newim,$im,$dstX,$dstY,$srcX,$srcY,$fdstW,$fdstH,$srcW,$srcH);
		$func_output($newim, $target_file);
		imagedestroy($im);
		imagedestroy($newim);
		if(!file_exists($target_file)) return '';
		return true;
	}
	/*
	 * 重置图片大小
	 * mode:0、自动判断大小，调整到预期大小，1、放大原图：右下留空，2、放大原图：中间居中，3、放大原图：左上留空
	 */
	static public function resize($source_file,$target_file,$width,$height,$mode){
		$dstW = intval($width);
		$dstH = intval($height);
		$info = self::getImageInfo($source_file);
		if(!empty($info[0])){
			$srcW = $info[0];
			$srcH = $info[1];
		} else{
			return false;
		}
		if(empty($mode)){
			//如果原图过大，则生成缩略图
			if($srcW > $dstW && $srcH > $dstH) return self::thumb($source_file,$target_file,$width,$height,4,$mode);
			$mode = 2;
		}
		$srcX = 0;
		$srcY = 0;
		$dstX = 0;
		$dstY = 0;
		switch ($mode) {
			case 1: $dstX = 0; $dstY = 0;
				break;
			case 2: $dstX = round(($dstW - $srcW) / 2);
				$dstY = round(($dstH - $srcH) / 2);
				break;
			case 3: $dstX = $dstW - $srcW;
				$dstY = $dstH - $srcH;
				break;
		}
		$func_create = "imagecreatefrom".$info['type'];
		if (!function_exists ($func_create)) return '';

		$func_output = 'image'.$info['type'];
		if (!function_exists ($func_output)) return '';
		$im = $func_create($source_file);

		if(function_exists("imagecopyresampled") and function_exists("imagecreatetruecolor")) {
			$func_create = "imagecreatetruecolor";
			$func_resize = "imagecopyresampled";
		} elseif (function_exists("imagecreate") and function_exists("imagecopyresized")) {
			$func_create = "imagecreate";
			$func_resize = "imagecopyresized";
		} else {
			return '';
		}

		$newim = $func_create($dstW,$dstH);
		$black = imagecolorallocate($newim,0,0,0);
		$back = imagecolortransparent($newim, $black);
		imagefilledrectangle($newim,0,0,$dstW,$dstH,imagecolorallocate($newim,255,255,255));
		//print_r(array($dstX,$dstY,$srcX,$srcY,$dstW,$dstH,$srcW,$srcH));
		//exit;
		$func_resize($newim,$im,$dstX,$dstY,$srcX,$srcY,$srcW,$srcH,$srcW,$srcH);
		$func_output($newim, $target_file);
		imagedestroy($im);
		imagedestroy($newim);
		if(!file_exists($target_file)) return '';
		return true;
	}
	/*
	 * 添加水印
	 *
	 * cutmode:0、裁剪水印，1、放大原图：右下留空，2、放大原图：中间居中，3、放大原图：左上留空
	 */
	static public function water($source_file,$target_file,$water,$pos=0,$pct=80,$cutmode=0){
		// 加载水印图片
		$info = self::getImageInfo($water);
		if(!empty($info[0])){
			$water_w = $info[0];
			$water_h = $info[1];
			$type = $info['type'];
			$fun  = 'imagecreatefrom'.$type;
			$waterimg = $fun($water);
		} else{
			return false;
		}
		// 加载背景图片
		$info = self::getImageInfo($source_file);

		if(!empty($info[0])){
			$old_w = $info[0];
			$old_h = $info[1];
			$type  = $info['type'];
			$fun   = 'imagecreatefrom'.$type;
			$source_file_r = $fun($source_file);
		} else{
			return false;
		}
		if($cutmode == 0){
			// 剪切水印
			$water_w >$old_w && $water_w = $old_w;
			$water_h >$old_h && $water_h = $old_h;
		}else{
			//原图比水印小，放大原图
			if($water_w > $old_w && $water_h > $old_h){
				self::resize($source_file,$source_file,$water_w,$water_h,$cutmode);
				return self::water($source_file,$target_file,$water,$pos,$pct,$cutmode);
			}
		}
		// 水印位置
		switch($pos){
			case 0://随机
				$posX = rand(0,($old_w - $water_w));
				$posY = rand(0,($old_h - $water_h));
				break;
			case 1://1为顶端居左
				$posX = 0;
				$posY = 0;
				break;
			case 2://2为顶端居中
				$posX = ($old_w - $water_w) / 2;
				$posY = 0;
				break;
			case 3://3为顶端居右
				$posX = $old_w - $water_w;
				$posY = 0;
				break;
			case 4://4为中部居左
				$posX = 0;
				$posY = ($old_h - $water_h) / 2;
				break;
			case 5://5为中部居中
				$posX = ($old_w - $water_w) / 2;
				$posY = ($old_h - $water_h) / 2;
				break;
			case 6://6为中部居右
				$posX = $old_w - $water_w;
				$posY = ($old_h - $water_h) / 2;
				break;
			case 7://7为底端居左
				$posX = 0;
				$posY = $old_h - $water_h;
				break;
			case 8://8为底端居中
				$posX = ($old_w - $water_w) / 2;
				$posY = $old_h - $water_h;
				break;
			case 9://9为底端居右
				$posX = $old_w - $water_w;
				$posY = $old_h - $water_h;
				break;
			default: //随机
				$posX = rand(0,($old_w - $water_w));
				$posY = rand(0,($old_h - $water_h));
				break;
		}
		// 设定图像的混色模式
		imagealphablending($source_file_r, true);
		// 添加水印
		self::imagecopymerge_alpha($source_file_r, $waterimg, $posX, $posY, 0, 0, $water_w,$water_h,$pct);
		$fun = 'image'.$type;
		$fun($source_file_r, $target_file);
		imagedestroy($source_file_r);
		imagedestroy($waterimg);
		if(!file_exists($target_file)) return '';
		return $target_file;
	}

	/*
	 * 提供透明度的拷贝
	 */
	static public function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
		$opacity=$pct;

		// creating a cut resource
		$cut = imagecreatetruecolor($src_w, $src_h);
		// copying that section of the background to the cut
		imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
		// inverting the opacity
		$opacity = 100 - $opacity;

		// placing the watermark now
		imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
		imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity);
	}

	// imageInfo
	static public function getImageInfo($img){
		$info = @getimagesize($img);
		$extArr=array(1=>'gif','2' =>'jpg','3'=>'png');
		$extStr=$extArr[$info[2]];
		if($extStr=='jpg') $extStr='jpeg';
		$info['ext']=$info['type']=$extStr;
		$info['size'] = @filesize($img);
		return $info;
	}
}
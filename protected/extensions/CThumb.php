<?php
class CThumb extends CApplicationComponent {
	public static function resizeImage($im, $maxwidth, $maxheight, $name,$filetype) {
		$pic_width = imagesx($im);
		$pic_height = imagesy($im);

		if (($maxwidth && $pic_width > $maxwidth) || ($maxheight && $pic_height > $maxheight)) {
			if ($maxwidth && $pic_width > $maxwidth) {
				$widthratio = $maxwidth / $pic_width;
				$resizewidth_tag = true;
			}
			
			if ($maxheight && $pic_height > $maxheight) {
				$heightratio = $maxheight / $pic_height;
				$resizeheight_tag = true;
			}
			
			if ($resizewidth_tag && $resizeheight_tag) {
				$ratio = $widthratio < $heightratio ? $widthratio : $heightratio;
			}
			
			if ($resizewidth_tag && !$resizeheight_tag)
				$ratio = $widthratio;
			if ($resizeheight_tag && !$resizewidth_tag)
				$ratio = $heightratio;

			$newwidth = $pic_width * $ratio;
			$newheight = $pic_height * $ratio;

			$createFunction = function_exists('imagecopyresampled') ? 'imagecreatetruecolor' : 'imagecreate';
			$copyFunction = function_exists('imagecopyresampled') ? 'imagecopyresampled' : 'imagecopyresized';
			$newim = $createFunction($newwidth, $newheight);
			$copyFunction($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $pic_width, $pic_height);
			
			imagejpeg($newim, $name);
			imagedestroy($newim);
		} else {
			$name = $name . $filetype;
			imagejpeg($im, $name);
		}
	}
}

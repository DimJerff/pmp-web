<?php
function smarty_modifier_filesize($size)
{
	$size = (int)$size;
	$unit = 'B';
	if($size >= 1024*1024) {
		$size = ($size/1024/1024) . 'MB';
	}elseif($size >= 1024) {
		$size = ($size/1024) . 'KB';
	}else{
		$size .= 'B';
	}
	return $size;
}

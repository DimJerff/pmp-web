<?php
function smarty_modifier_attach_url($url)
{
	$prefix = Yii::app()->params['uploadUrl'];
	$relationDir = '/upload/';
	$url = trim($url);
	if(empty($url)) return '';
	if(strpos($url, 'http') !== false) return $url;
    return $relationDir . $url;
}

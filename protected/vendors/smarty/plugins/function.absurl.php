<?php
function smarty_function_absurl($params, $template)
{
	$route = $params['route'];
	unset($params['route']);
	return Yii::app()->controller->createAbsoluteUrl($route, $params);
}

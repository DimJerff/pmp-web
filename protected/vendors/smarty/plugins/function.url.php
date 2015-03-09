<?php
function smarty_function_url($params, $template)
{
	$route = $params['route'];
	unset($params['route']);
	return Yii::app()->controller->createUrl($route, $params);
}

<?php
return array(
	/* 上传路径 */
	'uploadPath' => dirname(__FILE__) . '/../../',
	/* 附件URL */
	'uploadUrl' => '/',
	/* 附件规则 */
	'uploadRuleType' => array(
		'img' => 'application.validators.ImageValidator',
	),
	/* 附件类型 */
	'uploadExtTypes' => array(
		'jpg' => array('image/jpeg,image/jpg,image/pjpeg', 'jpg,jpeg,jpe'),
		'gif' => 'image/gif',
		'png' => 'image/png,image/x-png',
	),
	/* 上传限制 */
	'uploadLimit' => array(

	),
);

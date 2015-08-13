<?php
define("YII_CONSOLE", true);
return CMap::mergeArray(
	require dirname(__FILE__).DIRECTORY_SEPARATOR . 'main.php',
	array(
		'components' => array(
			'mailer' => array(
				'class' => 'application.extensions.EMailer',
				'config' => array(
					'Host'=>'smtp.exmail.qq.com',
					'SMTPAuth'=>true,
					'CharSet'=>'utf-8',
					'Username'=>'dsp-admin@limei.com',
					'Password'=>'uNQ80NCfGBYa',
					'From'=>'dsp-admin@limei.com',
					'FromName'=>'力美广告'
				),
				/* 异常或错误，发送邮件：注释 */
				'exceptionEmail' => array('gaojie@limei.com', ),
			),
		),
		'params'=>array(

		),
	)
);
<?php
$__curDir = dirname(__FILE__).DIRECTORY_SEPARATOR;
return array(
	'basePath' => $__curDir.'..',
	'name' => 'Limei Private Marketing Platform',
	'language' => 'zh_cn',
	'preload' => array('log',),
	'timeZone' => 'PRC',
	'import' => array(
		'application.models.*',
		'application.components.*',
		'application.extensions.*',
		'application.vendors.phpexcel.PHPExcel',
	),
	'modules' => array(
		'develop',
		'operate',
		'gii' => array(
			'class' => 'system.gii.GiiModule',
			'password' => '123',
			'ipFilters' => array('127.0.0.1', '192.168.2.106'),
		),
	),
	
	'components' => array(
		'session' => array(
			'timeout' => 3600,
		),
		'db' => require $__curDir.'db.php',
		'urlManager' => array(
            'class'  => 'CUrlManager',
			'urlFormat' => 'path',
			'urlSuffix' => '.html',
			/* 不显示脚本名 */
			'showScriptName' => false,
			'rules' => array(
				'<id:[^\/]{6,}>' => array('pc/shortUrl/index', 'urlSuffix' => false,),
			),
		),
		'user' => array(
			'class' => 'UserInstance',
			/* 没登陆跳到指定地址 */
			'loginUrl' => array('develop/user/login'),
			'returnUrl' => array('site/index'),
			/* auto login cookie encrypt keyt */
			'autoLoginEncryptKeyt' => '2194823(*@(*$RHN}{>f2\'33;',
			/* auto login cookie name */
			'autoLoginCookieName' => 'uauto',
			/* 未登陆返回的内容 */
			'loginRequiredAjaxResponse' => '["error",{"code":401}]',
		),
		'errorHandler' => array(
			'errorAction' => 'site/error',
		),
		'log' => array(
			'class' => 'CLogRouter',
			'routes' => array(
// 				array(
// 					'class'=>'CWebLogRoute',
// 					'levels'=>'trace, info, error, warning',
// 				),
				 array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),
		/* Smarty的配置项 */
		'smarty' => array(
			'class'=>'application.extensions.SmartyViewRender',    
			'config' => array (    
				'left_delimiter' => "<{",
				'right_delimiter' => "}>",
				'debugging' => false,
				'caching' => false,
				'cache_lifetime' => 3600,
			),
			/* 默认assign进模板的数据 */
			'assignConfig' => array(
				'versionCode' => '20140805',
			),
		),
		'authManager' => array(
			'class' => 'CDbAuthManager',
			'defaultRoles'=>array('authenticated', 'guest'),
			
		),
		'cache' => array(
			'class' => 'system.caching.C'.(function_exists('apc_store') ? 'Apc':'File').'Cache',
		)
	),
	
	'params' => CMap::mergeArray(
		array(
			/* 注册cc邮箱 */
			'registerCCEmail' => 'sunrui@limei.com',
			/* 默认角色ID */
			'defaultRoleId' => 0,
			/* 支持的语言列表 */
			'languageList' => array(
				'pc' => array(
					'zh_cn',
				),
			),
			/* 模板内容，目前主要是邮件内容 */
			'template' => require $__curDir.'template.php',
			/* 默认的解密密钥 */
			'decodeEncodeKey' => "dsp.corp.limei.com",
			/* 默认邮箱 */
			'defaultEmail' => 'support@service.limei.com',

		),array()
	),
);
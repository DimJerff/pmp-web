<?php
$__curDir = dirname(__FILE__).DIRECTORY_SEPARATOR;
return CMap::mergeArray(
    array(
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
        /* 获取组件配置信息 */
        'components' => array(
            'session' => array(
                'timeout' => 3600,
            ),
            /* url 验证规则 */
            'urlManager' => array(
                'class'  => 'CUrlManager',
                'urlFormat' => 'path',
                'urlSuffix' => '.html',
                /* 不显示脚本名 */
                'showScriptName' => false,
                'rules' => array(
                    '<id:[^\/]{6,}>' => array('develop/site/index', 'urlSuffix' => false,),
                ),
            ),
            'user' => array(
                'class' => 'UserInstance',
                /* 没登陆跳到指定地址 */
                'loginUrl' => array('site/login'),
                'returnUrl' => array('site/index'),
                /* 未登陆返回的内容 */
                'loginRequiredAjaxResponse' => '["error",{"code":401}]',
            ),
            'errorHandler' => array(
                'errorAction' => 'site/error',
            ),
            /* 日志 */
            'log' => array(
                'class' => 'CLogRouter',
            ),
            /* Smarty的配置项 */
            'smarty' => array(
                //指向 protected/extensions/SmartyViewRender.php 类文件
                'class'=>'application.extensions.SmartyViewRender',
                //配置信息
                'config' => array (
                    'left_delimiter' => "<{",
                    'right_delimiter' => "}>",
                    'debugging' => false,
                    'caching' => false,
                    'cache_lifetime' => 3600,
                ),
            ),
            'authManager' => array(
                'class' => 'CDbAuthManager',
                'defaultRoles'=>array('authenticated', 'guest'),

            ),
            'cache' => array(
                'class' => 'system.caching.C'.(function_exists('apc_store') ? 'Apc':'File').'Cache',
            ),
            'jump'=>array(
                'class'=>'ext.jumpage.jumpage',
            ),
        ),

        'params' => CMap::mergeArray(
            array(
                /* 注册cc邮箱 */
                'registerCCEmail' => 'sunrui@limei.com',
                /* 默认角色ID */
                'defaultRoleId' => 0,
                /* 支持的语言列表 */
                'languageList' => array(
                    'develop' => array(
                        'zh_cn',
                    ),
                    'operate' => array(
                        'zh_cn',
                    ),
                ),
                /* 模板内容，目前主要是邮件内容 */
                'template' => require $__curDir.'template.php',
                /* 默认的解密密钥 */
                'decodeEncodeKey' => "dsp.corp.limei.com",
                /* 默认邮箱 */
                'defaultEmail' => 'support@service.limei.com',
                // 设备分辨率
                'deviceDpi' => array(
                    '1' => array(
                        'zhName' => '智能手机',
                        'enName' => 'phone',
                        'type'   => array(
                            array(300, 250, '300x250'),
                            array(320, 50, '320x50'),
                            array(320, 480, '320x480'),
                            array(480, 320, '480x320'),
                            array(400, 300, '插屏'),
                            array(-1, -1, '全屏'),
                        ),
                    ),
                    '2' => array(
                        'zhName' => '平板电脑',
                        'enName' => 'pad',
                        'type'   => array(
                            array(300, 250, '300x250'),
                            array(320, 50, '320x50'),
                            array(728, 90, '728x90'),
                            array(768, 1024, '768x1024'),
                            array(1024, 768, '1024x768'),
                            array(400, 300, '插屏'),
                            array(-1, -1, '全屏'),
                        ),
                    ),
                ),
                //广告形式
                //'adtype'=>array(1=>"Banner", 2=>"插屏广告", /* 3=>"扩展式广告",*/ 4=>"全屏广告", 5=>"文字链广告", 6=>"视频广告", 7=>"原生广告", /*8=>"积分墙",*/ 9=>"开屏广告")
                'adtype'=>array(1=>'banner',2=>'插屏广告'),
            ),
            // 文件上传配置
            require $__curDir.'upload.php'
        ),
    ),
    // 运维定制配置文件
    require $__curDir . 'deployment.php'
);
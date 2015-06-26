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
        'components' => array(
            'session' => array(
                'timeout' => 3600,
            ),
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
                'loginUrl' => array('develop/user/login'),
                'returnUrl' => array('site/index'),
                /* 未登陆返回的内容 */
                'loginRequiredAjaxResponse' => '["error",{"code":401}]',
            ),
            'errorHandler' => array(
                'errorAction' => 'site/error',
            ),
            'log' => array(
                'class' => 'CLogRouter',
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
                // sdk下载地址
                'sdkLink' => array(
                    'android' => 'http://android.cc',
                    'ios'     => 'http://ios.cc',
                ),
            ),
            // 文件上传配置
            require $__curDir.'upload.php'
        ),
    ),
    // 运维定制配置文件
    require $__curDir . 'deployment.php'
);
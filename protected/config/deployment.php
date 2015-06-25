<?php
/**
 * 运维定制配置文件
 * 线上线下不一致的配置项
 * Date: 15-6-24
 * Time: 下午12:07
 */
return array(
    'modules' => array(
        'develop',
        'operate',
        'gii' => array(
            'class' => 'system.gii.GiiModule',
            'password' => '123',
            'ipFilters' => array('127.0.0.1', '192.168.168.145'),
        ),
    ),
    'components' => array(
        'db' => array(
            'connectionString' => 'mysql:dbname=cheetahx;host=dsp.corp.limei.com',
            'emulatePrepare' => true,
            'username' => 'bunny',
            'password' => 'DDsMKoB3hLfV',
            'charset' => 'utf8',
            /* 表结构缓存有效期， 单位：秒  */
            'schemaCachingDuration' => 0,
            /* 表前缀 */
            'tablePrefix' => 'c_',
        ),
        'db2' => array(
            'class'            => 'CDbConnection' ,
            'connectionString' => 'mysql:dbname=bunny;host=dsp.corp.limei.com',
            'emulatePrepare'   => true,
            'username'         => 'bunny',
            'password'         => 'DDsMKoB3hLfV',
            'charset'          => 'utf8',
            /* 表结构缓存有效期， 单位：秒  */
            'schemaCachingDuration' => 0,
            /* 表前缀 */
            'tablePrefix'      => 'b_',
        ),
        'user' => array(
            /* auto login cookie encrypt keyt */
            'autoLoginEncryptKeyt' => '2194823(*@(*$RHN}{>f2\'33;',
            /* auto login cookie name */
            'autoLoginCookieName' => 'uauto',
        ),
        'log' => array(
            'routes' => array(
//				array(
//					'class'=>'CWebLogRoute',
//					'levels'=>'trace, info, error, warning',
//				),
                ///*
                array(
                    'class'=>'CFileLogRoute',
                    'levels'=>'error, warning',
                ),
                //*/
            ),
        ),
        'smarty' => array(
            /* 默认assign进模板的数据 */
            'assignConfig' => array(
                'versionCode' => '20140805',
            ),
        ),
    ),
);
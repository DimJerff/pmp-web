<?php
//这是练习测试
//这是练习测试
//这是练习测试
//这是练习测试
//这是练习测试
date_default_timezone_set('PRC');
ini_set('display_errors',1);
error_reporting(E_WARNING | E_ERROR);
// change the following paths if necessary
$yii=dirname(__FILE__).'/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

require_once($yii);
// 根据服务器情况加载调试类函数
if (getenv('DEPLOYMENT') == 'localhost') { require_once('./protected/functions.php'); }
Yii::createWebApplication($config)->run();
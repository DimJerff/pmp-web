<?php
return array(
	/* 上传路径 */
	'uploadPath' => dirname(__FILE__) . '/../../',
    'uploadDir' => 'upload',
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
	// 上传限制配置信息
	'uploadLimit' => array(
        // 应用
        'media' => array(
            'ruleType' => 'img',
            'extTypes' => 'jpg, png, gif',
            'maxSize' => 1024*1024*10, /* 10M */
            'urlPath' => 'media',
            'widthHeights' => array(
                '200x200',
            ),

            /* 保存缩略图 */
            'thumbWidth' => 100,
            'thumbHeight' => 100,
        ),
        /* 营业执照 */
        'businessLicense' => array(
            'ruleType' => 'img',
            'extTypes' => 'jpg, png, gif',
            'maxSize' => 1024*1024*10, /* 10M */
            'urlPath' => 'profile/business_license',
            /* 保存缩略图 */
            'thumbWidth' => 100,
            'thumbHeight' => 100,
        ),
        /* 身份证 */
        'identityCard' => array(
            'ruleType' => 'img',
            'extTypes' => 'jpg, png, gif',
            'maxSize' => 1024*1024*10, /* 10M */
            'urlPath' => 'profile/identity_card',
            /* 保存缩略图 */
            'thumbWidth' => 100,
            'thumbHeight' => 100,
        ),
	),
);

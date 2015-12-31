-- 设定sql的字符编码
SET character_set_client = utf8;
SET character_set_connection = utf8;
use cheetahx;

-- 关闭自动提交，开启事务
SET AUTOCOMMIT = 0;

-- 审计表
RENAME TABLE `b_operation_log` TO `b_operation_log_bk`;
DROP TABLE IF EXISTS `b_operation_log`;
CREATE TABLE `b_operation_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '审计ID',
  `userId` int(11) unsigned NOT NULL COMMENT '操作用户ID',
  `model` varchar(255) NOT NULL COMMENT '操作表名',
  `recordId` int(11) unsigned NOT NULL COMMENT 'model对应的记录ID',
  `url` varchar(255) NOT NULL COMMENT '操作执行的url',
  `data` text NOT NULL COMMENT 'post提交的数据',
  `result` text NOT NULL COMMENT '记录到model中的值',
  `createTime` int(11) NOT NULL COMMENT '操作时间',
  `ip` varchar(255) NOT NULL COMMENT '操作的IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 提交所有sql，如果失败，则全部失败
commit;
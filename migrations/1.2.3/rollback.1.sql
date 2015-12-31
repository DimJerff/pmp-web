-- 设定sql的字符编码
SET character_set_client = utf8;
SET character_set_connection = utf8;
use cheetahx;

-- 关闭自动提交，开启事务
SET AUTOCOMMIT = 0;

-- 审计表
DROP TABLE IF EXISTS `b_operation_log`;
RENAME TABLE `b_operation_log_bk` TO `b_operation_log`;

-- 提交所有sql，如果失败，则全部失败
commit;
-- 设定sql的字符编码
SET character_set_client = utf8;
SET character_set_connection = utf8;
use cheetahx;

-- 关闭自动提交，开启事务
SET AUTOCOMMIT = 0;

DELETE FROM `c_base_operation_object` WHERE id = 6;


-- 提交所有sql，如果失败，则全部失败
commit;
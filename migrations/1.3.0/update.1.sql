-- 设定sql的字符编码
SET character_set_client = utf8;
SET character_set_connection = utf8;
use cheetahx;

-- 关闭自动提交，开启事务
SET AUTOCOMMIT = 0;

-- 媒体添加 接入方式.结算方式.固定价.媒体分成
ALTER TABLE `c_media` ADD COLUMN `sdkType`  tinyint(3) UNSIGNED NOT NULL DEFAULT 1  COMMENT '接入方式:  1:SDK/JS;  2:S2S' AFTER `os`;
ALTER TABLE `c_media` ADD COLUMN `payType`  smallint(3) NOT NULL DEFAULT -1 COMMENT '结算方式：pay type ,不启用 = -1, CPM = 1, CPC = 2, CPD = 3, SHARING = 101,agreement = 201' AFTER `appBundle`;
ALTER TABLE `c_media` ADD COLUMN `mediaPrice`  int(11) UNSIGNED NOT NULL DEFAULT 0  COMMENT '固定价: price when paytype is CPM/CPC/CPD, nonsense when paytype is SHARING' AFTER `payType`;
ALTER TABLE `c_media` ADD COLUMN `mediaSharingRate`  float(10,2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '媒体分成: sharing rate with media side, make sense when paytype is SHARING' AFTER `mediaPrice`;

-- 媒体添加 标识位.结算方式.固定价.媒体分成
ALTER TABLE `c_media_adslot` ADD COLUMN `relationId`  varchar(64) UNSIGNED NOT NULL DEFAULT '' COMMENT '标识位: 暂无' AFTER `developId`;
ALTER TABLE `c_media_adslot` ADD COLUMN `payType`  smallint(3)  NOT NULL DEFAULT -1 COMMENT '结算方式：pay type ,不启用 = -1, CPM = 1, CPC = 2, CPD = 3, SHARING = 101,agreement = 201' AFTER `relationId`;
ALTER TABLE `c_media_adslot` ADD COLUMN `mediaPrice`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '固定价: price when paytype is CPM/CPC/CPD, nonsense when paytype is SHARING' AFTER `payType`;
ALTER TABLE `c_media_adslot` ADD COLUMN `mediaSharingRate`  float(10,2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '媒体分成: sharing rate with media side, make sense when paytype is SHARING' AFTER `mediaPrice`;

-- 媒体添加 接入方式.结算方式.固定价.售出底价开关
ALTER TABLE `c_deal` ADD COLUMN `payType`  smallint(3) NOT NULL DEFAULT -1 COMMENT '结算方式：pay type ,不启用 = -1, CPM = 1, CPC = 2, CPD = 3, SHARING = 101,agreement = 201' AFTER `dealType`;
ALTER TABLE `c_deal` ADD COLUMN `mediaPrice`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '固定价: price when paytype is CPM/CPC/CPD, nonsense when paytype is SHARING' AFTER `payType`;
ALTER TABLE `c_deal` ADD COLUMN `mediaSharingRate`  float(10,2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '媒体分成: sharing rate with media side, make sense when paytype is SHARING' AFTER `mediaPrice`;
ALTER TABLE `c_deal` ADD COLUMN `bidStrategy `  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '售出底价开关: 固定价=1，规则=2' AFTER `wseat`;

-- 媒体_广告_交易关系表添加 供应商ID
ALTER TABLE `c_media_adslot_deal` ADD COLUMN `company_id`  INT(11)  UNSIGNED  NOT NULL  COMMENT '供应商ID' AFTER `dealId`;

-- 媒体_广告_交易关系表更改 状态status
ALTER TABLE `c_media_adslot_deal` MODIFY COLUMN `status`  tinyint(1)   NOT NULL DEFAULT 1 COMMENT '状态: 1:运行; 2:停止;' AFTER `companyId`;

-- 供应商添加 接入方式.结算方式.固定价.媒体分成
ALTER TABLE `c_company` ADD COLUMN `sdkType`  VARCHAR(10) NOT NULL DEFAULT '1' COMMENT '接入方式:  1:SDK/JS;  2:S2S' AFTER `address`;
ALTER TABLE `c_company` ADD COLUMN `payType`  smallint(3) NOT NULL DEFAULT -1 COMMENT '结算方式：pay type ,不启用 = -1, CPM = 1, CPC = 2, CPD = 3, SHARING = 101,agreement = 201' AFTER `sdkType`;
ALTER TABLE `c_company` ADD COLUMN `mediaPrice`  int(11) UNSIGNED  NOT NULL DEFAULT 0 COMMENT '固定价: price when paytype is CPM/CPC/CPD, nonsense when paytype is SHARING' AFTER `payType`;
ALTER TABLE `c_company` ADD COLUMN `mediaSharingRate`  float(10,2) UNSIGNED  NOT NULL DEFAULT 0.00 COMMENT '媒体分成: sharing rate with media side, make sense when paytype is SHARING' AFTER `mediaPrice`;

-- 提交所有sql，如果失败，则全部失败
commit;
-- 设定sql的字符编码
SET character_set_client = utf8;
SET character_set_connection = utf8;
use cheetahx;

-- 关闭自动提交，开启事务
SET AUTOCOMMIT = 0;

-- 媒体删除 接入方式.结算方式.固定价.媒体分成
ALTER TABLE `c_media` DROP COLUMN `sdkType` IF EXISTS `sdkType`;
ALTER TABLE `c_media` DROP COLUMN `payType` IF EXISTS `payType`;
ALTER TABLE `c_media` DROP COLUMN `mediaPrice` IF EXISTS `mediaPrice`;
ALTER TABLE `c_media` DROP COLUMN `mediaSharingRate` IF EXISTS `mediaSharingRate`;

-- 广告位删除 标识位.结算方式.固定价.媒体分成
ALTER TABLE `c_media_adslot` DROP COLUMN `relationId`  IF EXISTS `relationId`;
ALTER TABLE `c_media_adslot` DROP COLUMN `payType`  IF EXISTS `payType`;
ALTER TABLE `c_media_adslot` DROP COLUMN `mediaPrice`  IF EXISTS `mediaPrice`;
ALTER TABLE `c_media_adslot` DROP COLUMN `mediaSharingRate`  IF EXISTS `mediaSharingRate`;

-- 交易删除 标识位.结算方式.固定价.媒体分成
ALTER TABLE `c_deal` DROP COLUMN `payType`  IF EXISTS `payType`;
ALTER TABLE `c_deal` DROP COLUMN `mediaPrice`  IF EXISTS `mediaPrice`;
ALTER TABLE `c_deal` DROP COLUMN `mediaSharingRate`  IF EXISTS `mediaSharingRate`;
ALTER TABLE `c_deal` DROP COLUMN `bidStrategy`  IF EXISTS `bidStrategy`;

-- 媒体_广告_交易关系表删除 供应商ID
ALTER TABLE `c_media_adslot_deal` DROP COLUMN `company_id` IF EXISTS `company_id`;

-- 供应商删除 标识位.结算方式.固定价.媒体分成
ALTER TABLE `c_company` DROP COLUMN `sdkType`  IF EXISTS `sdkType`;
ALTER TABLE `c_company` DROP COLUMN `payType`  IF EXISTS `payType`;
ALTER TABLE `c_company` DROP COLUMN `mediaPrice`  IF EXISTS `mediaPrice`;
ALTER TABLE `c_company` DROP COLUMN `mediaSharingRate`  IF EXISTS `mediaSharingRate`;

-- 提交所有sql，如果失败，则全部失败
commit;
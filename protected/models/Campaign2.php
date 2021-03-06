<?php

/**
 * bunny库数据库
 * 广告系列表数据模型
 *
 * The followings are the available columns in table '{{campaign}}':
 * @property integer $id
 * @property integer $companyId
 * @property integer $userId
 * @property string $name
 * @property string $description
 * @property integer $categoryId
 * @property integer $budget
 * @property integer $dailyBudget
 * @property integer $deliverySpeed
 * @property integer $creativeRotate
 * @property integer $costType
 * @property integer $bidStrategy
 * @property integer $minBidPrice
 * @property integer $maxBidPrice
 * @property integer $startDate
 * @property integer $endDate
 * @property string $inventoryType
 * @property string $dayPartTargeting
 * @property integer $deviceIdTargeting
 * @property string $trafficTypeList
 * @property integer $frequencyFlag
 * @property integer $frequencyCapLevel
 * @property integer $frequencyCapUnit
 * @property integer $frequencyCapAmount
 * @property integer $status
 * @property integer $modificationTime
 * @property integer $mflag
 * @property integer $creationTime
 * @property integer $count
 * @property integer $imprQuota
 * @property integer $dailyImprQuota
 * @property integer $clickQuota
 * @property integer $dailyClickQuota
 */
class Campaign2 extends Db2ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{campaign}}';
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Campaign the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * 通过公司id集合获取广告系列
     * @param $companyIdArr 公司id集合
     * @return mixed
     */
    public function getCampaignByCids($companyIdArr) {
        $sql = "";
        $sql .= "SELECT ";
        $sql .= "id * -1 AS id, `name`, companyId AS pid ";
        $sql .= "FROM {{campaign}} ";
        $sql .= "WHERE ";
        $sql .= "`status` = IN(1, 2) AND companyId IN ("+ implode(", ", $companyIdArr) +")";

        return $this->_query($sql);
    }

    // 通过广告系列id获取全名
    public function getCampaignFullName($idArr) {
        $field = array(
            "a.`name` AS campaignName",
            "o.companyName",
            "concat(companyName,' > ', a.`name`) AS campaignFullName",
        );
        $from = "a";
        $join = array(
            "{{company}} o ON o.id = a.companyId",
        );
        $where = array(
            "a.id IN (". implode(', ', $idArr) .")",
        );

        return $this->_select()->_field($field)->_from($from, true)->_join($join)->_where($where)->_query();
    }

}

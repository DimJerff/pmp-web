<?php

/**
 * bunny库数据库
 * 公司表数据模型
 *
 * The followings are the available columns in table '{{company}}':
 * @property integer $id
 * @property integer $roleID
 * @property string $companyName
 * @property integer $parentCompanyId
 * @property integer $budget
 * @property integer $cost
 * @property integer $currency
 * @property integer $timezone
 * @property string $website
 * @property string $telephone
 * @property integer $postalCode
 * @property integer $country
 * @property integer $city
 * @property integer $state
 * @property string $address
 * @property integer $linkUserId
 * @property integer $category
 * @property string $businessLicense
 * @property string $identityCard
 * @property string $identityCard2
 * @property integer $mflag
 * @property integer $creationTime
 * @property integer $updateTime
 * @property integer $status
 * @property integer $checkPoint
 * @property integer $repairPoint
 * @property integer $checkUserId
 * @property integer $checkTime
 * @property integer $validTime
 */
class Company2 extends Db2ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{company}}';
	}

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Company the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    // 获取所有正常状态的公司
    public function getCompanys() {
        $sql = "";
        $sql .= "SELECT ";
        $sql .= "id, companyName AS name, 0 AS pid ";
        $sql .= "FROM {{company}} ";
        $sql .= "WHERE ";
        $sql .= "`status` = 1";

        return $this->_query($sql);
    }

}

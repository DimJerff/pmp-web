<?php

/**
 * 应用表数据模型
 * This is the model class for table "{{company}}".
 *
 * The followings are the available columns in table '{{company}}':
 * @property integer $id
 * @property integer $companyId
 * @property integer $os
 * @property integer $sdkType
 * @property string $appName
 * @property integer $appCategory
 * @property string $appBundle
 * @property integer $status
 * @property integer $mflag
 * @property integer $creationTime
 * @property integer $modificationTime
 * @property integer $payType
 * @property integer $mediaPrice
 * @property integer $mediaSharingRate
 */
class Media extends DbActiveRecord
{
	/**
     * 返回当前表名
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{media}}';
	}

    /**
     * 返回当前数据模型
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Company the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

	/**
     * 验证规则
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('companyId , os, sdkType , appName, appCategory , appBundle , appIcon , payType , mediaPrice , mediaSharingRate', 'required'),
			array('companyId , os, appCategory , payType , mediaPrice , mediaSharingRate', 'numerical', 'integerOnly'=>true),
			array('appName, ', 'length', 'max'=>32),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, companyId, os, appName, appCategory, appBundle', 'safe', 'on'=>'search'),
		);
	}

	/**
     * 多表关联关系
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
     * 属性名
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'companyId' => 'companyId',
			'os' => 'OS',
			'sdkType' => '接入方式',
            'appIcon' => '图标',
			'appName' => '应用名称必填',
			'appCategory' => 'App Category',
			'appBundle' => 'iTunes URL 或 应用包名称不能为空',
			'status' => 'Status',
			'mflag' => 'Mflag',
			'creationTime' => 'Creation Time',
			'modificationTime' => 'Update Time',
			'payType' => '结算方式',
			'mediaPrice' => '协议支付价格',
			'mediaSharingRate' => '媒体分成',
		);
	}

	/**
     * 搜索
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('companyId',$this->companyId);
		$criteria->compare('os',$this->os,true);
		$criteria->compare('sdkType',$this->sdkType);
		$criteria->compare('appName',$this->appName);
		$criteria->compare('appCategory',$this->appCategory);
		$criteria->compare('appBundle',$this->appBundle,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('mflag',$this->mflag);
		$criteria->compare('creationTime',$this->creationTime);
		$criteria->compare('modificationTime',$this->modificationTime);
		$criteria->compare('payType',$this->payType);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     * 触发验证前的处理
     * @return bool
     */
	public function beforeValidate()
	{
		parent::beforeValidate();
		if(!$this->creationTime) {
			$this->creationTime = $_SERVER['REQUEST_TIME'];
		}
		$this->modificationTime = $_SERVER['REQUEST_TIME'];
		if(!isset($this->status)) {
			$this->status = 1;
		}
        if (!isset($this->developId)) {
            $this->developId = Yii::app()->user->getId();
        }
		return true;
	}

    /**
     * 触发保存前的处理
     * @return bool
     */
	public function beforeSave() {
		parent::beforeSave();
	
		$this->mflag = 1;
	
		return true;
	}

    // 统计应用的列表sql
    public function getMediaPageListSql($companyId, $os=0, $dateTimeArr=array(), $order = '') {
        // 判断开始时间和结束时间差是否为一日
        $reportTableName = "{{report_adslot_daily}}";
        if (($dateTimeArr[1] - $dateTimeArr[0]) <= 86400) {
            $reportTableName = "{{report_adslot_hourly}}";
        }

        $select = "SQL_CALC_FOUND_ROWS";
        $field = array(
            "m.id",
            "m.companyId",
            "m.os",
            "m.appName",
            "m.appIcon",
            "m.developId",
            "m.modificationTime",
            "m.creationTime",
            "m.`status`",
            "o.osName",
            "SUM(cost)/1000000 AS cost",
            "SUM(bidRequest) AS bidRequest",
            "SUM(impressions) AS impressions",
            "SUM(clicks) AS clicks",
            "IF(SUM(bidRequest), ROUND((SUM(impressions)/SUM(bidRequest) * 100), 2), 0) AS fillingr",
            "IF(SUM(impressions), ROUND((SUM(clicks)/SUM(impressions)*100), 2), 0) AS ctr",
            "IF(SUM(impressions), SUM(cost)/SUM(impressions)/1000, 0) AS ecpm",
            "IF(SUM(clicks), SUM(cost)/SUM(clicks)/1000000, 0) AS ecpc",
            "(SELECT COUNT(*) FROM c_media_adslot a WHERE a.mediaId = m.id) AS adslotCount"
        );
        $from = "m";
        $where = array(
            "m.`status` IN (1, 2)",
            "m.companyId = {$companyId}",
        );

        if(!empty($os)) {
            $where[] =  "m.os = {$os}";
        }
        $join = array(
            "{{base_os}} o ON m.os = o.id",
            //"{{report_media_daily}} rm ON (m.id = rm.mediaId AND rm.dateTime BETWEEN {$dateTimeArr[0]} AND {$dateTimeArr[1]})",
            //"{{report_deal_daily}} rm ON (m.id = rm.mediaId AND rm.companyId = {$companyId} AND rm.dateTime BETWEEN {$dateTimeArr[0]} AND {$dateTimeArr[1]})",
            "{$reportTableName} rm ON (m.id = rm.mediaId AND rm.companyId = {$companyId} AND rm.dateTime BETWEEN {$dateTimeArr[0]} AND {$dateTimeArr[1]})",
        );
        $group = "m.id";

        return $this->_select($select)->_field($field)->_from($from, true)->_join($join)->_where($where)->_group($group)->_order($order)->_getBuildSql();
    }

    /**
     * 获取统计应用的列表
     * @param $companyId 公司id
     * @param int $os 操作系统id
     * @param array $dateTimeArr 起始时间
     * @param string $order 排序
     * @return array
     */
    public function getMediaPageList($companyId, $os=0, $dateTimeArr=array(), $order = '') {
        // 获取sql
        $sql = $this->getMediaPageListSql($companyId, $os, $dateTimeArr, $order);

        $paging = Paging::instance();
        $paging->setPageSize(25);
        $paging->setPageNumKey('pagenum');
        $list = $paging->query($sql);
        return array($list, $paging->data());
    }

    // 统计应用的列表所以信息数据
    public function getMediaList($companyId, $dateTimeArr=array(), $os=0) {
        // 获取sql
        $sql = $this->getMediaPageListSql($companyId, $os, $dateTimeArr, NULL);

        return $this->_query($sql);
    }


    /**
     * 通过主键id获取应用信息带系统名称
     * @param $id
     * @return mixed
     */
    public function getMediaById($id) {
        $sql  = '';
        $sql .= "SELECT ";
        $sql .= "m.*, b.osName ";
        $sql .= "FROM ";
        $sql .= "{{media}} m ";
        $sql .= "LEFT JOIN {{base_os}} b ON b.id = m.os ";
        $sql .= "WHERE ";
        $sql .= "m.id = {$id}";

        return $this->_find($sql);

    }

    /**
     * 通过开发者id获取应用信息
     * @param $developId 开发者id
     * @return mixed
     */
    public function getMediaByDevelopId($developId) {
        $sql  = "";
        $sql .= "SELECT ";
        $sql .= "id AS mediaId, appName ";
        $sql .= "FROM {{media}} ";
        $sql .= "WHERE developId = {$developId} AND `status` = 1";

        return $this->_query($sql);
    }

    /**
     * 通过开发者id获取应用
     * @param $developId 开发者id
     * @return mixed
     */
    public function getMediaDealByDid($developId) {
        $sql = "";
        $sql .= "SELECT ";
        $sql .= "id, appName AS name, 0 as pid ";
        $sql .= "FROM {{media}} ";
        $sql .= "WHERE ";
        $sql .= "developId = {$developId} AND `status` = 1";

        return $this->_query($sql);
    }

    public function getMediaDealByCid($companyId) {
        $sql = "";
        $sql .= "SELECT ";
        $sql .= "id, appName AS name, 0 as pid ";
        $sql .= "FROM {{media}} ";
        $sql .= "WHERE ";
        $sql .= "companyId = {$companyId} AND `status` = 1";

        return $this->_query($sql);
    }


}

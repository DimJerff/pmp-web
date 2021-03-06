<?php

/**
 * 交易消耗日报表
 * This is the model class for table "{{report_deal_daily}}".
 *
 * The followings are the available columns in table '{{report_deal_daily}}':
 * @property string $id
 * @property string $dealId
 * @property string $mediaId
 * @property string $adslotId
 * @property integer $companyId
 * @property string $bidRequest
 * @property integer $impressions
 * @property integer $clicks
 * @property string $cost
 * @property string $dateTime
 */
class ReportDealDaily extends DbActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{report_deal_daily}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('companyId, impressions, clicks', 'numerical', 'integerOnly'=>true),
			array('dealId, bidRequest', 'length', 'max'=>11),
			array('mediaId, adslotId', 'length', 'max'=>10),
			array('cost, dateTime', 'length', 'max'=>20),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, dealId, mediaId, adslotId, companyId, bidRequest, impressions, clicks, cost, dateTime', 'safe', 'on'=>'search'),
		);
	}

	/**
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
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'dealId' => '交易id',
			'mediaId' => '应用id',
			'adslotId' => '广告位id',
			'companyId' => '公司id',
			'bidRequest' => '请求数',
			'impressions' => '展示数',
			'clicks' => '点击数',
			'cost' => '消耗',
			'dateTime' => '创建时间',
		);
	}

	/**
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

		$criteria->compare('id',$this->id,true);
		$criteria->compare('dealId',$this->dealId,true);
		$criteria->compare('mediaId',$this->mediaId,true);
		$criteria->compare('adslotId',$this->adslotId,true);
		$criteria->compare('companyId',$this->companyId);
		$criteria->compare('bidRequest',$this->bidRequest,true);
		$criteria->compare('impressions',$this->impressions);
		$criteria->compare('clicks',$this->clicks);
		$criteria->compare('cost',$this->cost,true);
		$criteria->compare('dateTime',$this->dateTime,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ReportDealDaily the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * 获取当日消耗
     * @param $companyId 公司id
     * @return mixed
     */
    public function getTodayCost($companyId = 0) {
        $field = "SUM(cost)/1000000 AS cost";
        if (!empty($companyId)) {
            $where[] = "companyId = {$companyId}";
        }
        $where[] = "dateTime = " . strtotime(date("Y-m-d", time()));

        $result = $this->_select()->_field($field)->_from()->_where($where)->_find();
        return $result['cost'] ? $result['cost']: '0.00';
    }

    /**
     * 获取所有消耗
     * @param $companyId 公司id
     * @return mixed
     */
    public function getAllCost($companyId = 0) {
        $field = "SUM(cost)/1000000 AS cost";
        if (!empty($companyId)) {
            $where[] = "companyId = {$companyId}";
        }

        $result = $this->_select()->_field($field)->_from()->_where($where)->_find();
        return $result['cost'] ? $result['cost']: '0.00';
    }

    /**
     * 根据时间范围和公司id获取应用报表数据
     * @param $companyId 公司id
     * @param $dateTimeArr 起始时间数组
     * @return mixed
     */
    public function getReportByCidAndTimeOfMid($companyId, $dateTimeArr, $mediaId) {
        $field = array(
            "SUM(bidRequest) AS bidRequest",
            "SUM(impressions) AS impressions",
            "SUM(clicks) AS clicks",
            "IF(SUM(bidRequest), ROUND((SUM(impressions)/SUM(bidRequest) * 100), 2), 0) AS fillingr",
            "IF(SUM(impressions), ROUND((SUM(clicks)/SUM(impressions) * 100), 2), 0) AS ctr",
            "SUM(cost)/1000000 AS cost",
            "dateTime",
        );
        $where = array(
            "companyId = {$companyId}",
            "dateTime BETWEEN {$dateTimeArr[0]} AND {$dateTimeArr[1]}",
        );
        if (!empty($mediaId)) {
            $where[] = "mediaId = {$mediaId}";
        }
        $group = "dateTime";
        $order = "dateTime";

        return $this->_select()->_field($field)->_from()->_where($where)->_group($group)->_order($order)->_query();
    }

    /**
     * 根据时间范围和公司id获取广告位报表数据
     * @param $companyId 公司id
     * @param $dateTimeArr 起始时间数组
     * @return mixed
     */
    public function getReportByCidAndTimeOfAid($companyId, $dateTimeArr, $adslotId=0) {
        $field =array(
            "SUM(bidRequest) AS bidRequest",
            "SUM(impressions) AS impressions",
            "SUM(clicks) AS clicks",
            "IF(SUM(bidRequest), ROUND((SUM(impressions)/SUM(bidRequest) * 100), 2), 0) AS fillingr",
            "IF(SUM(impressions), ROUND((SUM(clicks)/SUM(impressions) * 100), 2), 0) AS ctr",
            "SUM(cost)/1000000 AS cost",
            "dateTime",
        );

        $where = array(
            "companyId = {$companyId}",
            "dateTime BETWEEN {$dateTimeArr[0]} AND {$dateTimeArr[1]}",
        );
        if (!empty($mediaId)) {
            $where[] = "adslotId = {$adslotId}";
        }
        $group = "dateTime";
        $order = "dateTime";

        return $this->_select()->_field($field)->_from()->_where($where)->_group($group)->_order($order)->_query();
    }
}

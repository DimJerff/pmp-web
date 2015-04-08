<?php

/**
 * 交易应用广告位关系表数据模型
 * This is the model class for table "{{media_adslot_deal}}".
 *
 * The followings are the available columns in table '{{media_adslot_deal}}':
 * @property integer $mediaId
 * @property integer $adslotId
 * @property integer $dealId
 */
class MediaAdslotDeal extends DbActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{media_adslot_deal}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('mediaId, adslotId, dealId', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('mediaId, adslotId, dealId', 'safe', 'on'=>'search'),
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
			'mediaId' => '应用id',
			'adslotId' => '广告位id 0:应用全选时 广告位默认为0',
			'dealId' => '交易id',
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

		$criteria->compare('mediaId',$this->mediaId);
		$criteria->compare('adslotId',$this->adslotId);
		$criteria->compare('dealId',$this->dealId);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return MediaAdslotDeal the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * 通过交易id删除对应的交易关系
     * @param $dealId 交易id
     * @return mixed
     */
    public function delDealById($dealId) {
        $sql = "DELETE FROM ".$this->tableName()." WHERE dealId = {$dealId}";
        return Yii::app()->db->createCommand($sql)->execute();
    }

    /**
     * 清理旧交易id插入新交易关系
     * @param $dealId 交易id
     * @param $mediaIdArr 媒体id集合
     * @param $adslotIdArr 广告位id集合
     * @return mixed
     */
    public function upDealRelation($dealId, $mediaIdArr, $adslotIdArr) {
        // 先清理旧数据
        $this->delDealById($dealId);
        $arr = array();
        if (!empty($mediaIdArr)) {
            foreach ($mediaIdArr as $v) {
                $arr[] = "({$v}, 0, {$dealId})";
            }
        }

        if (!empty($adslotIdArr)) {
            $adslotIdArr = MediaAdslot::model()->getMediaIdsByIds($adslotIdArr);
            foreach ($adslotIdArr as $v) {
                $arr[] = "({$v['mediaId']}, {$v['adslotId']}, {$dealId})";
            }
        }
        $sql = "INSERT INTO ".$this->tableName()." VALUES " . implode(", ", $arr);
        return Yii::app()->db->createCommand($sql)->execute();
    }

    // 通过应用id或者广告位id获取交易对应的交易信息sql
    protected function getDealByMidOrAidSql($dateTimeArr, $companyId, $mediaId, $adslotId=0, $order=NULL) {
        $select = "SQL_CALC_FOUND_ROWS";
        $field = array(
            "mad.dealId",
            "d.dealName",
            "d.dealType",
            "d.payType",
            "d.mediaPrice",
            "d.startDate",
            "d.endDate",
            "d.mediaSharingRate",
            "SUM(bidRequest) AS bidRequest",
            "SUM(impressions) AS impressions",
            "SUM(clicks) AS clicks",
            "IF(SUM(bidRequest), ROUND((SUM(impressions)/SUM(bidRequest) * 100), 2), 0) AS fillingr",
            "IF(SUM(impressions), ROUND((SUM(clicks)/SUM(impressions) * 100), 2), 0) AS ctr",
            "SUM(cost) AS cost",
            "dateTime",
        );
        $from = "mad";
        $join = array(
            "{{deal}} d ON d.id = mad.dealId",
            "{{report_deal_daily}} rda ON (rda.dealId = mad.dealId AND rda.dateTime BETWEEN {$dateTimeArr[0]} AND {$dateTimeArr[1]})",
        );
        $where = array(
            "d.companyId = {$companyId}",
            "d.`status` = 1",
        );
        if (empty($adslotId)) {
            $where[] = "mediaId = {$mediaId}";
        } else {
            $where[] = "adslotId = {$adslotId} OR (mediaId = {$mediaId} AND adslotId = 0)";
        }
        $group = "d.id";
        if (empty($order)) {
            $order = NULL;
        }

        return $this->_select($select)->_field($field)->_from($from, true)->_join($join)->_where($where)->_group($group)->_order($order)->_getBuildSql();
    }

    /**
     * 通过应用id或者广告位id获取交易对应的交易信息
     * @param $dateTimeArr 起始时间
     * @param $companyId 公司id
     * @param $mediaId 应用id
     * @param int $adslotId 广告位id
     * @param null $order 排序
     * @return array
     */
    public function getDealByMidOrAid($dateTimeArr, $companyId, $mediaId, $adslotId=0, $order=NULL) {
        // 获取sql
        $sql = $this->getDealByMidOrAidSql($dateTimeArr, $companyId, $mediaId, $adslotId, $order);

        // 分页处理
        $paging = Paging::instance();
        $paging->setPageSize(5);
        $paging->setPageNumKey('pagenum');
        $list = $paging->query($sql);
        return array($list, $paging->data());
    }

    // 通过应用id或者广告位id获取交易对应的交易信息列表
    public function getDealListByMidOrAid($dateTimeArr, $companyId, $mediaId) {
        // 获取sql
        $sql = $this->getDealByMidOrAidSql($dateTimeArr, $companyId, $mediaId, 0, NULL);

        return $this->_query($sql);
    }

    // 通过交易id获取交易应用的交易信息Sql
    protected function getDealByDealIdSql($dateTimeArr, $companyId, $dealId, $order=NULL) {
        $select = "SQL_CALC_FOUND_ROWS";
        $field = array(
            "mad.dealId",
            "d.dealName",
            "d.dealType",
            "d.payType",
            "d.mediaPrice",
            "d.startDate",
            "d.endDate",
            "d.mediaSharingRate",
            "SUM(bidRequest) AS bidRequest",
            "SUM(impressions) AS impressions",
            "SUM(clicks) AS clicks",
            "IF(SUM(bidRequest), ROUND((SUM(impressions)/SUM(bidRequest) * 100), 2), 0) AS fillingr",
            "IF(SUM(impressions), ROUND((SUM(clicks)/SUM(impressions) * 100), 2), 0) AS ctr",
            "IF(SUM(impressions), SUM(cost)/SUM(impressions)/1000, 0) AS ecpm",
            "IF(SUM(clicks), SUM(cost)/SUM(clicks)/1000000, 0) AS ecpc",
            "SUM(cost) AS cost",
            "dateTime",
            "mad.mediaId",
            "mad.adslotId",
            "m.appName",
            "ma.adslotName",
            "d.`status`",
        );
        $from = "mad";
        $join = array(
            "{{media}} m ON mad.mediaId = m.id",
            "{{media_adslot}} ma ON mad.adslotId = ma.id",
            "{{deal}} d ON d.id = mad.dealId",
            "{{report_deal_daily}} rda ON (rda.dealId = mad.dealId AND rda.dateTime BETWEEN {$dateTimeArr[0]} AND {$dateTimeArr[1]})",
        );
        $where = array(
            "d.companyId = {$companyId}",
            "d.`status` IN (1, 2)",
            "mad.dealId = {$dealId}",
        );
        $group = "d.id";
        if (empty($order)) {
            $order = NULL;
        }

        return $this->_select($select)->_field($field)->_from($from, true)->_join($join)->_where($where)->_group($group)->_order($order)->_getBuildSql();
    }

    // 通过交易id获取交易对应的交易信息
    public function getDealByDealId($dateTimeArr, $companyId, $dealId, $order=NULL) {
        // 获取sql
        $sql = $this->getDealByDealIdSql($dateTimeArr, $companyId, $dealId, $order);

        // 分页处理
        $paging = Paging::instance();
        $paging->setPageSize(5);
        $paging->setPageNumKey('pagenum');
        $list = $paging->query($sql);
        return array($list, $paging->data());
    }

    // 通过交易id获取交易对应的交易信息的列表
    public function getDealListByDealId($dateTimeArr, $companyId, $dealId) {
        // 获取sql
        $sql = $this->getDealByDealIdSql($dateTimeArr, $companyId, $dealId, NULL);

        return $this->_query($sql);
    }
}

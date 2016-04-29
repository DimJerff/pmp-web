<?php

/**
 * 交易应用广告位关系表数据模型
 * This is the model class for table "{{media_adslot_deal}}".
 *
 * The followings are the available columns in table '{{media_adslot_deal}}':
 * @property integer $mediaId
 * @property integer $adslotId
 * @property integer $dealId
 * @property integer $companyId
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
			array('mediaId, adslotId, dealId, companyId', 'numerical', 'integerOnly'=>true),
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
			'companyId' => '供应商id',
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
		$criteria->compare('companyId',$this->companyId);

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
        // 先清理旧数据 通过交易id删除对应的交易关系
        $this->delDealById($dealId);
        $arr = array();
        $compamyID = Yii::app()->session['companyID'];
        if(!$compamyID){
            return false;
        }
        if (empty($mediaIdArr) && empty($adslotIdArr)) {
            $arr[] = "(0, 0, {$dealId}, {$compamyID}, 1)";
        }else{
            if (!empty($mediaIdArr)) {
                foreach ($mediaIdArr as $v) {
                    $arr[] = "({$v}, 0, {$dealId}, {$compamyID}, 1)";
                }
            }
            if (!empty($adslotIdArr)) {
                $adslotIdArr = MediaAdslot::model()->getMediaIdsByIds($adslotIdArr);
                foreach ($adslotIdArr as $v) {
                    $arr[] = "({$v['mediaId']}, {$v['adslotId']}, {$dealId}, {$compamyID}, 1)";
                }
            }
        }
        $sql = "INSERT INTO ".$this->tableName()." VALUES " . implode(", ", $arr);
        return Yii::app()->db->createCommand($sql)->execute();
    }

    /**
     * 通过应用id或者广告位id获取交易对应的交易信息sql
     * @param $dateTimeArr
     * @param $companyId
     * @param $mediaId
     * @param int $adslotId
     * @param null $order
     * @param int $throw
     * @return bool|string
     */
    protected function getDealByMidOrAidSql($dateTimeArr, $companyId, $mediaId, $adslotId=0, $order=NULL, $throw=0) {
        $select = "SQL_CALC_FOUND_ROWS";
        $field = array(
            "mad.dealId",
            "d.dealName",
            "d.dealType",
            "d.bidStrategy",
            "d.bidfloor",
            "d.payType",
            "d.mediaPrice",
            "d.startDate",
            "d.endDate",
            "d.mediaSharingRate",
            "d.status",
            "SUM(bidResponse) AS bidResponse",
            "SUM(impressions) AS impressions",
            "SUM(clicks) AS clicks",
            "IF(SUM(impressions), ROUND((SUM(clicks)/SUM(impressions) * 100), 2), 0) AS ctr",
            "SUM(cost)/1000000 AS cost",
            "dateTime",
        );
        $from = "mad";
        /*$join = array(
            "{{deal}} d ON d.id = mad.dealId",
            "{{report_deal_daily}} rda ON (rda.dealId = mad.dealId AND rda.dateTime BETWEEN {$dateTimeArr[0]} AND {$dateTimeArr[1]})",
        );*/
        $join[] = "{{deal}} d ON d.id = mad.dealId";
        if (empty($adslotId)) {
            $join[] = "{{report_deal_daily}} rda ON (rda.dealId = mad.dealId AND rda.mediaId = mad.mediaId AND rda.dateTime BETWEEN {$dateTimeArr[0]} AND {$dateTimeArr[1]})";
        } else {
            $join[] = "{{report_deal_daily}} rda ON (rda.dealId = mad.dealId AND (mad.adslotId = {$adslotId} OR (mad.mediaId = {$mediaId} AND mad.adslotId = 0)) AND rda.dateTime BETWEEN {$dateTimeArr[0]} AND {$dateTimeArr[1]})";
        }

        $where = array(
            "d.companyId = {$companyId}",
        );

        if (empty($adslotId)) {
            $where[] = "mad.mediaId = {$mediaId}";
        } else {
            $where[] = "mad.adslotId = {$adslotId} OR (mad.mediaId = {$mediaId} AND mad.adslotId = 0)";
        }
        if ($throw) {
            $where[] = "d.status = 1";
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
    public function getDealByMidOrAid($dateTimeArr, $companyId, $mediaId, $adslotId=0, $order=NULL, $throw=0) {
        // 获取sql
        $sql = $this->getDealByMidOrAidSql($dateTimeArr, $companyId, $mediaId, $adslotId, $order, $throw);

        // 分页处理
        $paging = Paging::instance();
        $paging->setPageSize(25);
        $paging->setPageNumKey('pagenum');
        $list = $paging->query($sql);
        return array($list, $paging->data());
    }

    /**
     * 通过应用id或者广告位id获取交易对应的交易信息列表
     * @param $dateTimeArr
     * @param $companyId
     * @param $mediaId
     * @return mixed
     */
    public function getDealListByMidOrAid($dateTimeArr, $companyId, $mediaId, $adslotId=0) {
        // 获取sql
        $sql = $this->getDealByMidOrAidSql($dateTimeArr, $companyId, $mediaId, $adslotId, NULL);

        return $this->_query($sql);
    }

    /**
     * 通过交易id获取交易应用的交易信息Sql
     * @param $dateTimeArr
     * @param $companyId
     * @param $dealId
     * @param null $order
     * @return bool|string
     */
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
            "SUM(bidResponse) AS bidResponse",
            "SUM(impressions) AS impressions",
            "SUM(clicks) AS clicks",
            //"IF(SUM(bidRequest), ROUND((SUM(impressions)/SUM(bidRequest) * 100), 2), 0) AS fillingr",
            "IF(SUM(impressions), ROUND((SUM(clicks)/SUM(impressions) * 100), 2), 0) AS ctr",
            "IF(SUM(impressions), SUM(cost)/SUM(impressions)/1000, 0) AS ecpm",
            "IF(SUM(clicks), SUM(cost)/SUM(clicks)/1000000, 0) AS ecpc",
            "SUM(cost)/1000000 AS cost",
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

    /**
     * 通过交易id获取交易对应的交易信息的列表
     * @param $dateTimeArr
     * @param $companyId
     * @param $dealId
     * @return mixed
     */
    public function getDealListByDealId($dateTimeArr, $companyId, $dealId) {
        // 获取sql
        $sql = $this->getDealByDealIdSql($dateTimeArr, $companyId, $dealId, NULL);

        return $this->_query($sql);
    }

    /**
     * 通过交易id获取其下的所有广告位消耗数据SQL
     * @param $dateTimeArr 时间区间
     * @param $companyId 公司id
     * @param $dealId 交易id
     * @param null $order 排序
     * @return bool|string
     */
    public function getAdslotDataByDealIdSql($dateTimeArr, $companyId, $dealId, $order=NULL, $adslotName='', $throw=0) {
        // 判断开始时间和结束时间差是否为一日
        $reportTableName = "{{report_deal_daily}}";
        if (($dateTimeArr[1] - $dateTimeArr[0]) <= 86400) {
            $reportTableName = "{{report_deal_hourly}}";
        }

        $select = "SQL_CALC_FOUND_ROWS";
        $field = array(
            "mad.*",
            "m.appName",
            "ma.adslotName",
            //"ma.status",
            //"SUM(bidResponse) AS bidResponse",
            "SUM(impressions) AS impressions",
            "SUM(clicks) AS clicks",
            //"IF(SUM(bidRequest), ROUND((SUM(impressions)/SUM(bidRequest) * 100), 2), 0) AS fillingr",
            "IF(SUM(impressions), ROUND((SUM(clicks)/SUM(impressions) * 100), 2), 0) AS ctr",
            "SUM(cost)/1000000 AS cost",
            "IF(SUM(impressions), SUM(cost)/SUM(impressions)/1000, 0) AS ecpm",
            "IF(SUM(clicks), SUM(cost)/SUM(clicks)/1000000, 0) AS ecpc",
        );
        $from = "mad";
        $join = array(
            "{{media}} m ON mad.mediaId = m.id",
            "{{media_adslot}} ma on mad.adslotId = ma.id",
            "{{deal}} d on mad.dealId = d.id",
            "{$reportTableName} rd on rd.dealId = mad.dealId  AND rd.dateTime BETWEEN {$dateTimeArr[0]} AND {$dateTimeArr[1]} AND ((mad.adslotId > 0 AND rd.adslotId = mad.adslotId) OR (mad.adslotId = 0 AND rd.mediaId = mad.mediaId))",
        );
        $where = array(
            "d.companyId = {$companyId}",
            "mad.dealId = {$dealId}",
        );
        if ($throw) {
            $where[] = "((mad.adslotId > 0 AND ma.`status` = 1) OR (mad.adslotId = 0 AND m.`status` = 1))";
        }
        if (!empty($adslotName)) {
            $where[] = "adslotName LIKE '%". $adslotName ."%'";
        }
        $group = "mad.adslotId, mad.mediaId";
        if (empty($order)) {
            $order = NULL;
        }

        return $this->_select($select)->_field($field)->_from($from, true)->_join($join)->_where($where)->_group($group)->_order($order)->_getBuildSql();
    }

    /**
     * 通过交易id获取其下的所有广告位消耗数据
     * @param $dateTimeArr 时间区间
     * @param $companyId 公司id
     * @param $dealId 交易id
     * @param null $order 排序
     * @param string $adslotName
     * @param int $throw
     * @return array
     */
    public function getAdslotDataByDealId($dateTimeArr, $companyId, $dealId, $order=NULL, $adslotName='', $throw=0) {
        // 获取sql
        $sql = $this->getAdslotDataByDealIdSql($dateTimeArr, $companyId, $dealId, $order, $adslotName, $throw);

        // 分页处理
        $paging = Paging::instance();
        $paging->setPageSize(25);
        $paging->setPageNumKey('pagenum');
        $list = $paging->query($sql);
        return array($list, $paging->data());
    }

    /**
     * 查询一段时间范围内在交易的应用列表信息[不包含制定交易id]
     * @param $companyId 公司id
     * @param $mediaIdArr 应用id集合
     * @param $dealId 交易id
     * @param $startDate 开始时间戳
     * @param int $endDate 结果时间戳
     * @return array|mixed
     */
    public function getCheckedMediaList($companyId, $mediaIdArr, $dealId, $startDate, $endDate=0) {
        if (empty($mediaIdArr)) {
            return array();
        }
        $field = "t.*, d.dealName, m.appName, ma.adslotName";
        $join = array(
            "c_media_adslot ma ON ma.id = t.adslotId",
            "c_media m ON m.id = t.mediaId",
        );
        if (empty($endDate)) {
            $join[] = "c_deal d ON d.id = t.dealId AND (endDate >= {$startDate} OR (endDate = 0))";
        } else {
            $join[] = "c_deal d ON d.id = t.dealId AND (endDate >= {$startDate} OR (startDate <= {$startDate} AND endDate = 0))";
        }
        $where[] = "t.mediaId IN (". implode(",", $mediaIdArr) .") AND d.companyId = {$companyId}";
        if (!empty($dealId)) {
            $where[] = "t.dealId != {$dealId}";
        }

        return $this->_select()->_field($field)->_from()->_join($join)->_where($where)->_query();
    }

    /**
     * 查询一段时间范围内在交易的广告位列表信息[不包含制定交易id]
     * @param $companyId 公司id
     * @param $adslotIdArr 应用id集合
     * @param $dealId 交易id
     * @param $startDate 开始时间戳
     * @param int $endDate 结果时间戳
     * @return array|mixed
     */
    public function getCheckedAdslotList($companyId, $adslotIdArr, $dealId, $startDate, $endDate=0) {
        if (empty($adslotIdArr)) {
            return array();
        }
        $field = "t.*, d.dealName, m.appName, ma.adslotName";
        $join = array(
            "c_media_adslot ma ON ma.id = t.adslotId",
            "c_media m ON m.id = t.mediaId",
        );
        if (empty($endDate)) {
            $join[] = "c_deal d ON d.id = t.dealId AND (endDate >= {$startDate} OR (endDate = 0))";
        } else {
            $join[] = "c_deal d ON d.id = t.dealId AND (endDate >= {$startDate} OR (startDate <= {$startDate} AND endDate = 0))";
        }
        $where[] = "t.adslotId IN (". implode(",", $adslotIdArr) .") AND d.companyId = {$companyId}";
        if (!empty($dealId)) {
            $where[] = "t.dealId != {$dealId}";
        }

        return $this->_select()->_field($field)->_from()->_join($join)->_where($where)->_query();
    }
}

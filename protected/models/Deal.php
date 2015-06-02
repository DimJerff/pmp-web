<?php

/**
 *  交易表数据模型
 *
 * The followings are the available columns in table '{{deal}}':
 * @property integer $id
 * @property string $dealName
 * @property integer $dealType
 * @property integer $payType
 * @property integer $mediaPrice
 * @property double $mediaSharingRate
 * @property string $developId
 * @property string $medias
 * @property string $adslots
 * @property string $companies
 * @property string $campaigns
 * @property integer $startDate
 * @property integer $endDate
 * @property integer $bidfloor
 * @property string $wseat
 * @property integer $status
 * @property integer $mflag
 * @property integer $modificationTime
 * @property integer $creationTime
 */
class Deal extends DbActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{deal}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('dealName, dealType, payType, developId, startDate, bidfloor', 'required'),
			array('dealType, payType, mediaPrice, bidfloor, status, mflag, modificationTime, creationTime', 'numerical', 'integerOnly'=>true),
			array('mediaSharingRate', 'numerical'),
			array('dealName', 'length', 'max'=>60, 'min'=>2),
			array('developId', 'length', 'max'=>10),
			array('medias, adslots', 'length', 'min'=>2),
			array('companies, campaigns, wseat', 'length', 'min'=>2),
			array('startDate, endDate', 'length', 'max'=>20),
            array('payType', 'checkPayType'),
            array('startDate', 'checkDate'),
            array('developId', 'checkMediasAdslots'),
            array('dealType', 'checkCompaniesCampaigns'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, dealName, dealType, payType, mediaPrice, mediaSharingRate, developId, medias, adslots, companies, campaigns, startDate, endDate, bidfloor, wseat, status, mflag, modificationTime, creationTime', 'safe', 'on'=>'search'),
		);
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
            $this->companyId = Yii::app()->user->getRecord()->defaultCompanyID;
        }
        return true;
    }

    /**
     * 验证结算方式
     */
    public function checkPayType() {
        if ($this->payType > 100) {
            $this->mediaSharingRate = intval($this->mediaSharingRate);
            if (empty($this->mediaSharingRate)) {
                $this->addError('mediaSharingRate', '媒体收入占售出额比例不能为空且为数字');
            }
        } else {
            $this->mediaPrice = intval($this->mediaPrice);
            if (empty($this->mediaPrice)) {
                $this->addError('mediaPrice', '协议支付价格不能为空且为数字');
            }
        }
    }

    /**
     * 验证应用/广告位
     */
    public function checkMediasAdslots() {
        if (empty($this->medias) && empty($this->adslots)) {
            $this->addError('medias', '应用/广告位不能为空');
        }
    }

    /**
     * 验证公司/广告系列
     */
    public function checkCompaniesCampaigns() {
        // 私有竞价-判断公司/广告系列是否为空
        if ($this->dealType) {
            if (empty($this->companies) && empty($this->campaigns)) {
                $this->addError('companies', '公司/广告系列不能为空');
            }
            if ($this->payType != 3) {
                return true;
            }
        }
        if (empty($this->startDate)){
            return false;
        }

        // 交易类型下检测选择的广告位同一时段内是否已经被选择
        $errMsg = array(
            '以下应用或广告位同一时段已经被其他交易选择',
        );
        // 获取公司id
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;
        $checkedMediaList = MediaAdslotDeal::model()->getCheckedMediaList($companyId, CJSON::decode($this->medias, true), $this->startDate, $this->endDate);
        $checkedAdslotList = MediaAdslotDeal::model()->getCheckedAdslotList($companyId, CJSON::decode($this->adslots, true), $this->startDate, $this->endDate);
        if (!empty($checkedMediaList)) {
            foreach ($checkedMediaList as $k=>$v) {
                $errMsg[] = $v['dealName'].' > '.$v['appName'];
            }
        }
        if (!empty($checkedAdslotList)) {
            foreach ($checkedAdslotList as $k=>$v) {
                $errMsg[] = $v['dealName'].' > '.$v['appName'].' > '.$v['adslotName'];
            }
        }
        if (count($errMsg) != 1) {
            $this->addError('dealType', implode('<br />', $errMsg));
        }




    }

    /**
     * 验证时间
     */
    public function checkDate() {
        if (empty($this->endDate)) {
            return true;
        }
        if ($this->startDate > $this->endDate) {
            $this->addError('endDate', '结束时间不能小于开始时间');
        }
    }

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
            'userInfo' => array(self::BELONGS_TO, 'User', 'developId'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'dealName' => '交易名称',
			'dealType' => '交易类型deal type ,  公开竞价open = 0  私有竞价private = 1 ',
			'payType' => 'pay type , CPM = 1, CPC = 2, CPD = 3, SHARING = 101',
			'mediaPrice' => 'price when paytype is CPM/CPC/CPD, nonsense when paytype is SHARING',
			'mediaSharingRate' => 'sharing rate with media side, make sense when paytype is SHARING',
			'developId' => '开发者ID',
			'medias' => '应用id集合',
			'adslots' => '广告位id集合',
			'companies' => '公司id集合candidate companies,  like [66, 67]',
			'campaigns' => '广告系列id集合candidate campaigns, like [11, 34]',
			'startDate' => '开始时间',
			'endDate' => '结束时间',
			'bidfloor' => '售出低价',
			'wseat' => 'refer to openrtb',
			'status' => 'Status',
			'mflag' => '修改状态',
			'modificationTime' => '修改时间',
			'creationTime' => '创建时间',
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

		$criteria->compare('id',$this->id);
		$criteria->compare('dealName',$this->dealName,true);
		$criteria->compare('dealType',$this->dealType);
		$criteria->compare('payType',$this->payType);
		$criteria->compare('mediaPrice',$this->mediaPrice);
		$criteria->compare('mediaSharingRate',$this->mediaSharingRate);
		$criteria->compare('developId',$this->developId,true);
		$criteria->compare('medias',$this->medias,true);
		$criteria->compare('adslots',$this->adslots,true);
		$criteria->compare('companies',$this->companies,true);
		$criteria->compare('campaigns',$this->campaigns,true);
		$criteria->compare('startDate',$this->startDate,true);
		$criteria->compare('endDate',$this->endDate,true);
		$criteria->compare('bidfloor',$this->bidfloor);
		$criteria->compare('wseat',$this->wseat,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('mflag',$this->mflag);
		$criteria->compare('modificationTime',$this->modificationTime);
		$criteria->compare('creationTime',$this->creationTime);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Deal the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
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

    // 获取交易列表sql
    public function getDealListSql($companyId, $dateTimeArr, $order='', $mediaid=0, $adslotid=0, $dealname='', $throw=0) {
        // 判断开始时间和结束时间差是否为一日
        $reportTableName = "{{report_deal_daily}}";
        if (($dateTimeArr[1] - $dateTimeArr[0]) <= 86400) {
            $reportTableName = "{{report_deal_hourly}}";
        }

        $select = "SQL_CALC_FOUND_ROWS";
        $field = array(
            "d.*",
            //"SUM(bidRequest) AS bidRequest",
            "SUM(impressions) AS impressions",
            "SUM(clicks) AS clicks",
            //"IF(SUM(bidRequest), ROUND((SUM(impressions)/SUM(bidRequest) * 100), 2), 0) AS fillingr",
            "IF(SUM(impressions), ROUND((SUM(clicks)/SUM(impressions) * 100), 2), 0) AS ctr",
            "SUM(cost) AS cost",
            "dateTime",
            //"CONCAT(u.firstname,u.lastname) AS developName",
            "c.companyName AS developName",
        );
        $from = "{{deal}} d";
        $join = array(
            //"{{user}} u ON u.id = d.developId",
            "{{company}} c ON c.id = d.developId",
            "{$reportTableName} rd ON (rd.dealId = d.id AND dateTime BETWEEN {$dateTimeArr[0]} AND {$dateTimeArr[1]})"
        );
        $where = array(
            "d.companyId = {$companyId}",
        );
        if ($mediaid) {
            $where[] = "rd.mediaId = {$mediaid}";
        }
        if ($mediaid) {
            $where[] = "rd.mediaId = {$mediaid}";
        }
        if ($adslotid) {
            $where[] = "rd.adslotId = {$adslotid}";
        }
        if (!empty($dealname)) {
            $where[] = "dealName LIKE '%". $dealname ."%'";
        }
        $group = "d.id";
        if (empty($order)) {
            $order = "d.id DESC";
        }

        return $this->_select($select)->_field($field)->_from($from)->_join($join)->_where($where)->_group($group)->_order($order)->_getBuildSql();

    }

    // 获取交易分页列表
    public function getDealPageList($companyId, $dateTimeArr, $order='', $mediaid=0, $adslotid=0, $dealname='', $throw=0) {
        $sql = $this->getDealListSql($companyId, $dateTimeArr, $order, $mediaid, $adslotid, $dealname, $throw);

        // 分页处理
        $paging = Paging::instance();
        $paging->setPageSize(25);
        $paging->setPageNumKey('pagenum');
        $list = $paging->query($sql);
        return array($list, $paging->data());
    }

    // 获取交易列表
    public function getDealList($companyId, $dateTimeArr) {
        $sql = $this->getDealListSql($companyId, $dateTimeArr);

        return $this->_query($sql);
    }

    // 模糊查询交易名称
    public function getDealNameLike($dealNameLike) {
        $dealNameLike = trim($dealNameLike);
        if (empty($dealNameLike)) {
            return array();
        }
        $field = array(
            "dealName"
        );
        $where = "dealName LIKE '%". $dealNameLike ."%'";

        return $this->_select()->_field($field)->_from()->_where($where)->_query();
    }
}

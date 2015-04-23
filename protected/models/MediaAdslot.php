<?php

/**
 * 应用广告位数据表模型
 * This is the model class for table "{{media_adslot}}".
 *
 * The followings are the available columns in table '{{media_adslot}}':
 * @property integer $id
 * @property string $mediaId
 * @property string $adslotName
 * @property string $adslotIdStr
 * @property string $refreshTime
 * @property integer $deviceType
 * @property string $width
 * @property string $height
 * @property integer $frequencyCapUnit
 * @property integer $frequencyCapAmount
 * @property integer $adtype
 * @property integer $apiFramework
 * @property integer $privateAuction
 * @property integer $status
 * @property integer $mflag
 * @property integer $modificationTime
 * @property integer $creationTime
 */
class MediaAdslot extends DbActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{media_adslot}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
            array('mediaId, adslotName, adslotIdStr, refreshTime, deviceType, width, height, frequencyCapUnit, frequencyCapAmount', 'required'),
			array('deviceType, frequencyCapUnit, frequencyCapAmount', 'numerical', 'integerOnly'=>true),
			array('mediaId, refreshTime, width, height', 'length', 'max'=>11),
			array('adslotName', 'length', 'max'=>64),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, mediaId, adslotName, adslotIdStr, refreshTime, deviceType, width, height, frequencyCapUnit, frequencyCapAmount, adtype, apiFramework, privateAuction, status, mflag, modificationTime, creationTime', 'safe', 'on'=>'search'),
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
			'mediaId' => '应用id',
			'adslotName' => '广告位名称',
			'adslotIdStr' => 'uuid for sdk 唯一吗',
			'refreshTime' => '刷新时间',
			'deviceType' => '设备类型 , 未知unknown = 0,  手机phone = 1, 平板pad = 2',
			'width' => '宽',
			'height' => '高',
			'frequencyCapUnit' => '频率限次，1=每小时，2=每天',
			'frequencyCapAmount' => '频率限次数量',
			'adtype' => 'ad type , unknown = 0, Interstitial = 1, full-screen = 2, complete full screen = 3, banner = 4, topbanner = 5, bottombanner = 6',
			'apiFramework' => 'api_framework , refer to openrtb',
			'privateAuction' => 'refer to openrtb',
			'status' => 'on/off state of this adslot , on = 1',
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
		$criteria->compare('mediaId',$this->mediaId,true);
		$criteria->compare('adslotName',$this->adslotName,true);
		$criteria->compare('adslotIdStr',$this->adslotIdStr,true);
		$criteria->compare('refreshTime',$this->refreshTime,true);
		$criteria->compare('deviceType',$this->deviceType);
		$criteria->compare('width',$this->width,true);
		$criteria->compare('height',$this->height,true);
		$criteria->compare('frequencyCapUnit',$this->frequencyCapUnit);
		$criteria->compare('frequencyCapAmount',$this->frequencyCapAmount);
		$criteria->compare('adtype',$this->adtype);
		$criteria->compare('apiFramework',$this->apiFramework);
		$criteria->compare('privateAuction',$this->privateAuction);
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
	 * @return MediaAdslot the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /*
     *****************************************************************************************************************
     * 以上方法gii脚手架生成
     *****************************************************************************************************************/


    // 自定义验证方法
    // 暂时无用
    public function checkUniqueAdslotName() {
        $adslot = self::model()->find('adslotName=:name', array(':name'=>$this->adslotName));
        if ($adslot) {
            $this->addError('adslotName', '广告位名称已经存在');
        }
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
            $this->adslotIdStr = md5(uniqid(mt_rand(), true) . time());
        }
        $this->modificationTime = $_SERVER['REQUEST_TIME'];
        if(!isset($this->status)) {
            $this->status = 1;
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

    /**
     * 通过应用id获取广告的个数
     * @param $mediaId 应用id
     * @return int
     */
    public function getCountByMediaId($mediaId) {
        $sql  = " ";
        $sql .= "SELECT ";
        $sql .= "COUNT(*) ";
        $sql .= "FROM ";
        $sql .= "{{media_adslot}} ";
        $sql .= "WHERE ";
        $sql .= "mediaId = {$mediaId}";

        $count = $this->_count($sql);
        return $count ? $count : 0;
    }

    // 获取广告列表sql
    public function getAdslotListSql($companyId, $os=0, $dpi='', $dateTimeArr=array(), $order='',  $mediaId=0) {
        $select = "SQL_CALC_FOUND_ROWS";
        $field = array(
            "a.id",
            "a.mediaId",
            "a.adslotName",
            "a.width",
            "a.height",
            "a.developId",
            "a.`status`",
            "m.os",
            "CONCAT(width,'x',height) AS dpi",
            "SUM(cost) AS cost",
            "SUM(bidRequest) AS bidRequest",
            "SUM(impressions) AS impressions",
            "SUM(clicks) AS clicks",
            "IF(SUM(bidRequest), ROUND((SUM(impressions)/SUM(bidRequest) * 100), 2), 0) AS fillingr",
            "IF(SUM(impressions), ROUND((SUM(clicks)/SUM(impressions)*100), 2), 0) AS ctr",
            "IF(SUM(impressions), SUM(cost)/SUM(impressions)/1000, 0) AS ecpm",
            "IF(SUM(clicks), SUM(cost)/SUM(clicks)/1000000, 0) AS ecpc",
        );
        $from = "{{media_adslot}} a";
        $join = array(
            "{{media}} m ON m.id = a.mediaId",
            "{{report_deal_daily}} ra ON (ra.adslotId = a.id AND ra.dateTime BETWEEN {$dateTimeArr[0]} AND {$dateTimeArr[1]})",
        );

        $where = array();
        $where[] =  "m.companyId = {$companyId}";
        $where[] =  "a.`status` IN (1, 2)";
        if (!empty($mediaId)) {
            $where[] =  "a.mediaId = {$mediaId}";
        }
        if (!empty($os)) {
            $where[] =  "m.os = {$os}";
        }
        if (!empty($dpi)) {
            list($width, $height) = explode('x', strtolower($dpi));
            $where[] = "width = {$width}";
            $where[] = "height = {$height}";
        }
        $group = "a.id";
        return $this->_select($select)->_field($field)->_from($from)->_join($join)->_where($where)->_group($group)->_order($order)->_getBuildSql();
    }

    /**
     * 获取广告位的列表
     * @param $companyId 公司id
     * @param int $os 操作系统类型
     * @param $dpi 分辨率 如:320x240
     * @param array $dateTimeArr 起始时间
     * @param string $order 排序
     * @param int $mediaId 媒体id
     * @return array
     */
    public function getMediaPageList($companyId, $os=0, $dpi, $dateTimeArr=array(), $order='',  $mediaId=0) {
        $sql = $this->getAdslotListSql($companyId, $os, $dpi, $dateTimeArr, $order,  $mediaId);

        // 分页处理
        $paging = Paging::instance();
        $paging->setPageSize(5);
        $paging->setPageNumKey('pagenum');
        $list = $paging->query($sql);
        return array($list, $paging->data());
    }

    // 获取所有广告位数据
    public function  getAdslotList($companyId, $dateTimeArr, $mediaId=0, $os=0, $dpi='') {
        $sql = $this->getAdslotListSql($companyId, $os, $dpi, $dateTimeArr, NULL,  $mediaId);
        return $this->_query($sql);
    }

    /**
     * 通过广告id获取广告信息附加应用名称
     * @param $id 广告主键id
     * @return mixed
     */
    public function getAdslotById($id) {
        $sql = "";
        $sql .= "SELECT ";
        $sql .= "a.*, m.appName, m.os ";
        $sql .= "FROM ";
        $sql .= "{{media_adslot}} a ";
        $sql .= "LEFT JOIN {{media}} m ON m.id = a.mediaId ";
        $sql .= "WHERE ";
        $sql .= "a.id = {$id}";

        return $this->_find($sql);
    }

    /**
     * 通过开发者id获取广告位信息
     * @param $developId 开发者id
     * @return mixed
     */
    public function getAdslotByDevelopId($developId) {
        $sql = "";
        $sql .= "SELECT ";
        $sql .= "a.id AS adslotId, a.adslotName, a.width, a.height, a.mediaId, m.appName ";
        $sql .= "FROM {{media_adslot}} a ";
        $sql .= "LEFT JOIN {{media}} m ON m.id = a.mediaId ";
        $sql .= "WHERE ";
        $sql .= "a.developId = {$developId} ";
        $sql .= "ORDER BY mediaId";

        return $this->_query($sql);
    }

    /**
     * 通过应用id集合获取在投的广告位
     * @param $mediaArr 媒体id集合
     * @return mixed
     */
    public function getAdslotByMids($mediaArr) {
        $sql = "";
        $sql .= "SELECT ";
        $sql .= "id * -1 AS id, concat(adslotName,'_', if(width>0,width,'全屏'), if(width>0,'x',''), if(width>0,height,'')) AS name, mediaId AS pid ";
        $sql .= "FROM {{media_adslot}} ";
        $sql .= "WHERE ";
        $sql .= "mediaId IN (". implode(",", $mediaArr) .") AND `status` = 1";

        return $this->_query($sql);
    }

    /**
     * 通过公司id获取该公司下面广告位的数据
     * @param $companyId 公司id
     * @return mixed
     */
    public function getCountByCid($companyId) {
        $field = "COUNT(*)";
        $from = "{{media_adslot}} a";
        $join = "{{media}} m ON m.id = a.mediaId";
        $where = "m.companyId = {$companyId}";

        return $this->_select()->_field($field)->_from($from)->_join($join)->_where($where)->_count();
    }

    /**
     * 通过广告id获取公告信息
     * @param $id 广告id
     * @return mixed
     */
    public function getAdslotInfoByAid($id) {
        $field = array(
            "a.id AS adslotId",
            "a.adslotName",
            "a.width",
            "a.height",
            "a.developId",
            "a.`status`",
            "a.modificationTime",
            "a.creationTime",
            "m.id AS mediaId",
            "m.companyId",
            "m.os",
            "m.appName",
            "m.appIcon, o.osName",
        );
        $from = "{{media_adslot}} a";
        $join = array(
            "{{media}} m ON m.id = a.mediaId",
            "{{base_os}} o ON o.id = m.os",
        );
        $where = "a.id = {$id}";

        return $this->_select()->_field($field)->_from($from)->_join($join)->_where($where)->_find();
    }

    /**
     * 通过广告位的id集合获取这些广告位对应的应用id
     * @param $idArr 广告id集合
     * @return mixed
     */
    public function getMediaIdsByIds($idArr) {
        $field = array("id AS adslotId", "mediaId");
        $where = array(
            "id IN (". implode(", ", $idArr) .")"
        );

        return $this->_select()->_field($field)->_from()->_where($where)->_query();
    }

    // 模糊查询广告位名称
    public function getAdslotNameLike($adslotNameLike, $dealid) {
        $adslotNameLike = trim($adslotNameLike);
        if (empty($adslotNameLike)) {
            return array();
        }
        $field = array(
            "adslotName"
        );
        $from = "ma";
        $where =array();
        $where[] = "adslotName LIKE '%". $adslotNameLike ."%'";
        $where[] = "status IN (1, 2)";
        $join = "";
        if ($dealid) {
            $join = "{{media_adslot_deal}} mad ON mad.adslotId = ma.id";
            $where[] = "mad.dealId = {$dealid}";
        }

        return $this->_select()->_field($field)->_from($from, true)->_join($join)->_where($where)->_query();
    }

}

<?php

/**
 * This is the model class for table "{{operation_log}}".
 *
 * The followings are the available columns in table '{{operation_log}}':
 * @property string $id
 * @property string $userId
 * @property string $operationId
 * @property integer $type
 * @property string $recordId
 * @property string $recordName
 * @property string $params
 * @property integer $createTime
 * @property integer $ip
 */
class OperationLog extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{operation_log}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('userId, operationId, type, recordId, recordName, params, createTime, ip', 'required'),
			array('type, createTime, ip', 'numerical', 'integerOnly'=>true),
			array('userId, operationId', 'length', 'max'=>10),
			array('recordId', 'length', 'max'=>19),
			array('recordName', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, userId, operationId, type, recordId, recordName, params, createTime, ip', 'safe', 'on'=>'search'),
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
				'user' => array(self::BELONGS_TO, 'Admin', 'userId', ),
				'operation' => array(self::BELONGS_TO, 'BaseOperationObject', 'operationId', ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'userId' => '用户ID',
			'operationId' => '操作ID',
			'type' => '类型，1=审核通过，2=审核拒绝',
			'recordId' => '操作的记录的ID',
			'recordName' => '操作的记录的名称',
			'params' => '操作的参数，JSON格式',
			'createTime' => '创建时间',
			'ip' => '客户端IP',
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
		$criteria->compare('userId',$this->userId,true);
		$criteria->compare('operationId',$this->operationId,true);
		$criteria->compare('type',$this->type);
		$criteria->compare('recordId',$this->recordId,true);
		$criteria->compare('recordName',$this->recordName,true);
		$criteria->compare('params',$this->params,true);
		$criteria->compare('createTime',$this->createTime);
		$criteria->compare('ip',$this->ip);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return OperationLog the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	/* 添加记录 */
	public static function add($operationKey, $type, $recordId, $recordName, $params = array())
	{
		static $keyList;
		if(!isset($keyList)) {
			$keyList = array();
			foreach(BaseOperationObject::model()->findAll() as $item) {
				$keyList[$item->key] = $item->id;
			}
		}
		$operationId = $keyList[$operationKey];
		if(!is_numeric($operationId) || $operationId <= 0)
			return false;
	
		$model = new self;
		$user = Yii::app()->user;
		$model->userId = $user->id;
		$userState = $user->getRecord();

		$model->operationId = $operationId;
		$model->type = $type;
		$model->recordId = $recordId;
		$model->recordName = $recordName;
		$model->createTime = $_SERVER['REQUEST_TIME'];
		/* 添加用户邮箱到数据中，方便直接查看数据处理问题 */
		$model->params = CJSON::encode(array_merge(array('userEmail'=>$userState->email),$params));
		$model->ip = ip2long($_SERVER['REMOTE_ADDR']);
		return $model->save();
	}

	/* 根据模型添加记录 */
	public static function addModel($operationKey, $type, $model){
		self::add($operationKey, $type, $model->id, $model->name, $model->attributes);
	}
}

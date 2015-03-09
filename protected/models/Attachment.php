<?php

/**
 * This is the model class for table "{{attachment}}".
 *
 * The followings are the available columns in table '{{attachment}}':
 * @property integer $id
 * @property string $name
 * @property integer $companyId
 * @property integer $campaignId
 * @property integer $adGroupId
 * @property string $metaType
 * @property integer $fileSize
 * @property string $sourcePath
 * @property string $thumbPath
 * @property integer $operatorUID
 * @property integer $creationTime
 */
class Attachment extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{attachment}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, companyId, campaignId, adGroupId, metaType, fileSize, sourcePath, thumbPath, operatorUID, creationTime', 'required'),
			array('companyId, campaignId, adGroupId, fileSize, operatorUID, creationTime', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>64),
			array('metaType, sourcePath, thumbPath', 'length', 'max'=>256),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, companyId, campaignId, adGroupId, metaType, fileSize, sourcePath, thumbPath, operatorUID, creationTime', 'safe', 'on'=>'search'),
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
			'name' => 'Name',
			'companyId' => 'Company',
			'campaignId' => 'Campaign',
			'adGroupId' => 'Ad Group',
			'metaType' => 'Meta Type',
			'fileSize' => 'File Size',
			'sourcePath' => 'Source Path',
			'thumbPath' => 'Thumb Path',
			'operatorUID' => 'Operator Uid',
			'creationTime' => 'Creation Time',
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('companyId',$this->companyId);
		$criteria->compare('campaignId',$this->campaignId);
		$criteria->compare('adGroupId',$this->adGroupId);
		$criteria->compare('metaType',$this->metaType,true);
		$criteria->compare('fileSize',$this->fileSize);
		$criteria->compare('sourcePath',$this->sourcePath,true);
		$criteria->compare('thumbPath',$this->thumbPath,true);
		$criteria->compare('operatorUID',$this->operatorUID);
		$criteria->compare('creationTime',$this->creationTime);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Attachment the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function beforeValidate() {
		parent::beforeValidate();

		if(!isset($this->operatorUID)) {
			$this->operatorUID = Yii::app()->user->id;
		}
		
		if(!isset($this->creationTime)) {
			$this->creationTime = $_SERVER['REQUEST_TIME'];
		}
		
		return true;
	}
	
	public function getLocalSource($absolute=true) {
		if($absolute)
			return Yii::app()->params['uploadPath'].'/upload/'.$this->sourcePath;
		else
			return 'upload/'.$this->sourcePath;
	}
}

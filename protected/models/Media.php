<?php

/**
 * This is the model class for table "{{company}}".
 *
 * The followings are the available columns in table '{{company}}':
 * @property integer $id
 * @property integer $companyId
 * @property integer $os
 * @property string $appName
 * @property integer $appCategory
 * @property string $appBundle
 * @property integer $status
 * @property integer $mflag
 * @property integer $creationTime
 * @property integer $modificationTime
 */
class Company extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{company}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('companyId, os, appName, appCategory, appBundle', 'required'),
			array('companyId, os, appCategory', 'numerical', 'integerOnly'=>true),
			array('appName, ', 'length', 'max'=>32),
			array('telephone', 'length', 'max'=>16),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, companyId, os, appName, appCategory, appBundle', 'safe', 'on'=>'search'),
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
			'companyId' => 'companyId',
			'os' => 'OS',
			'appName' => 'App Name',
			'appCategory' => 'App Category',
			'appBundle' => 'App Bundle',
			'status' => 'Status',
			'mflag' => 'Mflag',
			'creationTime' => 'Creation Time',
			'modificationTime' => 'Update Time',
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
		$criteria->compare('companyId',$this->companyId);
		$criteria->compare('os',$this->os,true);
		$criteria->compare('appName',$this->appName);
		$criteria->compare('appCategory',$this->appCategory);
		$criteria->compare('appBundle',$this->appBundle,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('mflag',$this->mflag);
		$criteria->compare('creationTime',$this->creationTime);
		$criteria->compare('modificationTime',$this->modificationTime);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public function afterFind() {
		parent::afterFind();
	}
	
	public function beforeValidate()
	{
		parent::beforeValidate();
		if(!$this->creationTime) {
			$this->creationTime = $_SERVER['REQUEST_TIME'];
		}
		$this->modificationTime = $_SERVER['REQUEST_TIME'];
		if(!isset($this->status)) {
			$this->status = 2;
		}
		return true;
	}

	public function beforeSave() {
		parent::beforeSave();
	
		$this->mflag = 1;
	
		return true;
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
}

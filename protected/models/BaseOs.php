<?php

/**
 * This is the model class for table "{{base_os}}".
 *
 * The followings are the available columns in table '{{base_os}}':
 * @property integer $id
 * @property integer $parentId
 * @property string $osName
 * @property string $osVersion
 */
class BaseOs extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{base_os}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, parentId, osName, osVersion', 'required'),
			array('id, parentId', 'numerical', 'integerOnly'=>true),
			array('osName', 'length', 'max'=>60),
			array('osVersion', 'length', 'max'=>10),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, parentId, osName, osVersion', 'safe', 'on'=>'search'),
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
			'parentId' => 'Parent',
			'osName' => 'Os Name',
			'osVersion' => 'Os Version',
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
		$criteria->compare('parentId',$this->parentId);
		$criteria->compare('osName',$this->osName,true);
		$criteria->compare('osVersion',$this->osVersion,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return BaseOs the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}

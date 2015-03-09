<?php

/**
 * This is the model class for table "{{company}}".
 *
 * The followings are the available columns in table '{{company}}':
 * @property integer $id
 * @property integer $roleID
 * @property string $companyName
 * @property integer $currency
 * @property integer $timezone
 * @property string $telephone
 * @property integer $postalCode
 * @property integer $country
 * @property integer $city
 * @property integer $state
 * @property string $address
 * @property integer $linkUserId
 * @property integer $mflag
 * @property integer $creationTime
 * @property integer $updateTime
 * @property integer $status
 * @property integer $checkUserId
 * @property integer $checkTime
 * @property integer $validTime
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
			array('roleID, companyName, currency, timezone, creationTime', 'required'),
			array('currency, timezone, postalCode, country, city, state, mflag, creationTime, updateTime, status, checkTime, linkUserId, checkUserId, validTime', 'numerical', 'integerOnly'=>true),
			array('companyName', 'length', 'max'=>32),
			array('telephone', 'length', 'max'=>16),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, roleID, companyName, currency, timezone, telephone, postalCode, country, city, state, address, mflag, creationTime, status, checkTime', 'safe', 'on'=>'search'),
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
			'roleID' => 'Role',
			'companyName' => 'Company Name',
			'currency' => 'Currency',
			'timezone' => 'Timezone',
			'telephone' => 'Telephone',
			'postalCode' => 'Postal Code',
			'country' => 'Country',
			'city' => 'City',
			'state' => 'State',
			'address' => 'Address',
			'linkUserId' => 'LinkMan',
			'mflag' => 'Mflag',
			'creationTime' => 'Creation Time',
			'updateTime' => 'Update Time',
			'status' => 'Status',
			'checkUserId' => 'Check User',
			'checkTime' => 'Check Time',
			'validTime' => 'Valid Time',
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
		$criteria->compare('roleID',$this->roleID);
		$criteria->compare('companyName',$this->companyName,true);
		$criteria->compare('parentCompanyId',$this->parentCompanyId);
		$criteria->compare('budget',$this->budget);
		$criteria->compare('cost',$this->cost);
		$criteria->compare('currency',$this->currency);
		$criteria->compare('timezone',$this->timezone);
		$criteria->compare('website',$this->website,true);
		$criteria->compare('telephone',$this->telephone,true);
		$criteria->compare('postalCode',$this->postalCode);
		$criteria->compare('country',$this->country);
		$criteria->compare('city',$this->city);
		$criteria->compare('state',$this->state);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('category',$this->category);
		$criteria->compare('businessLicense',$this->businessLicense,true);
		$criteria->compare('identityCard',$this->identityCard,true);
		$criteria->compare('identityCard2',$this->identityCard2,true);
		$criteria->compare('mflag',$this->mflag);
		$criteria->compare('creationTime',$this->creationTime);
		$criteria->compare('status',$this->status);
		$criteria->compare('checkPoint',$this->checkPoint);
		$criteria->compare('repairPoint',$this->repairPoint);
		$criteria->compare('checkTime',$this->checkTime);

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
	
	/*
	开始登陆
	return false or current object
	*/
	public function login($data)
	{
		$model = new LoginForm('login');
		$model->attributes = $data;
		return $model->validate();
	}
}

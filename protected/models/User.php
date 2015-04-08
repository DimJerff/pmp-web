<?php

/**
 * This is the model class for table "{{user}}".
 *
 * The followings are the available columns in table '{{user}}':
 * @property integer $id
 * @property integer $roleID
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property string $passwd
 * @property string $telephone
 * @property integer $language
 * @property integer $defaultCompanyID
 * @property integer $lastLoginTime
 * @property integer $creationTime
 */
class User extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{user}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('firstname, lastname, email, passwd, telephone, language, lastLoginTime, creationTime', 'required'),
			array('roleID, language, defaultCompanyID, lastLoginTime, creationTime', 'numerical', 'integerOnly'=>true),
			array('firstname, lastname', 'length', 'max'=>64),
			array('email, passwd', 'length', 'max'=>256),
			array('telephone', 'length', 'max'=>16),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('firstname, lastname, email, telephone', 'safe', 'on'=>'search'),
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
			'role' => array(self::BELONGS_TO, 'AuthRole', 'roleID',),
			'companyList' => array(self::MANY_MANY, 'Company', 'c_relation_userCompany(userId, companyId)'),
			'defaultCompany' => array(self::BELONGS_TO, 'Company', 'defaultCompanyID',),
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
			'firstname' => 'Firstname',
			'lastname' => 'Lastname',
			'email' => 'Email',
			'passwd' => 'Passwd',
			'telephone' => 'Telephone',
			'language' => 'Language',
			'defaultCompanyID' => 'Default Company',
			'lastLoginTime' => 'Last Login Time',
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
		$criteria->compare('roleID',$this->roleID);
		$criteria->compare('firstname',$this->firstname,true);
		$criteria->compare('lastname',$this->lastname,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('passwd',$this->passwd,true);
		$criteria->compare('telephone',$this->telephone,true);
		$criteria->compare('language',$this->language);
		$criteria->compare('defaultCompanyID',$this->defaultCompanyID);
		$criteria->compare('lastLoginTime',$this->lastLoginTime);
		$criteria->compare('creationTime',$this->creationTime);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function beforeSave() {
		if(!isset($this->id)) {
			/* 初始化插入数据 */
			$params = Yii::app()->params;
			$this->passwd = md5(md5($this->passwd));
			if($this->roleID == 0) $this->roleID = $params['defaultRoleId'];
			$this->creationTime = $_SERVER['REQUEST_TIME'];
		}
		return true;
	}
	
	public function currentRoleId() {
		$roleId = $this->roleID;
		if($roleId == 0) $roleId = $this->defaultCompany->roleID;
		return $roleId;
	}

	public function changePasswd($userId, $passwd)
	{
		return self::model()->updateByPk($userId, array(
			'passwd' => md5(md5($passwd)),
		));
	}
	
	/* 设置默认公司ID */
	public function setDefaultCompanyId($companyId) {
		$this->defaultCompanyID = $companyId;
		$relationModel = RelationUserCompany::model();
		$relationModel->updateAll(array('status' => 0), 'userId=:userId', array(':userId' => $this->id));
		$relationModel->updateAll(array('status' => 1), 'userId=:userId AND companyId=:companyId', array(
			':userId' => $this->id,
			':companyId' => $companyId,
		));
		
		UserMessageSetting::model()->find('');
		
		return $this->save();
	}
	
	/* 邀请用户 */
	public function inviteByEmail($formModel) {
		$yii = Yii::app();
		$user = $yii->user;
		$userState = $user->getRecord();
		
		$userInvitation = new UserInvitation;
		$userInvitation->inviterID = $user->id;
		$userInvitation->companyId = $userState->defaultCompanyID;
		$userInvitation->roleID = $formModel->roleID;
		$userInvitation->invitationEmail = $formModel->email;
		$userInvitation->save();
		
		/* 发送邮件 */
		$url = $formModel->user ? 'user/inviteLogin' : 'user/inviteRegister';
		$url = $yii->controller->createAbsoluteUrl($url) . '?' . http_build_query(array(
			'id' => $userInvitation->id,
			'code' => $userInvitation->validationCode,
		));
		$now = $_SERVER['REQUEST_TIME'] + Yii::app()->params['inviteExpireTime'];
		Mail::model()->sendByTemplate('REGISTER_INVITE', $formModel->email, array(
			'platformName' => Yii::app()->controller->domainModel->platformName,
			'COMPANY' => $userState->defaultCompany->companyName,
			'INVITER' => $userState->email,
			'INVITE_URL' => $url,
			'YEAR' => date('Y', $now),
			'MONTH' => date('m', $now),
			'DAY' => date('d', $now),
			'HOUR' => date('H', $now),
			'MINUTE' => date('i', $now),
		));
		
		return true;
	}
}

<?php

/**
 * This is the model class for table "{{auth_role}}".
 *
 * The followings are the available columns in table '{{auth_role}}':
 * @property string $id
 * @property string $name
 * @property string $externalName
 * @property string $roleType
 */
class AuthRole extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{auth_role}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, roleType', 'required'),
			array('name, externalName', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, externalName', 'safe', 'on'=>'search'),
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
			'items' => array(self::MANY_MANY, 'AuthItem', Yii::app()->db->tablePrefix . 'auth_role_item(roleId, itemId)', ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => '内部名称',
			'externalName' => '外部名称',
			'roleType' => '角色类型',
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('externalName',$this->externalName,true);
		$criteria->compare('roleType',$this->roleType,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return AuthRole the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	/* 添加指定角色的关系 */
	public static function addRelation($roleId, $rights)
	{
		if(!$rights || !preg_match('/^([12]\_[1-9]\d*\,)*([12]\_[1-9]\d*)?$/i', $rights)) return false;
		$rightIdList = $insertValueList = array();
		foreach(explode(',', $rights) as $v) {
			list($type, $id) = explode('_', $v);
			$rightIdList[$type][] = $id;
		}
		$authItemModel = AuthItem::model();
		foreach($rightIdList as $type => $idList) {
			$criteria = new CDbCriteria;
			$criteria->addInCondition('categoryId', $idList);
			$criteria->addColumnCondition(array('type' => $type,));
			foreach($authItemModel->findAll($criteria) as $v) {
				$insertValueList[] = '('.$roleId.', '.$v->id.')';
			}
		}
		if($insertValueList) {
			self::model()->getDbConnection()->createCommand("
				INSERT INTO `b_auth_role_item`
					(`roleId`, `itemId`) VALUES
					" . implode(',', $insertValueList)."
			")->execute();
		}
		return true;
	}
	
	/* 根据roleId复制指定角色 */
	public static function copy($roleId)
	{
		$roleId = abs((int)$roleId);
		$model = AuthRole::model()->findByPk($roleId);
		if(!$model) return false;
		$newModel = new AuthRole;
		$newModel->attributes = $model->attributes;
		$newModel->name .= '_副本';
		$newModel->externalName .= '_副本';
		$newModel->save();
		/* 复制成功，继续复制关系 */
		if($newModel->id) {
			$insertValueList = array();
			foreach($model->items as $v) {
				$insertValueList[] = '('.$newModel->id.', '.$v->id.')';
			}
			if($insertValueList) {
				self::model()->getDbConnection()->createCommand("
					INSERT INTO `b_auth_role_item`
						(`roleId`, `itemId`) VALUES
						" . implode(',', $insertValueList)."
				")->execute();
			}
		}
		return $model->id;
	}
	
	/* 根据角色Id删除角色 */
	public static function deleteByRoleId($id)
	{
		$id = abs((int)$id);
		self::model()->deleteByPk($id);
		AuthRoleItem::model()->deleteAllByAttributes(array(
			'roleId' => $id,
		));
	}
}

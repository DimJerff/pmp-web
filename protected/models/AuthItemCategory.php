<?php

/**
 * This is the model class for table "{{auth_item_category}}".
 *
 * The followings are the available columns in table '{{auth_item_category}}':
 * @property string $id
 * @property string $name
 * @property string $parentId
 * @property string $order
 */
class AuthItemCategory extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{auth_item_category}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, order', 'required'),
			array('name', 'length', 'max'=>50),
			array('parentId, order', 'length', 'max'=>10),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, parentId, order', 'safe', 'on'=>'search'),
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
			'name' => '分类名',
			'parentId' => '父级Id',
			'order' => '树状排序，降序',
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
		$criteria->compare('parentId',$this->parentId,true);
		$criteria->compare('order',$this->order,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return AuthItemCategory the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	/* 获取分类树 */
	public static function getTree()
	{
		$criteria = new CDbCriteria;
		$criteria->order = 't.parentId ASC, t.order DESC';
		$rows = self::model()->findAll($criteria);
		return self::buildTree($rows);
	}
	
	private static function buildTree($rows, $parentId = 0)
	{
		$records = array();
		if(!$rows) return $records;
		foreach($rows as $v) 
		{
			$v = $v->attributes;
			if($v['parentId'] == $parentId) {
				$v['subList'] = self::buildTree($rows, $v['id']);
				$records[$v['id']] = $v;
			}
		}
		return $records;
	}
}

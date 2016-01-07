<?php

/**
 * This is the model class for table "{{attachment}}".
 *
 * The followings are the available columns in table '{{attachment}}':
 * @property integer $id
 * @property string $name
 * @property integer $companyId
 * @property integer $mediaId
 * @property integer $adslotId
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
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Attachment the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, companyId, metaType, fileSize, sourcePath, thumbPath, operatorUID, creationTime', 'required'),
			array('companyId, fileSize, operatorUID, creationTime', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>64),
			array('metaType, sourcePath, thumbPath', 'length', 'max'=>256),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, companyId, metaType, fileSize, sourcePath, thumbPath, operatorUID, creationTime', 'safe', 'on'=>'search'),
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
		$criteria->compare('mediaId',$this->mediaId);
		$criteria->compare('adslotId',$this->adslotId);
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
     * 在验证之前压入操作者和操作时间
     * @return bool
     */
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
            return Yii::app()->params['uploadPath'].'/'.Yii::app()->params['uploadDir'].'/'.$this->sourcePath;
        else
            return Yii::app()->params['uploadDir'].'/'.$this->sourcePath;
    }
}

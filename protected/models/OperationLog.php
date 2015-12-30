<?php

/**
 * This is the model class for table "{{operation_log}}".
 *
 * The followings are the available columns in table '{{operation_log}}':
 * @property string $id
 * @property string $userId
 * @property string $model
 * @property string $recordId
 * @property string $url
 * @property string $data
 * @property string $result
 * @property integer $createTime
 * @property string $ip
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
            array('userId, model, recordId, url, data, result, createTime, ip', 'required'),
            array('createTime', 'numerical', 'integerOnly'=>true),
            array('userId, recordId', 'length', 'max'=>11),
            array('model, url, ip', 'length', 'max'=>255),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, userId, model, recordId, url, data, result, createTime, ip', 'safe', 'on'=>'search'),
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
            'id' => '审计ID',
            'userId' => '操作用户ID',
            'model' => '操作表名',
            'recordId' => 'model对应的记录ID',
            'url' => '操作执行的url',
            'data' => 'post提交的数据',
            'result' => '记录到model中的值',
            'createTime' => '操作时间',
            'ip' => '操作的IP',
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
        $criteria->compare('model',$this->model,true);
        $criteria->compare('recordId',$this->recordId,true);
        $criteria->compare('url',$this->url,true);
        $criteria->compare('data',$this->data,true);
        $criteria->compare('result',$this->result,true);
        $criteria->compare('createTime',$this->createTime);
        $criteria->compare('ip',$this->ip,true);

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

    /**
     * 根据模型添加记录
     * @param $model_name 模型的表名
     * @param $id 模型的id
     * @param $info 模型属性数据
     * @param array $params 附加参数数据
     * @return bool
     */
    public static function add($model_name, $id, $info, $params=array())
    {
        if(empty($params)){
            $params = $_REQUEST;
        }
        $request = Yii::app()->request;
        $log = new self;
        $log->userId = Yii::app()->user->id;
        $log->model = $model_name;
        $log->recordId = $id;
        $log->url = $request->getHostInfo().$request->getUrl();
        $log->data = CJSON::encode($params);
        $log->result = CJSON::encode($info);
        $log->createTime = $_SERVER['REQUEST_TIME'];
        $log->ip = $request->getUserHostAddress();
        return $log->save();
    }

    /**
     * 添加model记录
     * @param $model 模型信息
     * @param array $params
     * @return bool
     */
    public static function addModel($model, $params = array())
    {
        if(!is_numeric($model->id))
            return false;
        return self::add(self::getTableName($model), $model->id, $model->attributes, $params);
    }

    /**
     * 解析model的tablename
     * @param $model
     * @return mixed
     */
    private static function getTableName($model){
        $name = $model->tableName();
        $db = Yii::app()->db;
        if($db->tablePrefix!==null && strpos($name,'{{')!==false)
            $realName=preg_replace('/\{\{(.*?)\}\}/',$db->tablePrefix.'$1',$name);
        else
            $realName=$name;
        return $realName;
    }
}

<?php
/**
 * protected/components/Db2ActiveRecord.php
 * User: limei
 * Date: 15-3-25
 * Time: 上午11:36
 */
class Db2ActiveRecord extends CActiveRecord {

    public function getDbConnection()
    {
        if(self::$db!==null)
            return self::$db;
        else
        {
            //这里就是我们要修改的
            self::$db=Yii::app()->getComponent('db2');
            //self::$db=Yii::app()->db2;
            if(self::$db instanceof CDbConnection)
                return self::$db;
            else
                throw new CDbException(Yii::t('yii','Active Record requires a "db2" CDbConnection application component.'));
        }
    }

    /**
     * 通过sql查询一条数据
     * 返回一维数组
     * @param $sql
     * @return mixed
     */
    public function _find($sql) {
        return Yii::app()->db2->createCommand($sql)->queryRow();
    }

    /**
     * 通过sql查询出多条数据
     * 返回二维数组
     * @param $sql
     * @return mixed
     */
    public function _query($sql) {
        return Yii::app()->db2->createCommand($sql)->queryAll();
    }
}
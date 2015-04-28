<?php
/**
 * protected/components/Db2ActiveRecord.php
 * User: limei
 * Date: 15-3-25
 * Time: 上午11:36
 */
class Db2ActiveRecord extends CActiveRecord {
    // 临时拼接sql
    protected $_buildSql = array();

    public function getDbConnection()
    {
        if(self::$db!==null)
            return self::$db;
        else
        {
            //这里就是我们要修改的
            self::$db=Yii::app()->getComponent('db2');
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
    public function _find($sql='') {
        if (empty($sql)) {
            if (!empty($this->_buildSql)) {
                $sql = implode(" ", $this->_buildSql);
                $this->_buildSql = '';
            } else {
                return false;
            }
        }

        return Yii::app()->db2->createCommand($sql)->queryRow();
    }

    /**
     * 通过sql查询出多条数据
     * 返回二维数组
     * @param $sql
     * @return mixed
     */
    public function _query($sql='') {
        if (empty($sql)) {
            if (!empty($this->_buildSql)) {
                $sql = implode(" ", $this->_buildSql);
                $this->_buildSql = '';
            } else {
                return false;
            }
        }

        return Yii::app()->db2->createCommand($sql)->queryAll();
    }

    /**
     * 执行查询 主要针对 SELECT等指令
     * 返回一条数目
     * @access public
     * @param string $str  sql指令
     * @return mixed
     */
    public function _count($sql='') {
        if (empty($sql)) {
            if (!empty($this->_buildSql)) {
                $sql = implode(" ", $this->_buildSql);
                $this->_buildSql = '';
            } else {
                return false;
            }
        }

        return Yii::app()->db2->createCommand($sql)->queryScalar();
    }

    /**
     * 直接返回拼接的sql 不进行查询
     * getBuildSql
     * @return bool|string
     */
    public function _getBuildSql() {
        if (empty($this->_buildSql)) {
            return false;
        }
        $sql = implode(" ", $this->_buildSql);
        if (Yii::app()->db2->tablePrefix) {
            $sql = preg_replace('/{{(.*?)}}/',Yii::app()->db2->tablePrefix.'\1',$sql);
        }

        return $sql;
    }

    // select
    public function _select($select='') {
        if (empty($select)) {
            $this->_buildSql['SELECT'] = 'SELECT';
        } else {
            $this->_buildSql['SELECT'] = 'SELECT ' . $select;
        }

        return $this;
    }

    // field
    public function _field($field) {
        if (is_array($field)) {
            $field = implode(', ', $field);
        } else if (is_string($field)) {
            $field = $field;
        } else {
            $field = "*";
        }
        $this->_buildSql['FIELD'] = $field;

        return $this;
    }

    // from
    public function _from($tableName='', $alias=false) {
        if (empty($tableName)) {
            $this->_buildSql['FROM'] = "FROM " . $this->tableName(). " t";
        } else {
            if ($alias) {
                $this->_buildSql['FROM'] = "FROM " . $this->tableName(). " " . $tableName;
            } else {
                $this->_buildSql['FROM'] = "FROM " . $tableName;
            }
        }

        return $this;
    }

    // join
    public function _join($tableOn) {
        if (is_array($tableOn)) {
            $this->_buildSql['JOIN'] = "LEFT JOIN " . implode(" LEFT JOIN ", $tableOn);
        } else if (is_string($tableOn)) {
            $this->_buildSql['JOIN'] = "LEFT JOIN " . $tableOn;
        }

        return $this;
    }

    // where
    public function _where($whereSql) {
        $where = "WHERE ";
        if (is_array($whereSql)) {
            $where .= implode(' AND ', $whereSql);
        } else if (is_string($whereSql)) {
            $where .= $whereSql;
        } else {
            $where .= "1=1";
        }
        $this->_buildSql['WHERE'] = $where;

        return $this;
    }

    // group
    public function _group($group) {
        $this->_buildSql['GROUP'] = "GROUP BY " . $group;

        return $this;
    }

    // order
    public function _order($order) {
        if (!empty($order)) {
            $this->_buildSql['ORDER'] = "ORDER BY " . $order;
        }

        return $this;
    }

    // limit
    public function _limit($order) {
        $this->_buildSql['LIMIT'] = "LIMIT " . $order;

        return $this;
    }
}
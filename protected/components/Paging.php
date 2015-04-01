<?php
class Paging extends CComponent
{
    const MIN_pageSize = 1;
    const MAX_pageSize = 100;

    public $pageNum = 1;
    public $pageSize = 10;
    public $pageCount;
    public $recordCount;
    // 是否固定分页条目
    protected $_isFixedPageSize = false;

    private $queryParams;
    private $data;
    private $def_method = '_GET';
    private $defPageNumKey = 'pageNum';
    private $defPageSizeKey = 'pageSize';
    public static $queryKeys = array();

    public static function instance()
    {
        static $instance = null;
        if(!isset($instance))
        {
            $instance = new self;
        }
        return $instance;
    }

    /**
     * 设置页面分页显示条码数目
     * @param $pageSize int 条目个数
     */
    public function setPageSize($pageSize) {
        $this->pageSize = $pageSize;
        $this->_isFixedPageSize = true;
    }

    /**
     * 设置分页get提交的参数
     * @param $key
     */
    public function setPageNumKey($key) {
        $this->defPageNumKey = $key;
    }

    /*
        params:
            pageNum: integer
            pageSize: integer
            criteria: CDbCriteria
            model: CActiveRecord
            command: CDbCommand
            countCommand: CDbCommand
    */
    public function query($params)
    {
        if(is_string($params)) {
            $params_tmp = $params;
            $params = array('command' => $params,);
        }

        $methodData = &$GLOBALS[$this->def_method];
        /* 获取传递的page num参数 */
        if(!isset($params['pageNum'])) {
            /* 获取get中的p参数 */
            if(isset($methodData[$this->defPageNumKey]))
                $params['pageNum'] = $methodData[$this->defPageNumKey];
            /* 没有则获取默认值 */
            if(!isset($params['pageNum']))
                $params['pageNum'] = $this->pageNum;
        }

        /* 获取传递的page size参数 */
        if(!isset($params['pageSize'])) {
            /* 获取cookie中的默认page size */
            if(isset(Yii::app()->request->cookies['defps'])) {
                $params['pageSize'] = Yii::app()->request->cookies['defps']->value;
            }
            /* 没值则获取get参数 */
            if(!isset($params['pageSize']))
                $params['pageSize'] = $methodData[$this->defPageSizeKey];
            /* 都没值，则获取默认值 */
            if(!isset($params['pageSize']))
                $params['pageSize'] = $this->pageSize;
        }

        // 如果有限定分页条目重新设定分页
        if ($this->_isFixedPageSize) {
            $params['pageSize'] = $this->pageSize;
        }

        $this->pageNum = abs((int)$params['pageNum']);
        if($this->pageNum < 1) $this->pageNum = 1;
        $this->pageSize = abs((int)$params['pageSize']);
        if($this->pageSize < self::MIN_pageSize || $this->pageSize > self::MAX_pageSize) $this->pageSize = 1;

        $records = array(); $recordCount = null;
        $offset = ($this->pageNum-1) * $this->pageSize;
        /* query by sql */
        if($params['command']) {
            if(is_string($params['command'])) {
                $params['command'] .= " LIMIT {$offset},".$this->pageSize;
                $params['command'] = Yii::app()->db->createCommand($params['command']);
            }
            if(!$params['countCommand'])
                $params['countCommand'] = 'SELECT FOUND_ROWS()';
            if(is_string($params['countCommand']))
                $params['countCommand'] = Yii::app()->db->createCommand($params['countCommand']);
            $records = $params['command']->query()->readAll();
            $recordCount = $params['countCommand']->queryScalar();
            // 异常当pageNum大于总页的情况处理
            if ($recordCount && empty($records) && is_string($params_tmp)) {
                $this->pageNum = ceil($recordCount/$this->pageSize);
                $offset = ($this->pageNum-1) * $this->pageSize;
                $params['command'] = $params_tmp;
                $params['command'] .= " LIMIT {$offset},".$this->pageSize;
                $params['command'] = Yii::app()->db->createCommand($params['command']);
                $records = $params['command']->query()->readAll();
            }
        }else{
            $model = $params['model'];
            $criteria = $params['criteria'];
            $criteria->offset = ($this->pageNum-1) * $this->pageSize;
            $criteria->limit = $this->pageSize;
            $records = $model->findAll($criteria);

            $criteria->limit = $criteria->offset = -1;
            $recordCount = $model->count($criteria);
        }

        $this->recordCount = $recordCount;
        $this->pageCount = ceil($recordCount/$this->pageSize);

        return $records;
    }

    /*
    return paging params
    */
    public function data($footerLength = 10)
    {
        if(!isset($this->data)) {
            $pageNum = $this->pageNum > $this->pageCount ? $this->pageCount : ($this->pageNum <= 0 ? 1 : $this->pageNum);
            $pagePreviours = $pageNum <= 1 ? 1 : $pageNum - 1;
            $pageNext = $pageNum > 1 && $pageNum < $this->pageCount ? $pageNum + 1 : $this->pageCount;

            /* page left and page right */
            $pageLeft = $pageNum - ceil($footerLength / 2);
            if($pageLeft < 1) $pageLeft = 1;
            $pageRight = $pageNum + $footerLength - ($pageNum - $pageLeft);
            if($pageRight > $this->pageCount) $pageRight = $this->pageCount;
            if(($pageRight - $pageLeft) < $footerLength && $pageLeft > 1) {
                $pageLeft -= $footerLength - ($pageRight - $pageLeft);
                if($pageLeft < 1) $pageLeft = 1;
            }

            $this->data = array(
                'pageNum' => $this->pageNum,
                'pageSize' => $this->pageSize,
                'pageCount' => $this->pageCount,
                'recordCount' => $this->recordCount,

                'footerLength' => $footerLength, // 脚步相关页数长度

                'pagePreviours' => $pagePreviours, // 上页页数
                'pageNext' => $pageNext, // 下页页数

                'pageLeft' => $pageLeft, // 页脚起始页数
                'pageRight' => $pageRight, // 页脚结束页数
            );
        }

        return $this->data;
    }

    public function getQueryParams()
    {
        if(!isset($this->queryParams))
        {
            /* 获取参数 */
            $paramKeys = array();
            $controller = Yii::app()->controller;
            $method = new ReflectionMethod(ucfirst($controller->id).'Controller', 'action'.ucfirst($controller->action->id));
            foreach($method->getParameters() as $v)
                $paramKeys[] = $v->name;

            $paramKeys = CMap::mergeArray($paramKeys, self::$queryKeys);

            $methodData = &$GLOBALS[$this->def_method];
            $params = array();
            if(is_array($paramKeys)) {
                $paramKeys = array_merge($paramKeys, array('r', ));
                foreach($paramKeys as $k => $v) {
                    if(!is_numeric($k)) {
                        if(!is_string($v) || !empty($v)) $params[$k] = $v;
                    }elseif(isset($methodData[$v]) && $methodData[$v] !== '') {
                        $params[$v] = $methodData[$v];
                    }
                }
            }
            if(isset($methodData[$this->defPageNumKey]))
                $params['pageNum'] = $this->pageNum;
            if(isset($methodData[$this->defPageSizeKey]))
                $params['pageSize'] = $this->pageSize;
            $this->queryParams = $params;
        }
        return $this->queryParams;
    }

    public function getQueryUrl($params)
    {
        $queryParams = $this->getQueryParams();
        if($params && is_array($params)) {
            foreach($params as $k => $v) {
                if(isset($v)) {
                    $queryParams[$k] = $v;
                }else{
                    unset($queryParams[$k]);
                }
            }
        }
        return '?' . http_build_query($queryParams);
    }
}


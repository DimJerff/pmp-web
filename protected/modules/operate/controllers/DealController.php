<?php
/**
 * 交易管理控制器
 * User: limei
 * Date: 15-3-20
 * Time: 下午1:36
 */

class DealController extends Controller {

    // 不检测权限
    public $noCheckPermission = true;
    public $defaultCompanyID = 0;

    /**
     * 交易页面首页面
     * @param string $time
     */
    public function actionIndex($time='') {
        $this->checkAccess();
        // 获取当前用户的信息
        $yii = Yii::app();
        $user = $yii->user;
        $userState = $user->getRecord();

        // 获取当前用户的默认公司id
        $this->defaultCompanyID = $defaultCompanyId = $userState->defaultCompanyID;

        // 对时间进行判断处理
        if (empty($time)) {
            $time = date('Y/m/d', strtotime('-7 days')) . '-' . date('Y/m/d');
        }
        $timeArr = explode('-', $time);

        // 获取当前公司的信息
        $company = Company::model()->findByPk($defaultCompanyId);

        // 模板分配显示
        $renderData = array(
            'time'    => $time,
            'timeStr' => strtotime($timeArr[0]) . '_' . strtotime($timeArr[1]),
            'company' => $company,
        );
        $this->smartyRender($renderData);
    }

    /**
     * 新建交易页面
     */
    public function actionAdd() {
        $this->checkAccess();
        // 实例化db2中的公司表模型
        $company2Model = Company2::model();
        // 获取公司信息
        $companyCampaignList = $company2Model->getCompanys();
        // 获取上述公司的id集合
        $companyIdArr = array();
        foreach ($companyCampaignList as $k=>$v) {
            $companyIdArr[] = $v['id'];
        }
        // 实例化db2中广告系列列表表数据模型
        $campaign2Model = Campaign2::model();
        // 通过上述公司id集合获取出在投广告系列
        $campaignList = $campaign2Model->getCampaignByCids($companyIdArr);
        // 合并公司列表和广告系列列表
        $companyCampaignList = array_merge($companyCampaignList, $campaignList);
        // 排序
        $companyCampaignList = Data::order($companyCampaignList, 'id', 'pid');

        // 实例化开发者用户对象
        $userModel = User::model();
        $develops = $userModel->findAll();
        $companyModel = Company::model();
        $criteria = new CDbCriteria;
        $criteria->condition='status=:status';
        $criteria->params=array(':status'=>1);
        $companys = $companyModel->findAll($criteria);

        // 模板分配显示
        $this->smartyRender(array(
            'develops' => $develops,
            'companys' => $companys,
            'companyCampaignList' => CJSON::encode($companyCampaignList),
        ));
    }

    /**
     * 编辑交易页面
     * @param $id
     */
    public function actionEdit($id) {
        $this->checkAccess();
        // 实例化db2中的公司表模型
        $company2Model = Company2::model();
        // 获取公司信息
        $companyCampaignList = $company2Model->getCompanys();
        // 获取上述公司的id集合
        $companyIdArr = array();
        foreach ($companyCampaignList as $k=>$v) {
            $companyIdArr[] = $v['id'];
        }
        // 实例化db2中广告系列列表表数据模型
        $campaign2Model = Campaign2::model();
        // 通过上述公司id集合获取出在投广告系列
        $campaignList = $campaign2Model->getCampaignByCids($companyIdArr);
        // 合并公司列表和广告系列列表
        $companyCampaignList = array_merge($companyCampaignList, $campaignList);
        // 排序
        $companyCampaignList = Data::order($companyCampaignList, 'id', 'pid');

        // 实例化开发者用户对象
        $userModel = User::model();
        $develops = $userModel->findAll();
        $companyModel = Company::model();
        $criteria = new CDbCriteria;
        $criteria->condition='status=:status';
        $criteria->params=array(':status'=>1);
        $companys = $companyModel->findAll($criteria);

        // 获取交易信息
        $deal = Deal::model()->findByPk($id);
        // 处理交易中应用和广告位的总数
        $dealAttach['mediaAdslotCount'] = count(CJSON::decode($deal['medias'])) + count(CJSON::decode($deal['adslots']));
        $dealAttach['companyCampaignCount'] = count(CJSON::decode($deal['companies'])) + count(CJSON::decode($deal['campaigns']));

        // 模板分配显示
        $this->smartyRender(array(
            'develops' => $develops,
            'companyCampaignList' => CJSON::encode($companyCampaignList),
            'deal' => $deal,
            'companys' => $companys,
            'dealAttach' => $dealAttach,
        ));
    }

    /**
     * 交易详情页面
     * @param $dealId
     */
    public function actionDetail($dealId, $time='') {
        // 获取当前用户的信息
        $yii = Yii::app();
        $user = $yii->user;
        $userState = $user->getRecord();

        // 获取当前用户的默认公司id
        $this->defaultCompanyID = $defaultCompanyId = $userState->defaultCompanyID;

        // 对时间进行判断处理
        if (empty($time)) {
            $time = date('Y/m/d', strtotime('-7 days')) . '-' . date('Y/m/d');
        }
        $timeArr = explode('-', $time);

        // 获取当前公司的信息
        $company = Company::model()->findByPk($defaultCompanyId);

        $deal = Deal::model()->with('userInfo')->findByPk($dealId);

        $companyNames = array();
        $campaignNames = array();
        if (!empty($deal->companies)) {
            $companyNames = Company2::model()->getCompanyNames(CJSON::decode($deal->companies));
        }

        if (!empty($deal->campaigns)) {
            $campaignNames = Campaign2::model()->getCampaignFullName(CJSON::decode($deal->campaigns));
        }

        $allNames = array();
        foreach ($companyNames as $v) {
            $allNames[] = $v['companyName'];
        }
        foreach ($campaignNames as $v) {
            $allNames[] = $v['campaignFullName'];
        }

        // 模板分配显示
        $renderData = array(
            'time'    => $time,
            'timeStr' => strtotime($timeArr[0]) . '_' . strtotime($timeArr[1]),
            'company' => $company,
            'deal'    => $deal,
            'allNames'=> $allNames,
        );
        $this->smartyRender($renderData);
    }

    // 获取开发者广告信息 TODO
    public function actionGetAdslots($developId) {
        $adslots = MediaAdslot::model()->getAdslotByDevelopId($developId);
        if (empty($adslots)) {
            $this->rspJSON(null);die;
        }

        $data = array();
        foreach($adslots as $k=>$v) {
            if (!isset($data[$v['mediaId']])) {
                $data[$v['mediaId']] = $v;
            }
            $data[$v['mediaId']]['children'][] = $v;
        }

        return $data;
        $this->rspJSON($data);die;
    }

    /**
     * 获取当前公司交易的数目
     */
    public function actionDealCount() {
        // 获取当前用户的默认公司id
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;

        $count = Deal::model()->count("companyId=:companyId",array(":companyId"=>$companyId));
        $count = $count ? $count : 0;
        echo "document.write(". $count . ");";
    }

    /**
     * 获取所有交易的数目
     */
    public function actionDealAllCount() {
        $count = Deal::model()->count();
        $count = $count ? $count : 0;
        echo "document.write(". $count . ");";
    }

    /**
     * 所有交易的消耗报表
     * @param $timestr
     * @throws PHPExcel_Exception
     */
    public function actionExportAll($timestr) {
        // 获取当前用户的默认公司id
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;

        // 查出符合条件的数据
        $records = Deal::model()->getDealList($companyId, Util::_time2Arr($timestr));

        // 查询出来的统计数据进行处理
        $totalRecord = array(
            '0'           => '',
            '1'           => '',
            '2'           => '',
            'ctr'         => 0,
            'totalName'   => '共计',
        );

        foreach($records as $k=>$v) {
            $totalRecord['cost'] += $v['cost'];
            if ($v['dealType'] == 0) {
                $records[$k]['dealTypeStr'] = "公开";
            } else if ($v['dealType'] == 1) {
                $records[$k]['dealTypeStr'] = "私有";
            }

            if ($v['payType'] == 1) {
                $records[$k]['payTypeStr'] = $v['mediaPrice'] . " CPM";
            } elseif ($v['payType'] == 2) {
                $records[$k]['payTypeStr'] = $v['mediaPrice'] . " CPC";
            } elseif ($v['payType'] == 3) {
                $records[$k]['payTypeStr'] = $v['mediaPrice'] . " CPD";
            } elseif ($v['payType'] == 101) {
                $records[$k]['payTypeStr'] = $v['mediaSharingRate'] . "%";
            }
        }


        $timeArr = explode("_", $timestr);

        $title = "交易报表" . date('Y-m-d', $timeArr[0]) . "-" . date('Y-m-d', $timeArr[1]);
        $titleNames = array(
            "交易名称",
            "类型",
            "结算方式",
            "开发者",
            "消耗",
        );
        $recordsNames = array(
            'dealName'    => 'string',
            'dealTypeStr' => 'string',
            'payTypeStr'  => 'string',
            'developName' => 'string',
            'cost'        => 'money',
        );
        $totalRecordNames = array(
            'totalName'   => 'string',
            '0'           => 'string',
            '1'           => 'string',
            '2'           => 'string',
            'cost'        => 'money',
        );

        // excel表格处理
        // 实例化excel类对象
        $report = new ExcelExtend;
        $objPHPExcel = new PHPExcel();
        // 设置excel的属性
        $objPHPExcel->getProperties()->setCreator("limei.com"); // 创建人
        $objPHPExcel->getProperties()->setLastModifiedBy("limei.com"); // 最后修改人
        $objPHPExcel->getProperties()->setTitle($title); // 标题
        // 设置当前的sheet
        $objPHPExcel->setActiveSheetIndex(0);
        // 设置sheet的名称
        $activeSheet = $objPHPExcel->getActiveSheet()->setTitle($title);
        // 初始化当前处理的sheet
        $report->initCurrentSheet($activeSheet);
        // 处理标题
        $report->setHeadSection($titleNames);
        // 处理具体数据
        $report->setBodySection($records, $recordsNames);
        // 处理统计
        $report->setFootSection($totalRecord, $totalRecordNames);
        // 下载
        $report->download($objPHPExcel, $title);
    }

    /**
     * 导出单个交易的报表
     * @param $timestr
     * @param $dealid
     * @throws PHPExcel_Exception
     */
    public function actionExportDeal($timestr, $dealid) {
        // 获取公司id
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;

        // 查询出符合条件的数据
        $records = MediaAdslotDeal::model()->getDealListByDealId(Util::_time2Arr($timestr), $companyId, $dealid);

        // 对查询的数据进行处理以符合excel的要求
        $timeArr = explode("_", $timestr);
        $title = "交易报表" . date('Y-m-d', $timeArr[0]) . "-" . date('Y-m-d', $timeArr[1]);
        $titleNames = array(
            "广告位名称",
            "应用名称",
            "消耗",
            "展示数",
            "点击数",
            "点击率",
            "eCPM",
            "eCPC",
        );
        $recordsNames = array(
            'adslotName'  => 'string',
            'appName'     => 'string',
            'cost'        => 'money',
            'impressions' => 'number',
            'clicks'      => 'number',
            'ctr'         => 'percent',
            'ecpm'        => 'money',
            'ecpc'        => 'money',
        );
        $totalRecord = array(
            'totalName'   => '总计',
            '0'           => '',
            'cost'        => 0,
            'impressions' => 0,
            'clicks'      => 0,
            'ctr'         => 0,
            'ecpm'        => 0,
            'ecpc'        => 0,
        );
        $totalRecordNames = array(
            'totalName'   => 'string',
            '0'           => 'string',
            'cost'        => 'money',
            'impressions' => 'number',
            'clicks'      => 'number',
            'ctr'         => 'percent',
            'ecpm'        => 'money',
            'ecpc'        => 'money',
        );
        foreach($records as $k=>$v) {
            $records[$k]['ctr']          = $v['ctr']/100;
            $records[$k]['fillingr']     = $v['fillingr']/100;
            if (empty($records[$k]['adslotName'])) {
                $records[$k]['adslotName'] = '所有广告位';
            }
            $totalRecord['cost']        += $v['cost'];
            $totalRecord['impressions'] += $v['impressions'];
            $totalRecord['clicks']      += $v['clicks'];
        }
        if ($totalRecord['impressions']) {
            $totalRecord['ctr'] = round($totalRecord['clicks']/$totalRecord['impressions'], 4);
        }
        if ($totalRecord['impressions']) {
            $totalRecord['ecpm'] = $totalRecord['cost']/$totalRecord['impressions']/1000;
        }
        if ($totalRecord['clicks']) {
            $totalRecord['ecpc'] = $totalRecord['cost']/$totalRecord['clicks']/1000000;
        }

        // 实例化excel类添加如数据并返回下载
        $report = new ExcelExtend;
        $objPHPExcel = new PHPExcel();
        // 设置excel的属性
        $objPHPExcel->getProperties()->setCreator("limei.com"); // 创建人
        $objPHPExcel->getProperties()->setLastModifiedBy("limei.com"); // 最后修改人
        $objPHPExcel->getProperties()->setTitle($title); // 标题
        // 设置当前的sheet
        $objPHPExcel->setActiveSheetIndex(0);
        // 设置sheet的名称
        $activeSheet = $objPHPExcel->getActiveSheet()->setTitle($title);
        // 初始化当前处理的sheet
        $report->initCurrentSheet($activeSheet);
        // 处理标题
        $report->setHeadSection($titleNames);
        // 处理具体数据
        $report->setBodySection($records, $recordsNames);
        // 处理统计
        $report->setFootSection($totalRecord, $totalRecordNames);
        // 下载
        $report->download($objPHPExcel, $title);
    }

    /**
     * 导出单个应用下面的交易报表
     * @param $timestr 起始时间戳
     * @param $mediaid 应用id
     * @throws PHPExcel_Exception
     */
    public function actionExportMedia($timestr, $mediaid) {
        // 获取公司id
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;

        // 查询出符合条件的数据
        $records = MediaAdslotDeal::model()->getDealListByMidOrAid(Util::_time2Arr($timestr), $companyId, $mediaid);

        // 对查询的数据进行处理以符合excel的要求
        $timeArr = explode("_", $timestr);
        $title = "交易报表" . date('Y-m-d', $timeArr[0]) . "-" . date('Y-m-d', $timeArr[1]);
        $titleNames = array(
            "交易名称",
            "类型",
            "结算方式",
            "开始日期",
            "结束日期",
            "收入",
            "展示数",
            "点击数",
            "点击率",
        );
        $recordsNames = array(
            'dealName'   => 'string',
            'dealTypeStr'=> 'string',
            'payTypeStr' => 'string',
            'startDate'  => 'string',
            'endDate'    => 'string',
            'cost'       => 'money',
            'impressions'=> 'number',
            'clicks'     => 'number',
            'ctr'        => 'percent',
        );
        $totalRecord = array(
            'totalName'   => '总计',
            '0'           => '',
            '1'           => '',
            '2'           => '',
            '3'           => '',
            'cost'        => 0,
            'impressions' => 0,
            'clicks'      => 0,
            'ctr'         => 0,
        );
        $totalRecordNames = array(
            'totalName'   => 'string',
            '0'           => 'string',
            '1'           => 'string',
            '2'           => 'string',
            '3'           => 'string',
            'cost'        => 'money',
            'impressions' => 'number',
            'clicks'      => 'number',
            'ctr'         => 'percent',
        );
        foreach($records as $k=>$v) {
            $records[$k]['ctr']          = $v['ctr']/100;
            if ($v['dealType'] == 0) {
                $records[$k]['dealTypeStr'] = "公开";
            } else if ($v['dealType'] == 1) {
                $records[$k]['dealTypeStr'] = "私有";
            }

            if ($v['payType'] == 1) {
                $records[$k]['payTypeStr'] = $v['mediaPrice'] . " CPM";
            } elseif ($v['payType'] == 2) {
                $records[$k]['payTypeStr'] = $v['mediaPrice'] . " CPC";
            } elseif ($v['payType'] == 3) {
                $records[$k]['payTypeStr'] = $v['mediaPrice'] . " CPD";
            } elseif ($v['payType'] == 101) {
                $records[$k]['payTypeStr'] = $v['mediaSharingRate'] . "%";
            }

            $records[$k]['startDate'] = date('Y-m-d', $v['startDate']);
            $records[$k]['endDate'] = date('Y-m-d', $v['endDate']);

            $totalRecord['cost']        += $v['cost'];
            $totalRecord['bidRequest']  += $v['bidRequest'];
            $totalRecord['impressions'] += $v['impressions'];
            $totalRecord['clicks']      += $v['clicks'];
        }
        if ($totalRecord['bidRequest']) {
            $totalRecord['fillingr'] = round($totalRecord['impressions']/$totalRecord['bidRequest'], 2);
        }
        if ($totalRecord['impressions']) {
            $totalRecord['ctr'] = round($totalRecord['clicks']/$totalRecord['impressions'], 4);
        }
        if ($totalRecord['impressions']) {
            $totalRecord['ecpm'] = $totalRecord['cost']/$totalRecord['impressions']/1000;
        }
        if ($totalRecord['clicks']) {
            $totalRecord['ecpc'] = $totalRecord['cost']/$totalRecord['clicks']/1000000;
        }

        // 实例化excel类添加如数据并返回下载
        $report = new ExcelExtend;
        $objPHPExcel = new PHPExcel();
        // 设置excel的属性
        $objPHPExcel->getProperties()->setCreator("limei.com"); // 创建人
        $objPHPExcel->getProperties()->setLastModifiedBy("limei.com"); // 最后修改人
        $objPHPExcel->getProperties()->setTitle($title); // 标题
        // 设置当前的sheet
        $objPHPExcel->setActiveSheetIndex(0);
        // 设置sheet的名称
        $activeSheet = $objPHPExcel->getActiveSheet()->setTitle($title);
        // 初始化当前处理的sheet
        $report->initCurrentSheet($activeSheet);
        // 处理标题
        $report->setHeadSection($titleNames);
        // 处理具体数据
        $report->setBodySection($records, $recordsNames);
        // 处理统计
        $report->setFootSection($totalRecord, $totalRecordNames);
        // 下载
        $report->download($objPHPExcel, $title);
    }

    /**
     * 导出单个应用下面的交易报表
     * @param $timestr 起始时间戳
     * @param $adslotid 广告位id
     * @throws PHPExcel_Exception
     */
    public function actionExportAdslot($timestr, $mediaid, $adslotid) {
        // 获取公司id
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;

        // 查询出符合条件的数据
        $records = MediaAdslotDeal::model()->getDealListByMidOrAid(Util::_time2Arr($timestr), $companyId, $mediaid, $adslotid);

        // 对查询的数据进行处理以符合excel的要求
        $timeArr = explode("_", $timestr);
        $title = "交易报表" . date('Y-m-d', $timeArr[0]) . "-" . date('Y-m-d', $timeArr[1]);
        $titleNames = array(
            "交易名称",
            "类型",
            "结算方式",
            "开始日期",
            "结束日期",
            "收入",
            "展示数",
            "点击数",
            "点击率",
        );
        $recordsNames = array(
            'dealName'   => 'string',
            'dealTypeStr'=> 'string',
            'payTypeStr' => 'string',
            'startDate'  => 'string',
            'endDate'    => 'string',
            'cost'       => 'money',
            'impressions'=> 'number',
            'clicks'     => 'number',
            'ctr'        => 'percent',
        );
        $totalRecord = array(
            'totalName'   => '总计',
            '0'           => '',
            '1'           => '',
            '2'           => '',
            '3'           => '',
            'cost'        => 0,
            'impressions' => 0,
            'clicks'      => 0,
            'ctr'         => 0,
        );
        $totalRecordNames = array(
            'totalName'   => 'string',
            '0'           => 'string',
            '1'           => 'string',
            '2'           => 'string',
            '3'           => 'string',
            'cost'        => 'money',
            'impressions' => 'number',
            'clicks'      => 'number',
            'ctr'         => 'percent',
        );
        foreach($records as $k=>$v) {
            $records[$k]['ctr']          = $v['ctr']/100;
            if ($v['dealType'] == 0) {
                $records[$k]['dealTypeStr'] = "公开";
            } else if ($v['dealType'] == 1) {
                $records[$k]['dealTypeStr'] = "私有";
            }

            if ($v['payType'] == 1) {
                $records[$k]['payTypeStr'] = $v['mediaPrice'] . " CPM";
            } elseif ($v['payType'] == 2) {
                $records[$k]['payTypeStr'] = $v['mediaPrice'] . " CPC";
            } elseif ($v['payType'] == 3) {
                $records[$k]['payTypeStr'] = $v['mediaPrice'] . " CPD";
            } elseif ($v['payType'] == 101) {
                $records[$k]['payTypeStr'] = $v['mediaSharingRate'] . "%";
            }

            $records[$k]['startDate'] = date('Y-m-d', $v['startDate']);
            $records[$k]['endDate'] = date('Y-m-d', $v['endDate']);

            $totalRecord['cost']        += $v['cost'];
            $totalRecord['bidRequest']  += $v['bidRequest'];
            $totalRecord['impressions'] += $v['impressions'];
            $totalRecord['clicks']      += $v['clicks'];
        }
        if ($totalRecord['bidRequest']) {
            $totalRecord['fillingr'] = round($totalRecord['impressions']/$totalRecord['bidRequest'], 2);
        }
        if ($totalRecord['impressions']) {
            $totalRecord['ctr'] = round($totalRecord['clicks']/$totalRecord['impressions'], 4);
        }
        if ($totalRecord['impressions']) {
            $totalRecord['ecpm'] = $totalRecord['cost']/$totalRecord['impressions']/1000;
        }
        if ($totalRecord['clicks']) {
            $totalRecord['ecpc'] = $totalRecord['cost']/$totalRecord['clicks']/1000000;
        }

        // 实例化excel类添加如数据并返回下载
        $report = new ExcelExtend;
        $objPHPExcel = new PHPExcel();
        // 设置excel的属性
        $objPHPExcel->getProperties()->setCreator("limei.com"); // 创建人
        $objPHPExcel->getProperties()->setLastModifiedBy("limei.com"); // 最后修改人
        $objPHPExcel->getProperties()->setTitle($title); // 标题
        // 设置当前的sheet
        $objPHPExcel->setActiveSheetIndex(0);
        // 设置sheet的名称
        $activeSheet = $objPHPExcel->getActiveSheet()->setTitle($title);
        // 初始化当前处理的sheet
        $report->initCurrentSheet($activeSheet);
        // 处理标题
        $report->setHeadSection($titleNames);
        // 处理具体数据
        $report->setBodySection($records, $recordsNames);
        // 处理统计
        $report->setFootSection($totalRecord, $totalRecordNames);
        // 下载
        $report->download($objPHPExcel, $title);
    }


    /******************************************************************************************************************
     *
     * 异步ajax方式访问的方法
     *
     */

    /**
     * 异步获取列表信息
     * @param string $timestr
     * @param string $sort
     * @param string $dealname
     * @param int $mediaid
     * @param int $adslotid
     */
    public function actionDealList($timestr='', $sort='', $dealname='', $mediaid=0, $adslotid=0, $throw=0) {
        // 提交的参数处理
        $order = '';
        if (!empty($sort)) {
            $order = str_replace('_', ' ', $sort);
        }
        // 获取应用表模型
        $dealModel = Deal::model();
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;
        list($records, $pagingData) = $dealModel
            ->getDealPageList($companyId, Util::_time2Arr($timestr), $order, $mediaid, $adslotid, $dealname, $throw);

        // 模板分配显示
        $html = $this->smartyRender(array(
            'records' => $records,
            'pagingData' => $pagingData,
            'amount'     => Util::listAmount($records),
            'ajaxFun' => 'ajaxDealPage'
        ), null, true);
        $data = array('html' => $html);
        $this->rspJSON($data);
    }

    /**
     * 异步获取应用或者广告位下所有的交易列表
     * @param string $timestr
     * @param string $sort
     * @param string $dealname
     * @param int $mediaid
     * @param int $adslotid
     */
    public function actionDealReList($timestr='', $sort='', $dealname='', $mediaid=0, $adslotid=0, $throw=0) {
        // 对提交过来的参数进行处理
        if (!empty($sort)) {
            $order = str_replace('_', ' ', $sort);
        }
        if (empty($mediaid)) {
            $adslot = MediaAdslot::model()->findByPk($adslotid);
            $mediaid = $adslot->mediaId;
        }

        // 获取公司id
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;

        // 实例化交易关系表模型
        $model = MediaAdslotDeal::model();

        // 获取关系列表数据
        list($records, $pagingData) = $model->getDealByMidOrAid(Util::_time2Arr($timestr), $companyId, $mediaid, $adslotid, $order, $throw);

        // 模板分配显示
        $html = $this->smartyRender(array(
            'records' => $records,
            'pagingData' => $pagingData,
            'amount'     => Util::listAmount($records),
            'ajaxFun' => 'ajaxDealRePage'
        ), null, true);
        $data = array('html' => $html);
        $this->rspJSON($data);
    }

    /**
     * 异步获取应用下所有的广告位信息
     * @param $dealid
     * @param $timestr
     * @param string $sort
     * @param string $adslotname
     */
    public function actionDealAdList($dealid, $timestr, $sort='', $adslotname='', $throw=0) {
        // 对提交过来的参数进行处理
        if (!empty($sort)) {
            $order = str_replace('_', ' ', $sort);
        }

        // 获取公司id
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;

        // 实例化交易关系表模型
        $model = MediaAdslotDeal::model();

        // 获取关系列表数据
        list($records, $pagingData) = $model->getAdslotDataByDealId(Util::_time2Arr($timestr), $companyId, $dealid, $order, $adslotname, $throw);

        // 模板分配显示
        $html = $this->smartyRender(array(
            'records'    => $records,
            'pagingData' => $pagingData,
            'amount'     => Util::listAmount($records),
            'ajaxFun'    => 'ajaxDealAdPage'
        ), null, true);
        $data = array('html' => $html);
        $this->rspJSON($data);
    }

    /**
     * 异步改变交易状态
     * @param $id
     * @param $status
     */
    public function actionChange_status($id, $status) {
        /*
        $model = Deal::model()->findByPk($id);
        $data = array();
        $data['id'] = $id;
        $data['status'] = $status;
        $model->attributes = $data;

        if ($model->save()) {
            $this->rspJSON();
        } else {
            $this->rspErrorJSON(304, "状态修改失败");
        }
        */
        $count = Deal::model()->updateByPk($id, array('status'=>$status));
        if ($count > 0) {
            $this->rspJSON();
        } else {
            $this->rspErrorJSON(304, "状态修改失败");
        }
    }

    // 异步修改交易详情项状态
    public function actionDetailChange_status($status, $dealId, $mediaId, $adslotId=0) {
        $attributes = array('status' => (int)$status);
        $condition = "dealId=:dealId AND mediaId=:mediaId AND adslotId=:adslotId";
        $params = array(
            ":dealId" => (int)$dealId,
            ":mediaId" => (int)$mediaId,
            ":adslotId" => (int)$adslotId,
        );
        $count = MediaAdslotDeal::model()->updateAll($attributes,$condition,$params);
        if ($count > 0) {
            $this->rspJSON();
        } else {
            $this->rspErrorJSON(304, "状态修改失败");
        }
    }

    /**
     * 异步获取交易名
     * @param $name
     */
    public function actionDealNameSearch($name) {
        $names = array();

        // 实例化交易表模型
        $model = Deal::model();

        // 查询交易名称
        $result = $model->getDealNameLike($name);
        if (!empty($result)) {
            foreach($result as $v) {
                $names[] = $v['dealName'];
            }
        }

        $this->rspJSON($names);
    }

    /******************************************************************************************************************
     *
     * post提交数据的方式访问的方法
     *
     */

    /**
     * 处理应用提交的表单数据
     */
    public function actionPostData() {
        $postData = $_POST['deal'];
        if ($postData) {
            // 获取广告位数据模型实例
            if (isset($postData['id']) && !empty($postData['id'])) {
                $model = Deal::model()->findByPk($postData['id']);
                $operationType = 4;
            } else {
                $model = new Deal();
                $operationType = 3;
            }

            $model->attributes = $this->_dealDataBeforeValidate($postData);
            if ($model->validate()) {
                $transaction = Yii::app()->db->beginTransaction(); //开启事务
                $model->save();
                $r = MediaAdslotDeal::model()
                    ->upDealRelation($model->attributes['id'], CJSON::decode($model->attributes['medias']), CJSON::decode($model->attributes['adslots']));
                if ($r) {
                    $transaction->commit(); //提交事务
                } else {
                    $transaction->rollback(); //回滚事务

                }
                $data = array();
                if (isset($postData['id']) && !empty($postData['id'])) {
                    // Noting to do
                } else {
                    // Noting to do
                }
            } else {
                $errors = $model->getErrors();
            }
            if (empty($errors)) {
                // 记录操作日记
                OperationLog::model()->add("deal", $operationType, $model->id, $model->dealName, $model->attributes);
            }

            $errors ? $this->rspJSON($errors,'error') : $this->rspJSON($data);die;
        }
    }


    /******************************************************************************************************************
     *
     * 不对外访问的私有方法
     *
     */

    /**
     * 提交的数据验证前的处理
     * @param $data
     * @return mixed
     */
    private function _dealDataBeforeValidate($data) {
        $data['startDate'] = strtotime($data['startDate']);
        $data['endDate'] = strtotime($data['endDate']);

        if ($data['_mediaPrice_mediaSharingRate']) {
            $data['mediaPrice'] = 0;
            $data['payType'] = $data['_mediaPrice_mediaSharingRate'];
        } else {
            $data['mediaSharingRate'] = 0;
        }

        return $data;
    }
}
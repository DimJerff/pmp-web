<?php
/**
 * 广告位控制器
 * User: limei
 * Date: 15-3-18
 * Time: 下午3:38
 */
class AdslotController extends Controller {

    // 不检测权限
    public $noCheckPermission = TRUE;
    // 默认公司id
    public $defaultCompanyID = 0;


    /******************************************************************************************************************
     *
     * 直接通过浏览器地址栏get方式访问的方法
     *
     */
    // 广告详情页面
    public function actionDetail($adslotId, $time='') {
        // 对时间进行判断处理
        if (empty($time)) {
            $time = date('Y/m/d', strtotime('-7 days')) . '-' . date('Y/m/d');
        }
        $timeArr = explode('-', $time);

        // 实例化广告表实例模型
        $adslotModel = MediaAdslot::model();
        $adslot = $adslotModel->getAdslotInfoByAid($adslotId);

        // 模板分配显示
        $this->smartyRender(array(
            'adslot' => $adslot,
            'time'    => $time,
            'timeStr' => strtotime($timeArr[0]) . '_' . strtotime($timeArr[1]),
        ));
    }

    // 添加广告位页面
    public function actionAdd($mediaId) {
        $this->checkAccess();
        // 从配置文件中获取设定的设备分辨率
        $deviceDpi = Yii::app()->params['deviceDpi'];

        // 获取当前操作的应用信息
        $media = Media::model()->findByPk($mediaId);

        // 模板分配显示
        $this->smartyRender(array(
            'deviceDpi' => $deviceDpi,
            'media'     => $media,
        ));
    }

    // 广告编辑页面
    public function actionEdit($id) {
        $this->checkAccess();
        // 从配置文件中获取设定的设备分辨率
        $deviceDpi = Yii::app()->params['deviceDpi'];

        // 获取当前广告位信息
        $adslot = MediaAdslot::model()->getAdslotById($id);

        // 检测当前广告位分辨率是否为自定义
        $adslot['_widthHeight'] = "";
        foreach ($deviceDpi[$adslot['deviceType']]['type'] as $k=>$v) {
            if ($v[0] == $adslot['width'] && $v[1] == $adslot['height']) {
                $adslot['_widthHeight'] = $v[0] . ',' . $v[1];
            }
        }

        $this->smartyRender(array(
            'deviceDpi' => $deviceDpi,
            'adslot'     => $adslot,
        ));
    }

    // 获取当前公司广告位的数目
    public function actionAdslotCount() {
        // 获取当前用户的默认公司id
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;

        // 广告位数据
        $count = MediaAdslot::model()->getCountByCid($companyId);
        echo "document.write(". $count . ");";
    }

    // 所有广告位的消耗报表
    public function actionExportAll($timestr, $mediaid=0) {
        // 获取当前用户的默认公司id
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;
        $records = MediaAdslot::model()->getAdslotList($companyId, explode("_", $timestr), $mediaid);
        $totalRecord = array(
            '0'           => '',
            'cost'        => 0,
            'bidRequest'  => 0,
            'impressions' => 0,
            'clicks'      => 0,
            'fillingr'    => 0,
            'ctr'         => 0,
            'ecpm'        => 0,
            'ecpc'        => 0,
            'totalName'   => '共计',
        );

        foreach($records as $k=>$v) {
            $totalRecord['cost'] += $v['cost'];
            $totalRecord['bidRequest'] += $v['bidRequest'];
            $totalRecord['impressions'] += $v['impressions'];
            $totalRecord['clicks'] += $v['clicks'];
            $records[$k]['ctr'] = $records[$k]['ctr']/100;
            if ($records[$k]['width'] == "-1") {
                $records[$k]['dpi'] = '全屏';
            }
        }

        if ($totalRecord['bidRequest']) {
            $totalRecord['fillingr'] = round($totalRecord['impressions']/$totalRecord['bidRequest'] * 100, 2);
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

        $timeArr = explode("_", $timestr);

        $title = "广告位报表" . date('Y-m-d', $timeArr[0]) . "-" . date('Y-m-d', $timeArr[1]);
        $titleNames = array(
            "广告位名称",
            "尺寸",
            "收入",
            "请求数",
            "展示数",
            "填充率",
            "点击数",
            "点击率",
            "eCPM",
            "eCPC",
        );
        $recordsNames = array(
            'adslotName'  => 'string',
            'dpi'         => 'string',
            'cost'        => 'money',
            'bidRequest'  => 'number',
            'impressions' => 'number',
            'fillingr'    => 'percent',
            'clicks'      => 'number',
            'ctr'         => 'percent',
            'ecpm'        => 'money',
            'ecpc'        => 'money',
        );
        $totalRecordNames = array(
            'totalName'   => 'string',
            '0'           => 'string',
            'cost'        => 'money',
            'bidRequest'  => 'number',
            'impressions' => 'number',
            'fillingr'    => 'percent',
            'clicks'      => 'number',
            'ctr'         => 'percent',
            'ecpm'        => 'money',
            'ecpc'        => 'money',
        );
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


    /******************************************************************************************************************
     *
     * post提交数据的方式访问的方法
     *
     */

    /**
     * 处理应用提交的表单数据
     */
    public function actionPostAdslot() {
        if ($_POST['adslot']) {
            // 获取广告位数据模型实例
            if (isset($_POST['adslot']['id']) && !empty($_POST['adslot']['id'])) {
                $adslotModel = MediaAdslot::model()->findByPk($_POST['adslot']['id']);
            } else {
                $adslotModel = new MediaAdslot();
            }

            $adslotModel->attributes = $this->_dealDataBeforeValidate($_POST['adslot']);
            if ($adslotModel->validate()) {
                $adslotModel->save();
                $data = array();
                if (isset($_POST['adslot']['id']) && !empty($_POST['adslot']['id'])) {
                    // Noting to do
                } else {
                    $data['id'] = $adslotModel->attributes['id'];
                    $data['url'] = '/develop/down/sdk/adslotId/' . $data['id'];
                }
            } else {
                $errors = $adslotModel->getErrors();
            }
            $errors ? $this->rspJSON($errors,'error') : $this->rspJSON($data);die;
        }
    }


    /******************************************************************************************************************
     *
     * 异步ajax方式访问的方法
     *
     */

    /**
     * 广告位列表
     */
    public function actionAdslotList($ostype=0, $timestr='', $sort='', $dpi='', $mediaid=0) {
        // 提交的参数处理
        $order = '';
        if (!empty($sort)) {
            $order = str_replace('_', ' ', $sort);
        }
        // 获取应用表模型
        $adslotModel = MediaAdslot::model();
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;
        list($records, $pagingData) = $adslotModel->getMediaPageList($companyId, $ostype, $dpi, explode("_", $timestr), $order, $mediaid);

        // 模板分配显示
        $html = $this->smartyRender(array(
            'records' => $records,
            'pagingData' => $pagingData,
            'amount'     => Util::listAmount($records),
            'ajaxFun' => 'ajaxAdslotPage'

        ), null, true);
        $data = array('html' => $html);
        $this->rspJSON($data);
    }

    // 改变广告位状态
    public function actionChange_status($id, $status) {
        $model = MediaAdslot::model()->findByPk($id);
        $model->status = $status;

        if ($model->save()) {
            $this->rspJSON();
        } else {
            $this->rspErrorJSON(304, "状态修改失败");
        }
    }

    // 异步获取广告位名称
    public function actionAdslotNameSearch($name) {
        $names = array();

        // 实例化交易表模型
        $model = MediaAdslot::model();

        // 查询交易名称
        $result = $model->getAdslotNameLike($name);
        if (!empty($result)) {
            foreach($result as $v) {
                $names[] = $v['adslotName'];
            }
        }

        $this->rspJSON($names);
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
        // 处理宽高信息
        $widthHeight = $data['_widthHeight' . $data['deviceType']];
        if ($widthHeight) {
            $widthHeightArr = explode(",", $widthHeight); unset($widthHeight);
            $data['width'] = $widthHeightArr[0];
            $data['height'] = $widthHeightArr[1];
        }

        // 处理频次
        if ($data['_frequencyCapUnitCapAmount'] == -1) {
            $data['frequencyCapUnit'] = -1;
            $data['frequencyCapAmount'] = -1;
        }
        // 过滤无用的数据
        return Util::filterUnderlineKey($data);
    }



}
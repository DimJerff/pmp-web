<?php
/**
 * 应用控制器
 */
class MediaController extends Controller {

    // 不检测权限
    public $noCheckPermission = TRUE;
    // 默认公司id
    public $defaultCompanyID = 0;


    /******************************************************************************************************************
     *
     * 直接通过浏览器地址栏get方式访问的方法
     *
     */

    /**
     * 应用详情页面
     * @param $id 应用id
     */
    public function actionDetail($id, $time='') {
        $this->checkAccess();
        // 对时间进行判断处理
        if (empty($time)) {
            $time = date('Y/m/d', strtotime('-7 days')) . '-' . date('Y/m/d');
        }
        $timeArr = explode('-', $time);

        // 实例化应用数据模型
        $mediaModel = Media::model();
        // 通过主键id获取当前应用的信息
        $media = $mediaModel->getMediaById($id);

        // 实例化广告位
        $adslotModel = MediaAdslot::model();
        // 获取当前广告位的个数
        $adslotCount = $adslotModel->getCountByMediaId($id);

        // 模板分配显示
        $this->smartyRender(array(
            'media'       => $media,
            'adslotCount' => $adslotCount,
            'time'    => $time,
            'timeStr' => strtotime($timeArr[0]) . '_' . strtotime($timeArr[1]),
        ));
    }

    /**
     * 添加应用页面
     */
    public function actionAdd() {
        $this->checkAccess();
        // 获取全部类别树
        $categoryTree = BaseMediaCategory::model()->getAllCateToTree();

        // 模板分配显示
        $this->smartyRender(array(
            'categoryTree' => CJSON::encode($categoryTree),
        ));
    }

    // 添加应用成功后判定跳转的页面
    public function actionAddSuccessPage() {
        $cookie = Yii::app()->request->getCookies();
        $id = $cookie['newMediaId']->value;
        if ($id) {
            unset($cookie['newMediaId']);
            $this->redirect(array('adslot/add','mediaId'=>$id));
        } else {
            $this->redirect(array('site/index'));
        }
    }

    /**
     * 应用编辑页面
     * @param $id 应用id
     */
    public function actionEdit($id) {
        $this->checkAccess();
        // 获取当前编辑的应用信息
        $mediaModel = Media::model();
        $media = $mediaModel->findByPk($id);

        // 获取全部类别树
        $categoryTree = BaseMediaCategory::model()->getAllCateToTree();

        // 处理当前已经选中的类别树
        $curCatePathStr = '';
        foreach ($categoryTree as $v) {
            if ($v['id'] == $media['appCategory']) {
                $curCatePathStr = $v['Path'];
                break;
            }
        }
        $curCatePathArr = explode('-', $curCatePathStr);unset($curCatePathStr);
        array_shift($curCatePathArr);

        // 模板分配显示
        $this->smartyRender(array(
            'categoryTree' => CJSON::encode($categoryTree),
            'media'        => $media,
            'curCatePath'  => $curCatePathArr,
        ));
    }

    /**
     * 获取单个公司的应用数目
     */
    public function actionMediaCount() {
        // 获取当前用户的默认公司id
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;

        $count = Media::model()->count("companyId=:companyId",array(":companyId"=>$companyId));
        $count = $count ? $count : 0;
        echo "document.write(". $count . ");";
    }

    // 报表下载
    public function actionExportAll($timestr, $os=0) {
        // 获取当前用户的默认公司id
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;
        $records = Media::model()->getMediaList($companyId, explode("_", $timestr), $os);
        $totalRecord = array(
            'adslotCount' => 0,
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
            $totalRecord['adslotCount'] += $v['adslotCount'];
            $totalRecord['cost'] += $v['cost'];
            $totalRecord['bidRequest'] += $v['bidRequest'];
            $totalRecord['impressions'] += $v['impressions'];
            $totalRecord['clicks'] += $v['clicks'];
            $records[$k]['ctr'] = $records[$k]['ctr']/100;
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

        $title = "应用报表" . date('Y-m-d', $timeArr[0]) . "-" . date('Y-m-d', $timeArr[1]);
        $titleNames = array(
            "应用名称",
            "广告位",
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
            'appName'     => 'string',
            'adslotCount' => 'number',
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
            'adslotCount' => 'number',
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
    public function actionPostMedia() {
        if ($_POST['media']) {
            $errors = null;
            if (isset($_POST['media']['id']) && !empty($_POST['media']['id'])) {
                $mediaModel = Media::model()->findByPk($_POST['media']['id']);
                $operationType = 4;
            } else {
                $mediaModel = new Media();
                $operationType = 3;
            }
            $mediaModel->attributes = $_POST['media'];
            $mediaModel->companyId = Yii::app()->user->getRecord()->defaultCompanyID;
            if ($mediaModel->validate()) {
                $mediaModel->save();
                if ($_POST['media']['addadslot']) {
                    $id=$mediaModel->primaryKey;
                    $cookie = new CHttpCookie('newMediaId',$id);
                    $cookie->expire = time()+180;
                    Yii::app()->request->cookies['newMediaId']=$cookie;
                }
                // 记录操作日记
                OperationLog::model()->add("media", $operationType, $mediaModel->id, $mediaModel->appName, $mediaModel->attributes);
            } else {
                $errors = $mediaModel->getErrors();

            }
            $errors ? $this->rspJSON($errors,'error') : $this->rspJSON(null);die;
        }
    }


    /******************************************************************************************************************
     *
     * 异步ajax方式get方式访问的方法
     *
     */

    /**
     * 应用列表
     */
    public function actionAppList($ostype = 0, $timestr='', $sort = '') {
        // 获取应用表模型
        $mediaModel = Media::model();

        // 提交的参数处理
        if (!empty($sort)) {
            $order = str_replace('_', ' ', $sort);
        }

        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;
        list($records, $pagingData) = $mediaModel->getMediaPageList($companyId, $ostype, explode("_", $timestr), $order);

        // 模板分配显示
        $html = $this->smartyRender(array(
            'records'    => $records,
            'pagingData' => $pagingData,
            'amount'     => Util::listAmount($records),
            'ajaxFun'    => 'ajaxAppPage'
        ), null, true);
        $data = array('html' => $html);
        $this->rspJSON($data);
    }

    // 通过提交的开发者id获取开发者应用
    public function actionGetMediaByDevelopId($developId) {
        $mediaModel = Media::model();
        $list = $mediaModel->getMediaDealByDid($developId);
        if (empty($list)) {
            $this->rspJSON($list);die;
        }

        $mediaIds = array();
        foreach ($list as $k=>$v) {
            $mediaIds[] = $v['id'];
        }

        $adslotModel = MediaAdslot::model();
        $list2 = $adslotModel->getAdslotByMids($mediaIds);
        $list = array_merge($list, $list2);
        $list = Data::order($list, 'id', 'pid');

        $this->rspJSON($list);
    }

    // 通过提交的开发者id获取开发者应用
    public function actionGetMediaByCompanyId($companyId) {
        $mediaModel = Media::model();
        $list = $mediaModel->getMediaDealByCid($companyId);
        if (empty($list)) {
            $this->rspJSON($list);die;
        }

        $mediaIds = array();
        foreach ($list as $k=>$v) {
            $mediaIds[] = $v['id'];
        }

        $adslotModel = MediaAdslot::model();
        $list2 = $adslotModel->getAdslotByMids($mediaIds);
        $list = array_merge($list, $list2);
        $list = Data::order($list, 'id', 'pid');

        $this->rspJSON($list);
    }

    // 改变广告位状态
    public function actionChange_status($id, $status) {
        $model = Media::model()->findByPk($id);
        $model->status = $status;

        if ($model->save()) {
            $this->rspJSON();
        } else {
            $this->rspErrorJSON(304, "状态修改失败");
        }
    }


    /******************************************************************************************************************
     *
     * 异步ajax方式post方式访问的方法
     *
     */

    /**
     * 应用图标上传
     */
    public function actionIconupload() {
        // 获取用户信息
        $userState = Yii::app()->user->getRecord();

        // 实例化应用图片上传类
        $model = new MediaFile();

        // 获取图片上传实例对象
        $model->instance = CUploadedFile::getInstanceByName('file');
        // 添加附件入附件库信息
        $result = $model->save(array(
            'companyId' => $userState->defaultCompanyID,
        ));

        // 返回上传信息
        if($result) {
            $result['path'] = $model->path;
            $result['urlPath'] = $model->urlPath;
            $result['thumbUrlPath'] = $model->thumbUrlPath;
            $this->rspJSON($result);
        }else{
            $this->rspErrorJSON(403, $model->showError());
        }
    }
}

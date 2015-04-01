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
        // 获取全部类别树
        $categoryTree = BaseMediaCategory::model()->getAllCateToTree();

        // 模板分配显示
        $this->smartyRender(array(
            'categoryTree' => CJSON::encode($categoryTree),
        ));
    }

    /**
     * 应用编辑页面
     * @param $id 应用id
     */
    public function actionEdit($id) {
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
     * @param $companyId 公司id
     */
    public function actionMediaCount($companyId) {
        $count = Media::model()->count("companyId=:companyId",array(":companyId"=>$companyId));
        $count = $count ? $count : 0;
        echo "document.write(". $count . ");";
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
            } else {
                $mediaModel = new Media();
            }
            $mediaModel->attributes = $_POST['media'];
            $mediaModel->companyId = Yii::app()->user->getRecord()->defaultCompanyID;
            if ($mediaModel->validate()) {
                $mediaModel->save();
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
        $lists = $mediaModel->getMediaPageList($companyId, $ostype, explode("_", $timestr), $order);

        // 模板分配显示
        $html = $this->smartyRender(array(
            'records' => $lists['list'],
            'pagingData' => $lists['page'],
            'ajaxFun' => 'ajaxAppPage'
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
            $this->rspErrorJSON(403, $model->getError());
        }
    }
}

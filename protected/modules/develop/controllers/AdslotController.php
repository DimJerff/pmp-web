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
    public function actionAdslotCount($companyId) {
        // 广告位数据
        $count = MediaAdslot::model()->getCountByCid($companyId);
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
        if (!empty($sort)) {
            $order = str_replace('_', ' ', $sort);
        }

        // 获取应用表模型
        $adslotModel = MediaAdslot::model();
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;
        $lists = $adslotModel->getMediaPageList($companyId, $ostype, $dpi, explode("_", $timestr), $order, $mediaid);

        // 模板分配显示
        $html = $this->smartyRender(array(
            'records' => $lists['list'],
            'pagingData' => $lists['page'],
            'ajaxFun' => 'ajaxAdslotPage'

        ), null, true);
        $data = array('html' => $html);
        $this->rspJSON($data);
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
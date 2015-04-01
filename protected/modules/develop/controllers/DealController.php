<?php
/**
 * 交易管理控制器
 * User: limei
 * Date: 15-3-20
 * Time: 下午1:36
 */

class DealController extends Controller {

    // 不检测权限
    public $noCheckPermission = TRUE;
    public $defaultCompanyID = 0;

    // 交易页面首页面
    public function actionIndex() {
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

        // 今日消耗
        $todayCost = ReportMediaDaily::model()->getTodayCost($defaultCompanyId);
        // 累计消耗
        $allCost = ReportMediaDaily::model()->getAllCost($defaultCompanyId);

        // 模板分配显示
        $this->smartyRender(array(
            'time'    => $time,
            'timeStr' => strtotime($timeArr[0]) . '_' . strtotime($timeArr[1]),
            'company' => $company,
            'todayCost' => $todayCost,
            'allCost' => $allCost,
        ));
    }

    // 新建交易页面
    public function actionAdd() {
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

        // 模板分配显示
        $this->smartyRender(array(
            'develops' => $develops,
            'companyCampaignList' => CJSON::encode($companyCampaignList),
        ));
    }

    // 编辑交易页面
    public function actionEdit($id) {
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
            'dealAttach' => $dealAttach,
        ));
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
     * @param $companyId 公司id
     */
    public function actionDealCount($companyId) {
        $count = Deal::model()->count("companyId=:companyId",array(":companyId"=>$companyId));
        $count = $count ? $count : 0;
        echo "document.write(". $count . ");";
    }


    /******************************************************************************************************************
     *
     * 异步ajax方式访问的方法
     *
     */

    // 异步获取列表信息
    public function actionDealList($timestr='', $sort='', $dealname='', $mediaid=0, $adslotid=0) {
        // 提交的参数处理
        if (!empty($sort)) {
            $order = str_replace('_', ' ', $sort);
        }

        // 获取应用表模型
        $dealModel = Deal::model();
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;
        $lists = $dealModel->getDealPageList($companyId, explode("_", $timestr), $order, $mediaid, $adslotid);

        // 模板分配显示
        $html = $this->smartyRender(array(
            'records' => $lists['list'],
            'pagingData' => $lists['page'],
            'ajaxFun' => 'ajaxDealPage'

        ), null, true);
        $data = array('html' => $html);
        $this->rspJSON($data);
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
            } else {
                $model = new Deal();
            }

            $model->attributes = $this->_dealDataBeforeValidate($postData);
            if ($model->validate()) {
                $transaction = Yii::app()->db->beginTransaction(); //开启事务
                $model->save();
                $r = MediaAdslotDeal::model()->upDealRelation($model->attributes['id'], CJSON::decode($model->attributes['medias']), CJSON::decode($model->attributes['adslots']));
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
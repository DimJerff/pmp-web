<?php
/* 首页 */
class SiteController extends Controller
{
	/* 不检测权限 */
	public $noCheckPermission = TRUE;
	
	public $defaultCompanyID = 0;
    protected $pageBtn = null;

	public function actionIndex()
	{
		$this->redirect($this->createUrl('site/dashboard'));
	}

    /**
     * 首页
     * @param string $time
     */
	public function actionDashboard($time = ''){
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
        //获取当前公司的应用及媒体结算价格
        $media = Media::model()->findByPk($defaultCompanyId);
        $notice = false;
        if(empty($media) && empty($company['payType'])){
            $notice = true;
        }
        // 获取当前公司的接入方式
        $sdkType=$company['sdkType'];
        if(!empty($sdkType)){
            $company['sdkType']=explode(',',$sdkType);
        }else{
            $company['sdkType']=array();
        }
        // 模板分配显示
        $this->smartyRender(array(
            'time'    => $time,
            'timeStr' => strtotime($timeArr[0]) . '_' . strtotime($timeArr[1]),
            'company' => $company,
            'notice'  => $notice,
        ));
	}
    /*
     *编辑公司接入,结算方式
     */
    public function actionPostCompany(){
        if($_REQUEST['company']){
            $errors = null;
            $companyModel = Company::model()->findByPk($_POST['company']['id']);
            $operationType = 4;
            $data=$this->_mediaDataBeforeValidate($_POST['company']);
            if($data['sdkType']){
            $data['sdkType']=implode(',',$data['sdkType']);
            }
            $companyModel->attributes=$data;
            if ($companyModel->validate()) {
                $companyModel->save();
                // 记录操作日记
                OperationLog::model()->addModel($companyModel);
            } else {
                $errors = $companyModel->getErrors();

            }
            $errors ? $this->rspJSON($errors,'error') : $this->rspJSON(null);
        }
        $this->redirect($this->createUrl('site/dashboard'),array('errors',$errors));
    }
    /**
     * 获取当前当天的公司交易消耗
     */
    public function actionTodayCost() {
        // 获取当前用户的默认公司id
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;

        // 今日消耗
        $todayCost = ReportDealDaily::model()->getTodayCost($companyId);
        echo "document.write('". number_format($todayCost, 2) . "');";
    }

    /**
     * 获取当天的交易消耗
     */
    public function actionTodayAllCost() {
        // 今日消耗
        $todayCost = ReportDealDaily::model()->getTodayCost();
        echo "document.write('". number_format($todayCost, 2) . "');";
    }

    /**
     * 获取当前累计公司交易消耗
     */
    public function actionAllCost() {
        // 获取当前用户的默认公司id
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;

        // 累计消耗
        $allCost = ReportDealDaily::model()->getAllCost($companyId);
        echo "document.write('". number_format($allCost, 2) . "');";
    }


    /**
     * 获取所有所有交易消耗
     */
    public function actionAllAllCost() {
        // 累计消耗
        $allCost = ReportDealDaily::model()->getAllCost();
        echo "document.write('". number_format($allCost, 2) . "');";
    }

    /*
 * 验证提交数据前的处理
 */
    public function _mediaDataBeforeValidate ($data){
        if(empty($data['Enable'])){
            $data['payType']=-1;
            $data['mediaPrice']=0;
        }else{
            if($data['_mediaPrice_mediaSharingRate']){
                $data['payType']=$data['_mediaPrice_mediaSharingRate'];
                $data['mediaPrice']=0;
            }else{
                $data['mediaSharingRate']= 0;
            }
        }
        return $data;
    }
}

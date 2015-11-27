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

        // 模板分配显示
        $this->smartyRender(array(
            'time'    => $time,
            'timeStr' => strtotime($timeArr[0]) . '_' . strtotime($timeArr[1]),
            'company' => $company,
        ));
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

    /**
     * 上传文件
     * @param $type string 文件的类型
     * @param null $model 文件类型所在的模块,配合type获取文件上传配置
     * @param null $adgroupId 是否和adgroup关联,如果关联则判断公司权限
     * @param null $file 扩展file表单的name,如果一个页面有多个相同type,则可以设置不同的file
     * @throws CHttpException
     */
    public function actionUpload($type, $model=null, $adgroupId=null, $file=null){
        $limitType = lcfirst(str_replace(' ','',ucwords($model.' '.$type)));
        $model = new UploadFile($limitType);
        $model->setAdgroupId($adgroupId);
        $model->instance = CUploadedFile::getInstanceByName($file ? $file : $type);
        $result = $model->save();
        if($result) {
            $this->rspJSON($result);
        }else{
            $this->rspErrorJSON(403, $model->error());
        }
    }
}

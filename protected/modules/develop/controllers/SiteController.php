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
     * 获取当前累计公司交易消耗
     */
    public function actionAllCost() {
        // 获取当前用户的默认公司id
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;

        // 累计消耗
        $allCost = ReportDealDaily::model()->getAllCost($companyId);
        echo "document.write('". number_format($allCost, 2) . "');";
    }
}

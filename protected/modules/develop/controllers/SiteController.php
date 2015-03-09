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

	/* 仪表盘 */
	public function actionDashboard($time = ''){
		$yii = Yii::app();
		$user = $yii->user;
		$userState = $user->getRecord();
		
		$this->defaultCompanyID = $defaultCompanyId = $userState->defaultCompanyID;

	}

}

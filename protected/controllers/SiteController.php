<?php
/* 首页 */
class SiteController extends Controller
{
	/* 不检测权限 */
	public $noCheckPermission = TRUE;
	
	public function actionIndex()
	{
		/* 自动跳到pc屏的首页 */
		$this->redirect($this->createUrl('develop/site/index'));
	}
}

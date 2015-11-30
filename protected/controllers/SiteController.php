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

<?php
class Controller extends CController
{
	public $authItemPrefix = 'frontend';
	public $noCheckPermission = FALSE;
	public $smartyControllerId = '';
	public $domainModel;
	public $user;
	private $_assetsUrl;

	/* 初始化 */
	public function init()
	{
		$yii = Yii::app();
		
		/* 初始化模块的语言设置 */
		$module = $this->getModule();
		if(isset($module)) {
			$cookies = $yii->request->cookies;
			$hl = null;
			if(isset($_GET['hl'])) $hl = $_GET['hl'];
			if(!isset($hl) && isset($cookies['hl'])) $hl = $cookies['hl'];
			/* cookie hl存在且存在指定范围中，则设置为当前语言 */
			if(isset($hl) && in_array($hl, Yii::app()->params->languageList[$module->id])) {
				$yii->language = $hl;
				/* 同步cookie */
				if($hl != $cookies['hl']) {
					$yii->request->cookies['hl'] = (new CHttpCookie('hl', $hl));
				}
			}
		}
	}
	
	/* 检查权限 */
	public function checkAccess($key = '') {
		$user = Yii::app()->user;
		if(!$user->checkAccess($key)) {
			$this->throwError(403,'You don\'t have permission to access.');
		}
		return TRUE;
	}
	
	/* action执行前 */
	public function beforeAction($action)
	{
		if($this->id !='site' || $action->id != 'error') {
			$user = Yii::app()->user;
			/* auto login */
			if($user->isGuest) $user->autoLogin();
			/* check permission */
			if(!$this->noCheckPermission) $this->checkAccess();
		}
		return true;
	}

	/* 过滤器 */
	public function filters()
	{
		return array(
			'accessControl',
		);
	}
	
	/* 访问规则 */
	public function accessRules()
	{
		/* 没登陆禁止访问所有方法 */
		return array(
			array('allow',
					'actions' => array('error'),
			),
			array('deny',
				'actions' => array(),
				'users' => array('?'),
			),
		);
	}

	/* 请求错误的处理方式 */
	public function actionError()
	{
		if($error = Yii::app()->errorHandler->error)
		{
			
			if(Yii::app()->request->isAjaxRequest)
			{
				$this->rspErrorJSON($error['code'], $error['message']);
			}else{
				$this->render('error', $error);
			}
		}
	}

	/* 输出json内容 */
	public function rspJSON($data = null, $status = 'normal')
	{
		//header('Content-type: application/json');
		$result = array($status);
		if(isset($data)) array_push($result, $data);
		
		/* 增加callback功能 */
		$callback = '';
		if(isset($_GET['callback']) && preg_match('/[a-z\_][a-z\d\_](\.[a-z\_][a-z\d\_])*/i', $_GET['callback'])) {
			$callback = $_GET['callback'];
		}

		if($callback) echo $callback.'(';
		echo CJSON::encode($result);
		if($callback) echo ');';
	}

	/* 输出错误的json内容 */
	public function rspErrorJSON($code, $data, $url = NULL)
	{
		$this->rspJSON(array('code' => $code, 'message' => $data, 'url' => $url, ), 'error');
	}
	
	/* 抛出异常，兼容ajax */
	public function throwError($code, $message, $exceptionClass = 'CHttpException') {
		if(Yii::app()->request->isAjaxRequest) {
			$this->rspErrorJSON($code, $message);
			exit;
		}else {
			throw new $exceptionClass($code, $message);
		}
	}

	/* smarty */
	public function smartyRender($path = array(), $params = array(), $isReturn = false)
	{
		$yii = Yii::app();
		/* 可省略path，默认为当前action */
		if(is_array($path)) {
			$params = $path;
			$path = $this->action->id;
		}
		$user = $yii->user;
		$userState = $user->getRecord();
		$smarty = $yii->smarty;
		$smarty->init();
		
		$cookies = array();
		foreach($yii->request->getCookies() as $key => $item) {
			$cookies[$key] = (array)$item;
		}
		
		$checkAccess = new SmartyCheckAccess;
		
		$smarty->assign(array(
			'assetsUrl' => $this->assetsUrl,
			'assetsCommon' => $this->assetsUrl,
			'user' => $userState,
			'checkAccess' => $checkAccess,
			'language' => $yii->language,
			'cookies' => $cookies,
			'controllerId' => $this->id,
            'basePath' => Yii::app()->basePath,
			'domainModel' => '',
		));

		return $smarty->renderFile(Null, $path, $params, $isReturn);
	}
	
	public function redirect($url=null,$terminate=true,$statusCode=302) {
		/* 默认跳回上一页 */
		if(!isset($url)) {
			$url = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : $this->createUrl(Yii::app()->controller->id . '/index');
		}
		parent::redirect($url,$terminate,$statusCode);
	}
	
	/* 递归trim */
	public function dTrim(&$list) {
		if(is_scalar($list)) {
			$list = trim($list);
		}else{
			foreach($list as &$item) {
				$item = $this->dTrim($item);
			}
		}
		return $list;
	}
	
	/* 获取资源Url */
	public function getAssetsUrl()
	{
		if(!isset($this->_assetsUrl)) {
			$yii = Yii::app();
			$this->_assetsUrl = '/assets/'.$yii->smarty->assignConfig['versionCode'];
		}
		return $this->_assetsUrl;
	}
	
	/* 设置资源Url */
	public function setAssetsUrl($value)
	{
		$this->_assetsUrl = $value;
	}
}

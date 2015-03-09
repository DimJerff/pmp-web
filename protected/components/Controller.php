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
		/* 是否显示消耗 */
		Report::$showCost = $yii->user->checkAccess('#pc/costShow');
		Report::$costShowXNumber = $yii->user->checkAccess('#pc/costShowX') ? 2 : 1;
		Report::$costShowX = Report::$costShowXNumber > 1 ? '*'.Report::$costShowXNumber : '';
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
			
			/* binding domain */
			$host = $_SERVER['HTTP_HOST'];
			$this->domainModel = CompanyDomain::model()->findByAttributes(array('domain' => $host));
			if($this->domainModel && isset($_SERVER['HTTPS'])) {
				header("location:".$this->domainModel->domain);
				exit;
			}
		}
		return true;
	}

	/* 获取xhprof id */
	public function getXhprofId() {
		if(function_exists("xhprof_enable") == false) return null;
		$xhprof_data = xhprof_disable();
		require_once Yii::getPathOfAlias("webroot.xhprof_lib.utils.xhprof_lib").'.php';
		require_once Yii::getPathOfAlias("webroot.xhprof_lib.utils.xhprof_runs").'.php';
		$xhprof_runs = new XHProfRuns_Default();
		$runId = $xhprof_runs->save_run($xhprof_data, "xhprof_testing");
		return $runId;
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
		/* xhprof id */
		header("x-xhprof-id:".$this->getXhprofId());

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
			'assetsCommon' => $this->assetsUrl.'/common/common_assets',
			'user' => $userState,
			'domainModel' => $this->domainModel,
			'checkAccess' => $checkAccess,
			'language' => $yii->language,
			'cookies' => $cookies,
			'controllerId' => $this->id,
			'costShowX' => Report::$costShowXNumber,
		));
		
		if(!$user->isGuest) {
			/* 未读信息 */
			$messageModel = UserMessage::model();
			$criteria = new CDbCriteria;
			if(!$user->checkAccess('#pc/costShow')) {
				$criteria->addCondition('t.settingId NOT IN(2,3,4)');
			}
			$criteria->addCondition('userId='.(int)$user->id.' AND status=0');
			$count = $messageModel->count($criteria);
			$criteria->order = 't.id DESC';
			$criteria->limit = 5;
			$list = $messageModel->findAll($criteria);
			$smarty->assign(array(
				'unReadMessage' => $list,
				'unReadMessageCount' => $count,
			));
		}

		/* xhprof id */
		header("x-xhprof-id:".$this->getXhprofId());
		
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

<?php
class UserController extends Controller
{
	public $noCheckPermission = TRUE;
	
	/* 访问规则 */
	public function accessRules()
	{
		return CMap::mergeArray(array(
			array('allow',
				'actions' => array('login', 'loginapi', 'forgot', 'forgotpasswd', 'register', 'InviteRegister', 'InviteLogin', 'exists', 'upload', 'privacy_policy', 'domainExists', ),
				'users' => array('?'),
			),
		), parent::accessRules());
	}

	/* 登陆页面*/
	public function actionLogin()
	{
		$yii = Yii::app();
		/* Guest jump to homepage */
		if(!$yii->user->isGuest) {
			$this->redirect($yii->user->returnUrl);
		}
		
		$errors = null;
		if(isset($_POST['login'])) {
			/* check params */
			$formModel = new UserForm('login');
			$formModel->attributes = $_POST['login'];
			if($formModel->validate()) {
				$identity = $formModel->getIdentity();
				/* login */
				$yii->user->login($identity, $formModel->rememberMe);
				/* 操作日志 */
				OperationLog::model()->add('user', 7, $yii->user->id, '用户登录', array('loginTime'=>$_SERVER['REQUEST_TIME'],'loginIP'=>$_SERVER['REMOTE_ADDR']));
				/* jump to previours url */
				$this->redirect($yii->user->returnUrl);
			}else{
				/* get format errors */
				$errors = $formModel->getErrors();
			}
		}
		$this->smartyRender(array(
			'errors' => $errors,
		));
	}
	
	/* 登陆api */
	public function actionLoginapi()
	{
		/* check params */
		$formModel = new UserForm('login');
		$formModel->attributes = $_POST['loginbox'];
		if($formModel->validate()) {
			$model = User::model()->findByAttributes(array('email' => $formModel->email,));
			$attr = $model->attributes;
			unset($attr['passwd']);
			$this->rspJSON($attr);
		}else{
			$this->rspErrorJSON(403, $formModel->getErrors());
		}
	}

	/* 退出登陆 */
	public function actionLogout()
	{
		$user = Yii::app()->user;	
		$user->logout();
		$this->redirect($user->returnUrl);
	}

	/* 忘记密码 */
	public function actionForgot()
	{
		$yii = Yii::app();
		/* Guest jump to homepage */
		if(!$yii->user->isGuest) {
			$this->redirect($yii->user->returnUrl);
		}

		$errors = null;
		if(isset($_POST['forgot'])) {
			/* check params */
			$formModel = new UserForm('forgot');
			$formModel->attributes = $_POST['forgot'];
			if($formModel->validate()) {
				$userModel = User::model()->findByAttributes(array(
					'email' => $formModel->email,
				));
				Mail::model()->sendByTemplate('RESET_PASSWORD', $formModel->email, array(
					'RESET_URL' => $this->createAbsoluteUrl('user/forgotpasswd').'?'. http_build_query(array(
						'userId' => $userModel->id,
						'sig' => Encrypt::encode('forgot_'.$userModel->id, '', 3600),
					)),
					'platformName' => $this->domainModel->platformName,
				));
				$errors = array('normal' => true,);
			}else{
				/* get format errors */
				$errors = $formModel->getErrors();
			}
		}
		$this->smartyRender(array(
			'errors' => $errors,
		));
	}

	/* 修改密码 */
	public function actionForgotpasswd($userId, $sig)
	{
		if(!is_numeric($userId) || $userId <= 0 || !$sig || Encrypt::decode($sig) != 'forgot_'.$userId) {
			throw new CHttpException('404', 'Invalid Request');
		}
		
		if(isset($_POST['forgot'])) {
			/* check params */
			$formModel = new UserForm('forgot_resetpasswd');
			$formModel->attributes = $_POST['forgot'];
			if($formModel->validate()) {
				User::model()->changePasswd($userId, $formModel->passwd);
				$errors = array('normal' => true,);
			}else{
				/* get format errors */
				$errors = $formModel->getErrors();
			}
		}
		
		$this->smartyRender(array(
			'errors' => $errors,
		));
	}
	
	/* 注册 */
	public function actionRegister()
	{
		$isSuccess = false;
		if(isset($_POST['register'])) {
			$postData = $_POST['register'];
			$db = User::model()->getDbConnection();
			/* 校验公司 */
			$companyFormModel = new CompanyForm('add');
			$companyFormModel->attributes = $postData;
			if(!$companyFormModel->validate()) {
				/* 非法数据 */
				throw new CHttpException(403, 'Invalid Request By Company');
			}
			
			/* 新增用户 */
			if(!$postData['userId']) {
				$formModel = new UserForm('add');
				$formModel->attributes = $postData;
				if($formModel->validate()) {
					$attrs = $formModel->attributes;
					unset($attrs['rememberMe']);
					/* 校验通过 */
					$userModel = new User;
					$userModel->attributes = $attrs;
					$userModel->save();
				}else{
					/* 非法数据 */
					throw new CHttpException(403, 'Invalid Request By User');
				}
			}else{
				/* 已有用户 */
				$userModel = User::model()->findByPk((int)$postData['userId']);
				if(!$userModel) throw new CHttpException(403, 'Invalid Request By UserId');
			}
			
			/* 新增公司 */
			$attrs = $companyFormModel->attributes;
			/* 校验通过 */
			$companyModel = new Company;
			$companyModel->attributes = $attrs;
			/* 设置公司联系人 */
			$companyModel->linkUserId = $userModel->id;
			if($companyModel->save()) {
				/* 设置默认公司ID */
				if(!$postData['userId']) {
					$userModel->defaultCompanyID = $companyModel->id;
					$userModel->save();
					Yii::app()->user->setState('user', $userModel);
				}
				/* 关联表 */
				$relation = new RelationUserCompany;
				$relation->userId = $userModel->id;
				$relation->companyId = $companyModel->id;
				$relation->status = $postData['userId'] ? 0 : 1;
				$relation->save();
				
				/* 发送邮件 */
				$mail = Mail::model();
				$mail->bcc = Yii::app()->params['registerCCEmail'];
				$mail->sendByTemplate('REGISTER_COMPANY', array(
					$userModel->email => array(
						'COMPANY' => $companyModel->companyName,
					)
				));
			}
			$isSuccess = true;
		}
		$this->smartyRender(array(
			'timezoneList' => BaseTimezones::model()->findAll(),
			'isSuccess' => $isSuccess,
		));
	}

	/* 不存在 */
	public function actionExists($email, $userId = null)
	{
		echo !$userId && User::model()->findByAttributes(array('email' => $email,)) ? 'false' : 'true';
	}
	
	/* 编辑用户信息 */
	public function actionEdit() {
		$this->checkAccess();
		$user = Yii::app()->user;
		$userState = $user->getRecord();
		
		$errors = null;
		if($_POST['edit'] && $this->checkAccess('save')) {
			/* 用户信息 */
			$formModel = new UserForm('edit');
			$_POST['edit']['email'] = $userState->email;
			$formModel->attributes = $_POST['edit'];
			if($formModel->validate()) {
				$userModel = User::model();
				if($userModel->updateByPk($user->id, array(
						'firstname' => $formModel->firstname,
						'lastname' => $formModel->lastname,
						'telephone' => $formModel->telephone,
						'language' => $formModel->language,
				))) {
					/* 更新状态 */
					$user->setState('user', $userModel->findByPk($user->id));
				}
				$errors = array('normal' => true,);
			}else{
				/* get format errors */
				$errors = $formModel->getErrors();
			}
		}
		
		$this->smartyRender(array(
			'errors' => $errors,
		));
	}


	/* 修改密码 */
	public function actionEdit_passwd() {
		$this->checkAccess();
		$user = Yii::app()->user;
		$userState = $user->getRecord();

		$errors = null;
		if($_POST['change'] && $this->checkAccess('save')) {
			$formModel = new UserForm('changePasswd');
			$_POST['change']['email'] = $userState->email;
			$formModel->attributes = $_POST['change'];
			if($formModel->validate() && $_POST['change']['newPasswd']) {
				/* 更新密码 */
				User::model()->changePasswd($user->id, strval($_POST['change']['newPasswd']));
				$errors = array('normal' => true,);
			}else{
				/* get format errors */
				$errors = $formModel->getErrors();
			}
		}
		$this->smartyRender(array(
			'errors' => $errors,
		));
	}
	
	/* 切换公司 */
	public function actionSwitch($companyId = 0) {
		$this->checkAccess();
		$user = Yii::app()->user;
		$userState = $user->getRecord();
		/* 检查公司ID是否正确 */
		if($companyId && ($relation = RelationUserCompany::model()->findAllByAttributes(array(
			'companyId' => $companyId,
			'userId' => $user->id,
		)))) {
			$company = Company::model()->findByPk($companyId);
			if($company->status == 1) {
				$userState->setDefaultCompanyId($company->id);
				//绑定公司角色权限
				if(!$userState->roleID) $userState->roleID = $company->roleID;
				/* 更新 */
				$user->setState('user', $userState);
			}
			/* 跳转 */
			$this->redirect($this->createUrl('site/index'));
		}else{
			$companyList = array();
			foreach($userState->companyList as $item) {
				if($item->status == 1) $companyList[] = $item;
			}
			$this->rspJSON(array($userState->defaultCompanyID, $companyList));
		}
	}

	/* 隐私保护政策 */
	public function actionPrivacy_policy() {
		$this->smartyRender();
	}

}
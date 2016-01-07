<?php
class UserInstance extends CWebUser
{
	public $autoLoginEncryptKeyt;
	public $autoLoginCookieName = 'uauto';

	private $_record;
	public function getRecord() {
		if(!isset($this->_record)) {
			$this->_record = User::model()->findByPk($this->id);
		}
		return $this->_record;
	}
	public function setRecord($user) {
		if (!($user instanceof User)) return false;
		$this->_record = $user;
		return true;
	}
	
	/* check access permission */
	public function checkAccess($operation = '', $params = array(), $allowCaching = true) {
		$userState = $this->getRecord();
		//todo 游客状态还需在其他地方确定true
		if(!$userState && $this->isGuest) return true;
		if(!is_object($userState) && $this->id) Yii::app()->controller->throwError(403,'User No Found!');
		return $this->checkAccessNoLogin($userState->currentRoleId(), $operation);
	}
	
	public function checkAccessNoLogin($roleId, $operation = '') {
		static $itemList;
        /* 范例
        $itemList = array(
            "frontend" => array(
                "develop/login" => 1,
                "develop/media/index" => 2,
                "develop/user/switch" => 3,
            ),
        );
        */
		static $roleItemList;
        /* 范例
        $roleItemList = array(
            "1" => array(
                "1"=> 1,
                "2"=> 1,
                "3"=> 1,
            ),
        );
        */
		
		$controller = Yii::app()->controller;
		$prefix = $controller->authItemPrefix;


		/* 初始化key to id */
		if(!isset($itemList)) {
			$itemList = array();
			foreach(AuthItem::model()->findAll() as $item) {
				$key = explode('#', strtolower($item->key));
				$itemList[$key[0]][$key[1]] = $item->id;
			}
		}

		/* 获取用户所在角色的权限关系 */
		if(!isset($roleItemList)) {
			$roleItemList = array();
			$authRole = AuthRole::model()->with('items')->findByPk($roleId);
			if($authRole) {
				foreach($authRole->items as $item) {
					$roleItemList[$roleId][$item->id] = 1;
				}
			}
		}

		$relations = $roleItemList[$roleId];

		$operation = strtolower($operation);
		$index = strpos($operation, '#');
		if($index > 0) {
			$prefix = str_replace('#', '', $operation);
			foreach($itemList[$prefix] as $k => $itemId) {
				if(isset($relations[$itemId])) return true;
			}
			return false;
		}else{
			if($index !== 0) {
				$module = $controller->module;
				$controllerId = ($module ? $module->id . '/' : '') . $controller->id;
				$operation = $controllerId . '/' . $controller->action->id . ($operation ? '/' . $operation : '');
			}else{
				$operation = substr($operation, 1);
			}
			$operation = strtolower($operation);
			
			$itemId = $itemList[$prefix][$operation];
			if(!$itemId) $controller->throwError(403,'The Auth Item "'.$operation.'" is No Found!');

			/* 判断当前角色是否存在权限 */
			return (bool)$relations[$itemId];
		}
	}

	/* 登陆前 */
	public function beforeLogin($id, $states, $fromCookie) {

		return true;
	}

    /**
     * 登录后
     */
    public function afterLogin()
    {
        /* 更新最后登陆时间 */
        User::model()->updateByPk($this->getId(), array(
            'lastLoginTime' => $_SERVER['REQUEST_TIME'],
        ));
    }

	/* login by identity */
	public function login($identity, $isRememberMe = false)
	{
		if(!parent::login($identity)) return false;
		if($isRememberMe) $this->remember($identity->getId(), 14*86400);
	}

	/* remember cookie */
	public function remember($uid, $expireTime = 3600)
	{
		$autoStr = Encrypt::encode($uid, $this->autoLoginEncryptKeyt, $expireTime);
		$cookie = new CHttpCookie($this->autoLoginCookieName, $autoStr);
		$cookie->expire = $_SERVER['REQUEST_TIME'] + $expireTime;
		Yii::app()->request->cookies[$this->autoLoginCookieName] = $cookie;
	}

	/* logout */
	public function logout($destroySession = true)
	{
		parent::logout($destroySession);
		/* destory cookie */
		unset(Yii::app()->request->cookies[$this->autoLoginCookieName]);
	}
	
	/* auto login */
	public function autoLogin()
	{
		/* check cookie empty */
		$cookie = Yii::app()->request->cookies[$this->autoLoginCookieName];
		if(!$cookie || !$cookie->value) return false;
		/* decode */
		$uid = Encrypt::decode($cookie->value, $this->autoLoginEncryptKeyt);
		/* check format and try login */
		if(!$uid || !is_numeric($uid) || !$this->loginByUid(abs((int)$uid))) {
			/* failure auto login */
			unset(Yii::app()->request->cookies[$this->autoLoginCookieName]);
			return false;
		}else{
			/* 更新最后登陆时间 */
			User::model()->updateByPk(abs((int)$uid), array(
				'lastLoginTime' => $_SERVER['REQUEST_TIME'],
			));
			return true;
		}
	}

	/* login by uid */
	public function loginByUid($uid)
	{
		$identity = new UserIdentity('', '');
		if($identity->setByUid($uid)) {
			$this->login($identity);
			return true;
		}else{
			return false;
		}
	}

    /**
     * 用户当前访问的公司Id
     * @return mixed|void
     */
    public function defaultCompanyId(){
        $userState = $this->getRecord();
        if(!$userState->defaultCompanyID) {
            foreach($userState->companyList as $company) {
                $userState->setDefaultCompanyId($company->id);
                break;
            }
        }

        return $userState->defaultCompanyID;
    }

    /**
     * 获取当前访问公司实例
     * @return Company
     */
    public function defaultCompany(){
        $defaultCompanyId = $this->defaultCompanyId();
        $defaultCompany = Company::model()->findAllByPk($defaultCompanyId);

        return $defaultCompany;
    }
}

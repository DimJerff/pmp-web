<?php
class UserController extends Controller
{
	public $noCheckPermission = TRUE;
	
	/* 访问规则 */
	public function accessRules()
	{
		return CMap::mergeArray(array(
			array(
                'allow',
				'actions' => array(),
				'users' => array('?'),
			),
		), parent::accessRules());
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
    /* 编辑用户密码 */
    public function actionEdit_passwd() {
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
    /**
     * 切换公司
     * @param int $companyId 公司id
     */
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

    /**
     * 公司信息
     */
    public function actionCompany_info()
    {
        $this->checkAccess();
        $user = Yii::app()->user;
        $userState = $user->getRecord();

        $errors = null;
        if($_POST['edit'] && $this->checkAccess('save')) {
            /* 用户信息 */
            $formModel = new CompanyForm('edit');
            $formModel->attributes = $_POST['edit'];
            if($formModel->validate()) {
                if(Company::model()->updateByPk($userState->defaultCompanyID, array(
                    'companyName' => $formModel->companyName,
                    'website' => $formModel->website,
                    'businessLicense' => $formModel->businessLicense,
                    'identityCard' => $formModel->identityCard,
                    'identityCard2' => $formModel->identityCard2,
                ))) {
                    /* 更新状态 */
                    $user->setState('user', User::model()->findByPk($user->id));
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

    /**
     * 获取开发者数目
     * @param $companyId 公司id
     */
    public function actionUserCount() {
        // 获取当前用户的默认公司id
        $companyId = Yii::app()->user->getRecord()->defaultCompanyID;

        $count = User::model()->count("defaultCompanyID=:companyId",array(":companyId"=>$companyId));
        $count = $count ? $count : 0;
        echo "document.write(". $count . ");";
    }

    /**
     * 获取所有开发者数目
     */
    public function actionUserAllCount() {
        $count = Company::model()->count("status=:status", array(":status" => 1));
        $count = $count ? $count : 0;
        echo "document.write(". $count . ");";
    }

}
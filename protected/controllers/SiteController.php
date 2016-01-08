<?php
/* 首页 */
class SiteController extends Controller
{
	/* 不检测权限 */
	public $noCheckPermission = TRUE;
    public function accessRules(){
        return CMap::mergeArray(array(
            array(
                'allow',
                'actions' => array(
                    'login',
                    //'loginapi',
                    //'forgot',
                    //'forgotpasswd',
                    //'register',
                    'exists',
                    'upload'
                ),
                'users' => array('?'),
            ),
        ), parent::accessRules());
    }
	
	public function actionIndex()
	{
		/* 自动跳到首页 */
		$this->redirect($this->createUrl('develop/site/index'));
	}

	/**
	 * 上传文件
	 * @param $type string 文件的类型
	 * @param null $model 文件类型所在的模块,配合type获取文件上传配置
	 * @param null $file 扩展file表单的name,如果一个页面有多个相同type,则可以设置不同的file
	 * @throws CHttpException
	 */
	public function actionUpload($type, $model=null, $file=null){
		$limitType = lcfirst(str_replace(' ','',ucwords($model.' '.$type)));
		$model = new UploadFile($limitType);
		$model->instance = CUploadedFile::getInstanceByName($file ? $file : $type);
		$result = $model->save();
		if($result) {
			$this->rspJSON($result);
		}else{
			$this->rspErrorJSON(403, $model->error());
		}
	}

    /**
     * 用户登陆页面
     */
    public function actionLogin()
    {
        $yii = Yii::app();
        /* Guest jump to homepage */
        if(!$yii->user->isGuest) {
            $this->redirect($yii->user->getReturnUrl());
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
                OperationLog::model()->add('user', $yii->user->id, '用户登录', array('loginTime'=>$_SERVER['REQUEST_TIME'],'loginIP'=>$_SERVER['REMOTE_ADDR']));
                /* jump to previours url */
                $this->redirect($yii->user->getReturnUrl());
            }else{
                /* get format errors */
                $errors = $formModel->getErrors();
            }
        }
        $this->smartyRender(array(
            'errors' => $errors,
        ));
    }

    /* 不存在 */
    public function actionExists($email, $userId = null)
    {
        echo !$userId && User::model()->findByAttributes(array('email' => $email,)) ? 'false' : 'true';
    }

    /**
     * 退出登陆
     */
    public function actionLogout()
    {
        $user = Yii::app()->user;
        $user->logout();
        $this->redirect($user->getReturnUrl());
    }
}

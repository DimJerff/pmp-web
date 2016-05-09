<?php
/* 新增会员表单 */
class UserForm extends CFormModel
{
	public $roleID = 0;
	public $firstname;
	public $lastname;
	public $email;
	public $passwd;
	public $telephone;
	public $language = 1;
	public $defaultCompanyID = 0;
	public $lastLoginTime = 0;
	public $creationTime = 0;
	public $rememberMe = false;
	
	private $_identity;

	public function rules()
	{
		return array(
			array('firstname', 'required', 'message' => '名不能为空', 'on' => 'add, edit'),
			array('lastname', 'required', 'message' => '姓不能为空', 'on' => 'add, edit'),
			array('email', 'required', 'message' => '电子邮箱不能为空', 'on' => 'add, edit, login, forgot, changePasswd'),
			array('telephone', 'required', 'message' => '联系电话不能为空', 'on' => 'add, edit, forgot'),
			array('passwd', 'required', 'message' => '密码不能为空', 'on' => 'add, login, forgot_resetpasswd'),
			array('email', 'email', 'message' => '电子邮箱格式错误', 'on' => 'add, login, forgot'),
			array('language', 'required', 'on' => 'edit',),
			array('rememberMe', 'boolean', 'on' => 'login',),
			/* 添加时，检查邮箱是否已存在 */
			array('email', 'checkExist', 'on' => 'add',),
			/* 忘记密码时，检查telephone是否正确 */
			array('telephone', 'checkTelephone', 'on' => 'forgot'),
			/* 验证密码 */
			array('passwd', 'authPasswd', 'on' => 'login, changePasswd',),
		);
	}
	
	/* 检查邮箱是否已存在 */
	public function checkExist($attr, $params)
	{
		/* 避免邮箱重复问题 */
		if($model = User::model()->findByAttributes(array($attr => $this->$attr)))
			$this->addError($attr, '邮箱已存在');
	}

	/* 检查电话号码是否已存在 */
	public function checkTelephone($attr, $params)
	{
		/* 检查电话号码是否准确 */
		if(!User::model()->findByAttributes(array('email' => $this->email, 'telephone' => $this->telephone)))
			$this->addError('telephone', '电话号码错误');
	}
	
	/* 验证密码 */
	public function authPasswd($attr, $params)
	{
		//获取UserIdentity实例
		$this->_identity = new UserIdentity($this->email, $this->$attr);
		if(!$this->_identity->authenticate()) {
			switch ($this->_identity->errorCode) {
				case UserIdentity::ERROR_COMPANY_INVALID:
					$this->addError($attr, '帐号审核中');
					break;
				case UserIdentity::ERROR_DENY_LOGIN:
					$this->addError($attr, '帐号已被禁用');
					break;
				default:
					$this->addError($attr, '帐号或密码错误');
					break;
			}
		}
	}
	
	/* 返回identity对象 */
	public function getIdentity()
	{
		return $this->_identity;
	}
}


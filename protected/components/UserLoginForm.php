<?php
/* 管理员登陆表单 */
class UserLoginForm extends CFormModel
{
	public $email;
	public $passwd;
	public $rememberMe = false;
	
	private $_identity;
	
	public function rules()
	{
		return array(
			array('email', 'required', 'message' => '电子邮箱不能为空'),
			array('passwd', 'required', 'message' => '密码不能为空'),
			array('email', 'email', 'message' => '电子邮箱格式错误',),
			array('passwd', 'authenticate',),
			array('rememberMe', 'boolean',),
		);
	}
	
	public function authenticate($attr, $params)
	{
		$this->_identity = new UserIdentity($this->email, $this->passwd);
		if(!$this->_identity->authenticate())
			$this->addError('passwd', '密码错误');
	}
	
	/* 返回identity对象 */
	public function getIdentity()
	{
		return $this->_identity;
	}
}


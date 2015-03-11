<?php
class UserIdentity extends CUserIdentity
{
	const ERROR_COMPANY_INVALID = 501;
	const ERROR_DENY_LOGIN = 502;
	private $_id;
	
	public function authenticate()
	{
		$model = User::model()->findByAttributes(array(
			'email' => $this->username,
		));
		if($model === null) {
			/* 帐号不存在 */
			$this->errorCode = self::ERROR_USERNAME_INVALID;
		} else if($model->passwd !== md5(md5($this->password))) {
			/* 密码错误 */
			$this->errorCode = self::ERROR_PASSWORD_INVALID;
		} else{
			if(!$model->defaultCompany || $model->defaultCompany->status != 1) {
				$this->errorCode = self::ERROR_COMPANY_INVALID;
			}else{
				/* 认证成功，记录状态 */
				$this->setByUid($model);
			}
		}
		return !$this->errorCode;
	}

	public function getId()
	{
		 return $this->_id;
	}
	
	public function setByUid($flag)
	{
		if($flag instanceof User) {
			$record = $flag;
		}elseif(!($record = User::model()->findByPk($flag))){
			return false;
		}
		$userInstance = new UserInstance();
		if($userInstance->checkAccessNoLogin($record->currentRoleId(), '#develop/denyLogin')) {
			$this->errorCode = self::ERROR_DENY_LOGIN;
			return false;
		}else{
			$this->_id = $record->id;
			$this->errorCode = self::ERROR_NONE;
			return true;
		}
	}
}
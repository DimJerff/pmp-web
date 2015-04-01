<?php
class SmartyCheckAccess implements ArrayAccess
{
	private $user;
	
	function __construct() {
		$this->user = Yii::app()->controller->user;
		if(empty($this->user)) {
			$this->user = Yii::app()->user;
		}
	}
	
	/*
	 * 检测是否拥有某个权限
	 * */
	public function offsetExists($offset) {
		return (bool)$this->offsetGet($offset);
	}
	
	/* 获取值 */
	public function offsetGet($offset) {
		return $this->user->checkAccess($offset);
	}
	
	public function offsetSet($offset, $value) {
		return false;
	}
	
	public function offsetUnset($offset) {
		return false;
	}
}

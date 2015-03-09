<?php
class PcModule extends CWebModule
{
	public $defaultController = 'deal';
	
	public function beforeControllerAction($controller, $action) {
		if($_POST) $controller->dTrim($_POST);
		return true;
	}
}
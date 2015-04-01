<?php
class DevelopModule extends CWebModule
{
	public $defaultController = 'site';
	
	public function beforeControllerAction($controller, $action) {
		if($_POST) $controller->dTrim($_POST);
		return true;
	}
}
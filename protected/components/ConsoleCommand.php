<?php
class ConsoleCommand extends CConsoleCommand
{
	public function init() {
		parent::init();
		/* fix webroot路径错误的bug */
		Yii::setPathOfAlias('webroot', realpath(Yii::app()->BasePath.'/..'));
	}
}

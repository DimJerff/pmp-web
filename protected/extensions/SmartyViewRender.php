<?php
class SmartyViewRender extends CApplicationComponent implements IViewRenderer {
	public $fileExtension = '.html';
	private $_smarty = null;
	public $config = array();
	public $assignConfig = array();

	public function init() {
		$smartyPath = Yii::getPathOfAlias('application.vendors.smarty');
		Yii::$classMap['Smarty'] = $smartyPath . '/Smarty.class.php';
		Yii::$classMap['Smarty_Internal_Data'] = $smartyPath . '/sysplugins/smarty_internal_data.php';
		$controller = Yii::app()->controller;
		$this->_smarty = new Smarty();
		/* configure smarty */
		if (is_array($this->config)) {
			foreach ( $this->config as $key => $value ) {
				/* 忽略私有属性 */
				if ($key {0} != '_') {
					$this->_smarty->$key = $value;
				}
			}
			/* 设置目录路径 */
			$moduleId = $controller->getModule()->id;
			$modulePath = $moduleId ? '.modules.'.$moduleId : '';
			$languagePath = $moduleId ? '.'.Yii::app()->language : '';
			$smartyControllerId = $controller->smartyControllerId ? $controller->smartyControllerId : $controller->id;
			$view_dir = Yii::getPathOfAlias("application{$modulePath}.views{$languagePath}." . $smartyControllerId);
			$this->_smarty->template_dir = $view_dir;
			$this->_smarty->compile_dir = $view_dir . DS .'tpl_c';
			$this->_smarty->cache_dir = $view_dir . DS .'tpl_cache';
			$this->_smarty->config_dir = $view_dir . DS .'tpl_config';
		}
		/* 默认设置的参数 */
		if(is_array($this->assignConfig)) {
			$this->_smarty->assign('config', $this->assignConfig);
		}

		Yii::registerAutoloader('smartyAutoload', true);
	}
	
	public function renderFile($context, $file, $data, $isReturn) {
		$controller = Yii::app()->controller;
		/* 自动设置controller id和action id */
		$this->_smarty->assign('controllerid', $controller->id);
		$this->_smarty->assign('actionid', $controller->action->id);
		$this->_smarty->assign('menuFocus', array($controller->id => array($controller->action->id => 1)));
		/* 设置变量 */
		$this->_smarty->assign($data);
		/* 处理并返回模板的内容 返回当前调用的 */
		$html = $this->_smarty->fetch($file.$this->fileExtension);
		if ($isReturn) {
			return $html;
		}else {
			echo $html;
		}
	}
	
	public function assign($k, $v = NULL) {
		$this->_smarty->assign($k, $v);
	}
}

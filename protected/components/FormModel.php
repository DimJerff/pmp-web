<?php
class FormModel extends CFormModel {
	const idName = '';
	
	private $__id;
	public $instance;
	public $path;
	public $urlPath;
	public $absUrl;
	public $size;
	public $thumbPath;
	public $thumbUrlPath;
	public $absThumbUrl;
	public $width;
	public $height;
	public $attachmentId;
	
	private $ruleType = 'img';
	private $extensionName;
	private $instance_error = null;
	
	public function __construct($scenario = '') {
		parent::__construct($scenario);
		$this->setId();
		$itemParams = $this->getUploadLimit();
		if(isset($itemParams) && $itemParams['ruleType']) $this->ruleType = $itemParams['ruleType'];
	}
	
	public function rules() {
		return $this->createRule($this->ruleType);
	}
	
	public function save($attr = array()) {
		return $this->validate() && is_object($this->instance);
	}
	
	public function createRule($type, $key = NULL) {
		$rule = Yii::app()->params['uploadRuleType'][$type];
		if(is_string($rule)) $rule = array($rule, '');
		list($validator, $ignoreKeys) = $rule;
		$params = Yii::app()->params;
		/* 合并配置中的限制 */
		$itemParams = $this->getUploadLimit($key);
		/* 初始化mime和实际的types */
		if($itemParams['extTypes']) {
			$extTypes = $params['uploadExtTypes'];
			$extNames = Util::split($itemParams['extTypes']);
			$types = $mimeTypes = array();
			foreach($extNames as $name) {
				$item = $extTypes[$name];
				if(!isset($item)) continue;
				if(is_string($item)) $item = array($item, $name);
				$mimeTypes = array_merge($mimeTypes, Util::split($item[0]));
				$types = array_merge($types, Util::split($item[1]));
			}
			$itemParams['types'] = implode(',', $types);
			$itemParams['mimeTypes'] = implode(',', $mimeTypes);
		}
		$ignoreKeys = array_merge(Util::split($ignoreKeys), Util::split('ruleType,formatExtName,extTypes,savePath,urlPath,thumbWidth,thumbHeight'));
		if(is_array($ignoreKeys)) {
			foreach($ignoreKeys as $k)
				unset($itemParams[trim($k)]);
		}
		return array(CMap::mergeArray(array('instance', $validator, ), $itemParams),);
	}

	/**
	 * 获取上传文件错误信息，只返回第一条错误信息
	 * @return array|string
	 */
	public function getError(){
		$errors = parent::getErrors('instance');
		if(empty($errors)) return array(0, 'ok');
		if($this->instance_error) return $this->instance_error[0];
		//判断系统错误，只过滤类型和大小，其余错误为系统错误
		foreach($errors as $error){
			if(strpos($error, 'extensions') > -1 || strpos($error, 'MIME-types') > -1 || strpos($error, '附档名') > -1){
				return array(200, '格式错误', $error);
			}elseif(strpos($error, 'large') > -1 || strpos($error, 'smaller') > -1 || strpos($error, '太大') > -1 || strpos($error, '太小') > -1 ){
				return array(100, '大小错误', $error);
			}
			return array(11, '系统错误', $error);
		}
		return array(10, '系统错误');
	}

	/**
	 * 设置扩展错误接口
	 * @param $code ： creativeType + number（2位）,小于100为系统级错误
	 * @param $message
	 */
	public function setError($code, $message){
		$this->instance_error[] = array($code, $message);
	}
	
	/**
	 * 设定对应的updateLimit
	 * @param $id
	 * @return bool
	 */
	public function setId($id = ''){
		return $this->__id = static::idName.$id;
	}
	
	public function getId() {
		return $this->__id;
	}
	
	public function getUploadLimit($key = NULL) {
		if(!isset($key)) $key = $this->getId();
		return Yii::app()->params['uploadLimit'][$key];
	}
	
	public function createRandomName($extName = NULL) {
		if(!isset($extName)) $extName = $this->extensionName;
		return substr($_SERVER['REQUEST_TIME'], 2).'.'.mt_rand(0, (double)microtime() * 1000000).'.'.$extName;
	}
	
	public function initFileName($params = NULL, $itemParams = NULL, $extName = NULL) {
		if(!isset($params)) $params = Yii::app()->params;
		if(!isset($itemParams)) $itemParams = $this->getUploadLimit();
		
		/* format extname */
		$this->extensionName = $this->instance->extensionName;
		if($itemParams['formatExtName'] != 'no') {
			$this->extensionName = $this->formatExtName($this->instance->getType());
		}
		
		$fileName = $this->createRandomName($extName);
		$localPath = $params['uploadPath'].$itemParams['savePath'].'/'.$fileName;
		$this->instance->saveAs($localPath);
		$this->path = $this->thumbPath = $itemParams['savePath'].'/'.$fileName;
		$this->urlPath = $this->thumbUrlPath = $itemParams['urlPath'].'/'.$fileName;
		$this->absUrl = $this->absThumbUrl = $params['uploadUrl'] . $itemParams['urlPath'] . '/' . $fileName;;
		$this->size = $this->instance->size;
		return array($fileName, $localPath);
	}
	
	public function thumbImage($localPath, $params = NULL, $itemParams = NULL) {
		if(!isset($params)) $params = Yii::app()->params;
		if(!isset($itemParams)) $itemParams = $this->getUploadLimit();
		
		$instance = $this->instance;
		
		/* 保存缩略图 */
		$im = false;
		if($itemParams['thumbWidth'] && $itemParams['thumbHeight']) {
			$extName = $this->formatExtName($instance->getType());
			if($extName == 'gif') {
				$im = imagecreatefromgif($localPath);
			}elseif($extName == 'jpg') {
				$im = imagecreatefromjpeg($localPath);
			}elseif($extName == 'png') {
				$im = imagecreatefrompng($localPath);
			}else{
				throw new CHttpException(500, 'Unknow mime type '.$instance->getType());
			}

			if($im) {
				CThumb::resizeImage($im, $itemParams['thumbWidth'], $itemParams['thumbHeight'], $localPath . '.thumb.' . $this->extensionName, $this->extensionName );
				$this->thumbPath = $this->path . '.thumb.'.$this->extensionName;
				$this->absThumbUrl = $this->absUrl . '.thumb.'.$this->extensionName;
			}
		}
		return $im;
	}

	public function initDBAttachment($attr = NULL) {
		$instance = $this->instance;
		
		$this->thumbPath = $this->path;
		
		/* 写入DB */
		$attachModel = new Attachment;
		$attachModel->name = pathinfo($instance->name, PATHINFO_FILENAME);
		$attachModel->metaType = $this->extensionName;
		$attachModel->fileSize = $this->size;
		$attachModel->sourcePath = $this->urlPath;
		/* 自定义属性 */
		if(is_array($attr) && $attr) {
			foreach($attr as $k => $v)
				$attachModel->$k = $v;
		}
		return $attachModel;
	}

	public function saveDBAttachment($model, $localPath = NULL) {
		$model->thumbPath = $this->thumbUrlPath;
		if($model->save()) {
			if(isset($localPath) && (!$this->width || !$this->height)) {
				$imgInfo = getimagesize($localPath);
				$this->width = $imgInfo[0];
				$this->height = $imgInfo[1];
			}
			$this->attachmentId = $model->id;
		}else{
			$errors = $model->getErrors();
			$error = array_pop($errors);
			$error = array_pop($error);
			throw new CHttpException(500, $error);
		}
	}

	public function defaultResult($otherKeys = array()) {
		$result = array(
			'fileName' => $this->instance->name,
	        'attachId' => $this->attachmentId,
	        'size' => $this->size,
	        'path' => $this->path,
			'urlPath' => $this->urlPath,
	        'absUrl' => $this->absUrl,
	        'thumbPath' => $this->thumbPath,
			'thumbUrlPath' => $this->thumbUrlPath,
	        'width' => $this->width,
	        'height' => $this->height,
        );
		if(is_string($otherKeys)) $otherKeys = Util::split($otherKeys);
		foreach($otherKeys as $k => $name) {
			if(is_numeric($k)) $k = $name;
			$result[$k] = $this->$name;
		}
		return $result;
	}
	
	private function mime2extName($mime) {
		$params = Yii::app()->params;
		$extTypes = $params['uploadExtTypes'];
		$mime = strtolower(trim($mime));
		foreach($extTypes as $name => $item) {
			if(is_string($item)) $item = array($item, $name);
			list($mimeTypes, $types) = $item;
			$mimeTypes = Util::split($mimeTypes);
			if(in_array($mime, $mimeTypes)) {
				$types = Util::split($types);
				return array_shift($types);
			}
		}
		return null;
	}
	
	public function formatExtName($mime) {
		$extName = $this->mime2extName($mime);
		if(empty($extName))
			throw new CHttpException(500, 'Unknow mime type '.$mime);
		return $extName;
	}
}

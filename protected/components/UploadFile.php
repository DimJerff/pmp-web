<?php
class UploadFile extends CFormModel {
	public $instance;

	public $path;
	public $url;
	public $absUrl;
	public $size;
	public $thumbUrl;
	public $width;
	public $height;
	public $attachmentId;
	public $duration;
	public $bitrate;
	public $mimetype;
	public $support_list;
	public $richType;

	private $_ruleType = 'img';
	private $_extensionName;
	private $_instance_error = null;
	private $_limitConfig = null;
	private $_adgroupId = null;
	private $_campaignId = null;

	public function __construct($limitType='') {
		parent::__construct('');
		$this->_limitConfig = $this->getUploadLimit($limitType);
		$this->_ruleType = $this->_limitConfig['ruleType'];
	}

	public function rules() {
		$rule = Yii::app()->params['uploadRuleType'][$this->_ruleType];
		if(is_string($rule)) $rule = array($rule, '');
		list($validator, $ignoreKeys) = $rule;
		$itemConfig = $this->_limitConfig;

		// 初始化mime和实际的types
		$this->_setMimeOfTypes($itemConfig);

		// 创意视频验证规则根据广告组设定制定验证宽高比例时长规则
		$this->_setVideoPartRuleOfAdx($itemConfig);

		$ignoreKeys = array_merge(Util::split($ignoreKeys), Util::split('ruleType,formatExtName,extTypes,urlPath,thumbWidth,thumbHeight,resultExt'));
		if(is_array($ignoreKeys)) {
			foreach($ignoreKeys as $k)
				unset($itemConfig[trim($k)]);
		}

		return array(CMap::mergeArray(array('instance', $validator, ), $itemConfig),);
	}

	/**
	 * 判断操作权限,并保存attachment
	 * @param $adgroupId
	 * @throws CHttpException
	 */
	public function setAdgroupId($adgroupId){
		if(empty($adgroupId)) return ;
		$user = Yii::app()->user;
		$adgroupModel = CampaignAdGroup::model()->findByPk($adgroupId);
		if(!$user->companyRight($adgroupModel->company)){
			throw new CHttpException(403, 'Record No Found');
		}

		$this->_adgroupId = $adgroupModel->id;
		$this->_campaignId = $adgroupModel->campaignId;
	}

	/**
	 * 保存,同Model层
	 * @param array $attr
	 * @return array|bool
	 * @throws CHttpException
	 */
	public function save($attr = array()) {
		if(!$this->validate() || !is_object($this->instance)){
			return false;
		}
		if(!$this->saveFile()){
			return false;
		}

		if($this->_limitConfig['thumbWidth'] && $this->_limitConfig['thumbHeight']) {
			if(empty($this->_limitConfig['water'])) $this->_limitConfig['water'] = false;
			$this->thumbImage($this->path, $this->_limitConfig['thumbWidth'], $this->_limitConfig['thumbHeight'], $this->_limitConfig['water']);
		}

		if($this->_adgroupId){
			$attr['adGroupId'] = $this->_adgroupId;
			$attr['campaignId'] = $this->_campaignId;
			$attr['companyId'] = Yii::app()->user->defaultCompanyId();
			$this->addAttachment($attr);
		}

		return $this->result($this->_limitConfig['resultExt'] ? $this->_limitConfig['resultExt'] : array());
	}

	/**
	 * 获取上传文件错误信息，只返回第一条错误信息
	 * @return array|string
	 */
	public function error(){
		$errors = parent::getErrors('instance');
		if(empty($errors)) return array(0, 'ok');
		if($this->_instance_error) return $this->_instance_error[0];
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
		$this->_instance_error[] = array($code, $message);
	}

	/**
	 * 获取当前上传请求的配置信息
	 * @param $key
	 * @return mixed
	 * @throws CHttpException
	 */
	public function getUploadLimit($key) {
		if(empty($key) || empty(Yii::app()->params->uploadLimit[$key])){
			throw new CHttpException(403, 'Invalid Request');
		}
		return Yii::app()->params['uploadLimit'][$key];
	}

	/**
	 * 生成随机文件名
	 * @param $extensionName
	 * @return string
	 */
	public static function randomName($extensionName) {
		return substr($_SERVER['REQUEST_TIME'], 2).mt_rand(0, (double)microtime() * 1000000).'.'.$extensionName;
	}

	/**
	 * 保存上传文件到配置目录,并获取保存路径
	 * @return string
	 * @throws CHttpException
	 */
	public function saveFile() {
		if(!isset($params)) $params = Yii::app()->params;
		$path = $params['uploadPath'];  // 存储路径

		// 判断存储路径项目根目录还是上传到CDN缓存目录
		if(strpos($this->_limitConfig['urlPath'], '/') === 0){
			$urlPath = substr($this->_limitConfig['urlPath'], 1);
			$savePath = $path.$urlPath;
			$url = '/';
		}else{
			$urlPath = $this->_limitConfig['urlPath'];
			$savePath = $path.$params['uploadDir'].'/'.$urlPath;
			$url = $params['uploadUrl'];  // 链接前缀
		}
		if(!is_dir($savePath)) mkdir($savePath, 0777, true);

		$this->_extensionName = $this->instance->extensionName;
		$this->size = $this->instance->size;
		if($this->_limitConfig['formatExtName'] === false) {
			$this->_extensionName = $this->formatExtName($this->instance->getType());
		}

		$fileName = $this->randomName($this->_extensionName);

		$this->path = $savePath.'/'.$fileName;
		$this->instance->saveAs($this->path);
		$this->url = $this->thumbUrl = $urlPath.'/'.$fileName;
		$this->absUrl = $url . $this->url;
		return true;
	}

	/**
	 * 格式化扩展名
	 * @param $mime
	 * @return mixed|null
	 * @throws CHttpException
	 */
	public function formatExtName($mime) {
		$extName = $this->mime2extName($mime);
		if(empty($extName))
			throw new CHttpException(500, 'Unknow mime type '.$mime);
		return $extName;
	}

	/**
	 * 保存缩略图
	 * @param $targetFile
	 * @param $width
	 * @param $height
	 * @param $water
	 * @return bool|string|void
	 */
	public function thumbImage($targetFile, $width, $height, $water) {
		$extensionName = $this->_extensionName;
		switch($this->_ruleType){
			case 'image':
				$function = array('GImage', 'thumb');
				break;
			case 'video':
				$extensionName = 'jpg';
				$function = array('GFfmpeg', 'videoThumb');
				break;
			default:
				return false;
		}
		$thumbFile = $targetFile . '.thumb.' . $extensionName;
		$status = call_user_func($function, $targetFile, $thumbFile, $width, $height);
		if($status) {
			//添加缩略图水印
			if($status && $water) GImage::water($thumbFile, $thumbFile, $water, 1, 0, 2);
			$this->thumbUrl = $this->url . '.thumb.'.$extensionName;
		}
		return $status;
	}

	/**
	 * 保存附件
	 * @param $attr
	 * @throws CHttpException
	 */
	public function addAttachment($attr) {
		$attachModel = new Attachment;
		$attachModel->name = pathinfo($this->instance->name, PATHINFO_FILENAME);
		$attachModel->metaType = $this->_extensionName;
		$attachModel->fileSize = $this->size;
		$attachModel->sourcePath = $this->url;

		if(is_array($attr) && $attr) {
			foreach($attr as $k => $v)
				$attachModel->$k = $v;
		}
		$attachModel->thumbPath = $this->thumbUrl;
		if($attachModel->save()) {
			if(isset($this->path) && (!$this->width || !$this->height)) {
				$imgInfo = getimagesize($this->path);
				$this->width = $imgInfo[0];
				$this->height = $imgInfo[1];
			}
			$this->attachmentId = $attachModel->id;
		}else{
			$errors = $attachModel->getErrors();
			$error = array_pop($errors);
			$error = array_pop($error);
			throw new CHttpException(500, $error);
		}
	}

	/**
	 * 返回结果
	 * @param array $otherKeys
	 * @return array
	 */
	public function result($otherKeys = array()) {
		$result = array(
			'fileName' => $this->instance->name,
			'attachId' => $this->attachmentId,
			'size' => $this->size,
			'url' => $this->url,
			'thumbUrl' => $this->thumbUrl,
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

	/**
	 * 根据mime获取文件类型
	 * @param $mime
	 * @return mixed|null
	 */
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

	/**
	 * 根据扩展类型绑定文件的mime和types
	 * @param $itemConfig
	 * @return mixed
	 */
	private function _setMimeOfTypes(&$itemConfig){
		if($itemConfig['extTypes']) {
			$extTypes = Yii::app()->params->uploadExtTypes;
			$extNames = Util::split($itemConfig['extTypes']);
			$types = $mimeTypes = array();
			foreach($extNames as $name) {
				$item = $extTypes[$name];
				if(!isset($item)) continue;
				if(is_string($item)) $item = array($item, $name);
				$mimeTypes = array_merge($mimeTypes, Util::split($item[0]));
				$types = array_merge($types, Util::split($item[1]));
			}
			$itemConfig['types'] = implode(',', $types);
			$itemConfig['mimeTypes'] = implode(',', $mimeTypes);
		}
		return $itemConfig;
	}

	/**
	 * 创意视频验证规则根据广告组设定制定验证宽高比例时长规则
	 * @param $itemConfig array 从配置信息中获取的数据
	 * @return mixed
	 */
	private function _setVideoPartRuleOfAdx(&$itemConfig) {
		if (!empty($_GET['adx']) && !empty($itemConfig['support_list'][$_GET['adx']])) {
			$support_list_item = $itemConfig['support_list'][$_GET['adx']];
			foreach ($support_list_item as $k=>$v) {
				$itemParams[$k] = $v;
			}
		}

		return $itemConfig;
	}
}

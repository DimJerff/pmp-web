<?php
class ImageValidator extends CFileValidator{
	public $widthHeights;
	public $wrongWidthHeight;
	public $scales;
	public $minWidthHeight;

	/**
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 * @param CUploadedFile $file uploaded file passed to check against a set of rules
	 * @return void
	 */
	protected function validateFile($object, $attribute, $file) {
		parent::validateFile($object,$attribute,$file);
		if($object->hasErrors()) return ;
		list($width, $height) = @getimagesize($file->getTempName());
		/* 检查是否在范围内的高宽 */
		if($this->widthHeights) {
			$result = !$this->validWidthHeight($this->widthHeights, $width, $height);
			/* 不在则添加错误 */
			if($result) {
				$message = $this->wrongWidthHeight !==null ? $this->wrongWidthHeight : Yii::t('yii','The file "{file}" cannot be uploaded. Only files with these size are allowed: {widthHeights}.');
				$this->addError($object, $attribute, $message, array(
					'{file}' => $file->getName(),
					'{widthHeights}' => implode(', ', $this->widthHeights),
				));
				$object->setError(302, '尺寸错误：'.implode(', ', $this->widthHeights));
			}
		}
		/* 验证长宽比 */
		if($this->scales){
			$result = !$this->validScale($this->scales, $width, $height);
			if($result){
				$message = Yii::t('yii','The file "{file}" cannot be uploaded. Only files with these scale are allowed: {scale}.');
				$this->addError($object, $attribute, $message, array(
					'{file}' => $file->getName(),
					'{scale}' => implode(', ', $this->scales),
				));
				$object->setError(303, '比例错误：'.implode(', ', $this->scales));
				$error = true;
			}
		}
		/* 验证最小长宽 */
		if($this->minWidthHeight){
			$result = !$this->validMinWidthHeight($this->minWidthHeight, $width, $height);
			if($result){
				$message = Yii::t('yii','The file "{file}" cannot be uploaded. Only files with these size are allowed: {minWidthHeight}.');
				$this->addError($object, $attribute, $message, array(
					'{file}' => $file->getName(),
					'{minWidthHeight}' => implode(', ', $this->minWidthHeight),
				));
				$object->setError(302, '尺寸错误：'.implode(', ', $this->minWidthHeight));
				$error = true;
			}
		}
	}

	/**
	 * 验证长宽
	 * @param $allow_widthHeights
	 * @param $width
	 * @param $height
	 * @return bool
	 */
	public static function validWidthHeight($allow_widthHeights, $width, $height){
		$widthHeight = sprintf("%sx%s",$width,$height);
		if(in_array($widthHeight, $allow_widthHeights)) return true;
		return false;
	}

	/**
	 * 验证比例
	 * @param $allow_scales
	 * @param $width
	 * @param $height
	 * @return bool
	 */
	public static function validScale($allow_scales, $width, $height){
		$scale_array = array();
		$scale = $width / $height;
		foreach($allow_scales as $s){
			$s = explode(':',$s);
			$scale_array[] = $s[0] / $s[1];
		}
		if(in_array($scale,$scale_array)) return true;
		return false;
	}

	/**
	 * 验证最小长宽
	 * @param $minWidthHeight
	 * @param $width
	 * @param $height
	 * @return bool
	 */
	public static function validMinWidthHeight($minWidthHeight, $width, $height){
		$min = explode('x', $minWidthHeight);
		if($width >= $min[0] && $height >= $min[1]) return true;
		return false;
	}
}
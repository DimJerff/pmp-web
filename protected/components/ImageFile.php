<?php
class ImageFile extends FormModel
{
	public function save($attr = array()) {
		if(!parent::save($attr)) return false;
	
		/* 创建随机文件名 */
		list($fileName, $localPath) = $this->initFileName();
	
		/* 初始化DB */
		$attachModel = $this->initDBAttachment($attr);
		/* 保存缩略图 */
		$im = $this->thumbImage($localPath);
		if($im) {
			$this->width = imagesx($im);
			$this->height = imagesy($im);
		}
	
		/* 写入DB */
		$this->saveDBAttachment($attachModel, $localPath);
	
		return $this->defaultResult();
	}
}
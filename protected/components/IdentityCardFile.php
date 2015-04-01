<?php
class IdentityCardFile extends FormModel
{
	const idName = 'identityCard';

	public function save($attr = array()) {
		if(!parent::save($attr)) return false;

        /* 创建随机文件名 */
        list($fileName, $localPath) = $this->initFileName();

        /* 保存缩略图 */
        $this->thumbImage($localPath);
        
        return $this->defaultResult();
	}
}
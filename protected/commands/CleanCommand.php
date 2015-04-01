<?php

/**
 * Class CleanCommand
 * 清理脚本：清除框架内缓存信息脚本
 */
class CleanCommand extends ConsoleCommand
{
	/* yiic clean dbschema --tableName=xxx */
	public function actionDbSchema($tableName)
	{
		$db=Yii::app()->getDb();
		$table = $db->getSchema()->getTable($tableName,true);
		print_r($table);
		echo "Clean DbSchema:".$tableName." : Success";
	}
}
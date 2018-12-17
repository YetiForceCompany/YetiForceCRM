<?php
/**
 * MultiImages cron, deleting attachment from database and storage..
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Lorencik <m.lorencik@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
$dbCommand = \App\Db::getInstance()->createCommand();
$query = (new \App\Db\Query())->select(['id', 'name', 'path', 'key'])->from('u_#__file_upload_temp')->where(['status' => 0]);
$query->andWhere(['<', 'createdtime', date('Y-m-d H:i:s', strtotime('-1 day'))])->limit(AppConfig::performance('CRON_MAX_ATACHMENTS_DELETE'));

$dataReader = $query->createCommand()->query();
while ($row = $dataReader->read()) {
	$dbCommand->delete('u_#__file_upload_temp', ['id' => $row['id']])->execute();
	$fileName = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $row['path'] . $row['key'];
	if (file_exists($fileName)) {
		chmod($fileName, 0755);
		unlink($fileName);
	}
}
$dataReader->close();

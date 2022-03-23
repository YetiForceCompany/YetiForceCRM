<?php
/**
 * Cron task for deleting attachment from database temp table and storage.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Lorencik <m.lorencik@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Vtiger_FileUploadTemp_Cron class.
 */
class Vtiger_FileUploadTemp_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$query = (new \App\Db\Query())->select(['id', 'path', 'key'])
			->from(\App\Fields\File::TABLE_NAME_TEMP)
			->where(['status' => 0])
			->andWhere(['<', 'createdtime', date('Y-m-d H:i:s', strtotime('-1 day'))])
			->limit(App\Config::performance('CRON_MAX_ATACHMENTS_DELETE'));

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
	}
}

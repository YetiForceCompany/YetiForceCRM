<?php

/**
 * RecycleBin module model Class.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

/**
 * Class RecycleBin_Module_Model.
 */
class RecycleBin_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function to get all available modules for list.
	 *
	 * @return array|mixed
	 */
	public function getAllModuleList()
	{
		return \vtlib\Functions::getAllModules(true, false, 0);
	}

	/**
	 * Function to identify if the module supports quick search or not.
	 */
	public function isQuickSearchEnabled()
	{
		return false;
	}

	/**
	 * Delete all records from recycle to given date and module.
	 *
	 * @param string $untilModifiedTime
	 * @param int    $userId
	 * @param int[]  $recordsToDelete
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public static function deleteAllRecords(string $untilModifiedTime, int $userId)
	{
		$actualUserId = App\User::getCurrentUserId();
		try {
			if (!App\User::isExists($userId)) {
				throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED', 406);
			}
			App\User::setCurrentUserId($userId);
			$modulesList = \vtlib\Functions::getAllModules(true, false, 0);
			$deleteMaxCount = AppConfig::module('RecycleBin', 'DELETE_MAX_COUNT');
			$dataReader = (new \App\Db\Query())->select(['crmid', 'setype'])->from('vtiger_crmentity')
				->where(
					['and',
						['vtiger_crmentity.deleted' => 1],
						['in', 'setype', array_column($modulesList, 'name')],
						['<=', 'modifiedtime', $untilModifiedTime]])
						->createCommand()->query();
			while ($row = $dataReader->read()) {
				if (0 >= $deleteMaxCount) {
					(new App\BatchMethod(['method' => 'RecycleBin_Module_Model::deleteAllRecords', 'params' => [$untilModifiedTime, $userId]]))->save();
					break;
				}
				$recordModel = Vtiger_Record_Model::getInstanceById($row['crmid'], $row['setype']);
				if (!$recordModel->privilegeToDelete()) {
					continue;
				}
				$recordModel->delete();
				unset($recordModel);
				$deleteMaxCount--;
			}
			App\User::setCurrentUserId($actualUserId);
		} catch (\Throwable $ex) {
			\App\Log::error($ex->__toString());
			App\User::setCurrentUserId($actualUserId);
		}
	}
}

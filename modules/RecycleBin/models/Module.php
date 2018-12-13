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
		return \vtlib\Functions::getAllModules(1, true);
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
	 * @param int[]  $recordIds
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public static function deleteAllRecords(string $untilModifiedTime, int $userId, array $recordsToDelete = [])
	{
		$actualUserId = App\User::getCurrentUserId();
		try {
			App\User::setCurrentUserId($userId);
			$modulesList = \vtlib\Functions::getAllModules(true, false, 0);
			if (empty($recordsToDelete)) {
				foreach ($modulesList as $module) {
					$recordIds = (new \App\Db\Query())->select(['crmid'])->from('vtiger_crmentity')->where(
						['and',
							['deleted' => 1],
							['setype' => $module['name']],
							['<=', 'modifiedtime', $untilModifiedTime]])->column();
					if (!empty($recordIds)) {
						$recordsToDelete = array_merge($recordsToDelete, $recordIds);
					}
				}
			}
			$deleteMaxCount = AppConfig::module('RecycleBin', 'DELETE_MAX_COUNT');
			foreach ($recordsToDelete as $key => $recordId) {
				if (0 < $deleteMaxCount) {
					if (App\Record::isCrmExist($recordId)) {
						$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
						if (!$recordModel->privilegeToDelete()) {
							continue;
						}
						$recordModel->delete();
						unset($recordModel);
					}
					unset($recordsToDelete[$key]);
					$deleteMaxCount--;
				} else {
					(new App\BatchMethod(['method' => 'RecycleBin_Module_Model::deleteAllRecords', 'params' => App\Json::encode([date('Y-m-d H:i:s'), App\User::getCurrentUserId(), $recordsToDelete])]))->save();
					break;
				}
			}
			App\User::setCurrentUserId($actualUserId);
		} catch (\Throwable $ex) {
			\App\Log::error($ex->getMessage());
			App\User::setCurrentUserId($actualUserId);
		}
	}
}

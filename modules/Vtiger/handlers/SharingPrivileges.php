<?php

/**
 * Sharing privileges handler
 * @package YetiForce.Handler
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_SharingPrivileges_Handler
{

	/**
	 * EntityAfterSave function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		if (!\AppConfig::security('PERMITTED_BY_SHARED_OWNERS')) {
			return false;
		}
		$recordModel = $eventHandler->getRecordModel();
		$removeUser = $recordModel->getPreviousValue('assigned_user_id');
		if ($removeUser) {
			$addUser = $recordModel->get('assigned_user_id');
			$recordsByModule = Users_Privileges_Model::getSharedRecordsRecursively($recordModel->getId(), $recordModel->getModuleName());
			if (!$recordsByModule) {
				return false;
			}
			$db = \App\Db::getInstance();
			foreach ($recordsByModule as &$records) {
				$db->createCommand()->delete('u_#__crmentity_showners', ['userid' => $removeUser, 'crmid' => $records])->execute();
				if ($addUser) {
					$usersExist = [];
					$query = (new \App\Db\Query())->select(['crmid', 'userid'])->from('u_#__crmentity_showners')->where(['userid' => $addUser, 'crmid' => $records]);
					$dataReader = $query->createCommand()->query();
					while ($row = $dataReader->read()) {
						$usersExist[$row['crmid']][$row['userid']] = true;
					}
					foreach ($records as &$record) {
						if (!isset($usersExist[$record][$addUser])) {
							$db->createCommand()
								->insert('u_#__crmentity_showners', [
									'crmid' => $record,
									'userid' => $addUser
								])->execute();
						}
					}
				}
			}
		}
	}
}

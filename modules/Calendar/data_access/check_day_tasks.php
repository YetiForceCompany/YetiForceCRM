<?php

/**
 * Lock saving events after exceeding the limit
 * @package YetiForce.DataAccess
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class DataAccess_check_day_tasks
{

	public $config = true;

	public function process($moduleName, $ID, $recordData, $config)
	{
		if (!in_array($moduleName, ['Calendar', 'Events'])) {
			return ['save_record' => true];
		}
		$userRecordModel = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();
		$typeInfo = 'info';
		$statusType = $config['statusType'];
		switch ($statusType) {
			case 1:
				$status = Calendar_Module_Model::getComponentActivityStateLabel('current');
				break;
			case 2:
				$status = Calendar_Module_Model::getComponentActivityStateLabel('history');
				break;
			default:
				$status = empty($config['status']) ? [] : $config['status'];
				break;
		}
		$sql = 'SELECT count(*) as count FROM vtiger_activity 
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid 
			WHERE vtiger_crmentity.deleted = ? && vtiger_activity.date_start = ? && vtiger_activity.smownerid = ?';
		$params = [0, $recordData['date_start'], $userRecordModel->getId()];
		if (!empty($status)) {
			$sql .= ' && vtiger_activity.status IN (' . generateQuestionMarks($status) . ')';
			$params[] = $status;
		}
		$result = $db->pquery($sql, $params);

		if ($config['lockSave'] == 1) {
			$typeInfo = 'error';
		}

		$count = $db->getSingleValue($result);
		if ($count >= $config['maxActivites']) {
			$title = '<strong>' . vtranslate('Message', 'DataAccess') . '</strong>';

			$info = ['text' => vtranslate($config['message'], 'DataAccess'),
				'title' => $title,
				'type' => 1
			];
			return [
				'save_record' => false,
				'type' => 3,
				'info' => is_array($info) ? $info : [
					'text' => vtranslate($config['message'], 'DataAccess'),
					'ntype' => $typeInfo
					]
			];
		} else {
			return ['save_record' => true];
		}
	}

	public function getConfig($id, $module, $baseModule)
	{
		return [];
	}
}

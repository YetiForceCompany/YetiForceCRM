<?php
/**
 * Lock saving events after exceeding the limit
 * @package YetiForce.DataAccess
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class DataAccessCheckDayTasks
 */
class DataAccessCheckDayTasks
{

	/**
	 * Config
	 * @var bool
	 */
	public $config = true;

	/**
	 * Process
	 * @param string $moduleName
	 * @param int $ID
	 * @param array $recordData
	 * @param array $config
	 * @return array
	 */
	public function process($moduleName, $ID, $recordData, $config)
	{
		if (!in_array($moduleName, ['Calendar', 'Events'])) {
			return ['save_record' => true];
		}
		$userRecordModel = Users_Record_Model::getCurrentUserModel();
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
		$query = (new App\Db\Query())->from('vtiger_activity')->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_activity.activityid')->where(['vtiger_crmentity.deleted' => 0, 'vtiger_activity.date_start' => $recordData['date_start'], 'vtiger_activity.smownerid' => $userRecordModel->getId()]);
		if (!empty($status)) {
			$query->andWhere(['vtiger_activity.status' => $status]);
		}

		if ($config['lockSave'] == 1) {
			$typeInfo = 'error';
		}

		$count = $query->count();
		if ($count >= $config['maxActivites']) {
			$title = '<strong>' . \App\Language::translate('Message', 'DataAccess') . '</strong>';

			$info = ['text' => \App\Language::translate($config['message'], 'DataAccess'),
				'title' => $title,
				'type' => 1
			];
			return [
				'save_record' => false,
				'type' => 3,
				'info' => is_array($info) ? $info : [
				'text' => \App\Language::translate($config['message'], 'DataAccess'),
				'ntype' => $typeInfo
				]
			];
		} else {
			return ['save_record' => true];
		}
	}

	/**
	 * Get config
	 * @param id $id
	 * @param string $module
	 * @param string $baseModule
	 * @return array
	 */
	public function getConfig($id, $module, $baseModule)
	{
		return [];
	}
}

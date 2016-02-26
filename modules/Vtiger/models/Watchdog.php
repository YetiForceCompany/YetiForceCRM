<?php

/**
 * Watching Model Class
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Watchdog_Model extends Vtiger_Base_Model
{

	/**
	 * Function to get the instance by id
	 * @return <Home_Notification_Model>
	 */
	public static function getInstanceById($record, $moduleName)
	{
		$instance = Vtiger_Cache::get('WatchdogModel', $moduleName . $record);
		if ($instance) {
			return $instance;
		}
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Watchdog', $moduleName);
		$instance = new $modelClassName();
		$instance->set('record', $record);
		$instance->set('module', $moduleName);

		Vtiger_Cache::set('WatchdogModel', $moduleName . $record, $instance);
		return $instance;
	}

	/**
	 * Function to get the instance by module
	 * @return <Home_Notification_Model>
	 */
	public static function getInstance($moduleName)
	{
		$instance = Vtiger_Cache::get('WatchdogModel', $moduleName);
		if ($instance) {
			return $instance;
		}
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Watchdog', $moduleName);
		$instance = new $modelClassName();
		$instance->set('module', $moduleName);

		Vtiger_Cache::set('WatchdogModel', $moduleName, $instance);
		return $instance;
	}

	public function isWatchingModule()
	{
		if ($this->has('isWatchingModule')) {
			return $this->get('isWatchingModule');
		}
		$return = false;
		$modules = $this->getWatchingModules();
		if (in_array(Vtiger_Functions::getModuleId($this->get('module')), $modules)) {
			$return = true;
		}
		$this->set('isWatchingModule', $return);
		return $return;
	}

	public function isWatchingRecord()
	{
		if ($this->has('isWatchingRecord')) {
			return $this->get('isWatchingRecord');
		}
		$return = $this->isWatchingModule();
		$db = PearDatabase::getInstance();
		$userModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$sql = 'SELECT state FROM u_yf_watchdog_record WHERE userid = ? AND record = ?';
		$result = $db->pquery($sql, [$userModel->getId(), $this->get('record')]);
		$count = $db->getRowCount($result);
		$this->set('isRecord', $count);
		if ($count) {
			if ($db->getSingleValue($result) == 1) {
				$return = true;
			} else {
				$return = false;
			}
		}
		$this->set('isWatchingRecord', $return);
		return $return;
	}

	public function getWatchingModules($reload = false)
	{
		$userModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$modules = Vtiger_Cache::get('getWatchingModules', $userModel->getId());
		if (!$reload && $modules !== false) {
			return $modules;
		}
		$db = PearDatabase::getInstance();
		$sql = 'SELECT * FROM u_yf_watchdog_module WHERE userid = ?';
		$result = $db->pquery($sql, [$userModel->getId()]);
		$modules = [];
		while ($row = $db->getRow($result)) {
			$modules[] = $row['module'];
		}
		Vtiger_Cache::set('getWatchingModules', $userModel->getId(), $modules);
		return $modules;
	}

	public function changeRecordState($state)
	{
		$isWatchingRecord = $this->isWatchingRecord();
		if ($isWatchingRecord && $state == 1) {
			return true;
		}
		$userModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$db = PearDatabase::getInstance();
		$row = ['state' => $state];
		if ($this->get('isRecord') == 0) {
			$row['userid'] = $userModel->getId();
			$row['record'] = $this->get('record');
			$db->insert('u_yf_watchdog_record', $row);
		} else {
			$db->update('u_yf_watchdog_record', $row, 'userid = ? AND record = ?', [$userModel->getId(), $this->get('record')]);
		}
	}

	public function changeModuleState($state)
	{
		$isWatchingRecord = $this->isWatchingModule();
		if ($isWatchingRecord && $state == 1) {
			return true;
		}
		$userModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$db = PearDatabase::getInstance();
		$moduleId = Vtiger_Functions::getModuleId($this->get('module'));
		if ($state == 1) {
			$db->insert('u_yf_watchdog_module', [
				'userid' => $userModel->getId(),
				'module' => $moduleId
			]);
		} else {
			$db->delete('u_yf_watchdog_module', 'userid = ? AND module = ?', [$userModel->getId(), $moduleId]);
		}
	}
}

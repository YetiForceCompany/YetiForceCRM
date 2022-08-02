<?php

/**
 * Settings RealizationProcesses module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_RealizationProcesses_Module_Model extends Settings_Vtiger_Module_Model
{
	/**
	 * Gets status.
	 *
	 * @return array - array of status
	 */
	public static function getStatusNotModify()
	{
		\App\Log::trace('Entering Settings_RealizationProcesses_Module_Model::getStatusNotModify() method ...');
		$dataReader = (new App\Db\Query())->from('vtiger_realization_process')
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$moduleId = $row['module_id'];
			$moduleName = App\Module::getModuleName($moduleId);
			$return[$moduleName]['id'] = $moduleId;
			$status = \App\Json::decode(html_entity_decode($row['status_indicate_closing']));
			if (!\is_array($status)) {
				$status = [$status];
			}
			$return[$moduleName]['status'] = $status;
			$return[$moduleName]['id'] = $moduleId;
		}
		$dataReader->close();

		\App\Log::trace('Exiting Settings_RealizationProcesses_Module_Model::getStatusNotModify() method ...');

		return $return;
	}

	/**
	 * Update status.
	 *
	 * @param mixed $moduleId
	 * @param mixed $status
	 *
	 * @return array - array of status
	 */
	public static function updateStatusNotModify($moduleId, $status)
	{
		\App\Log::trace('Entering Settings_RealizationProcesses_Module_Model::updateStatusNotModify() method ...');
		\App\Db::getInstance()->createCommand()->update('vtiger_realization_process', [
			'status_indicate_closing' => \App\Json::encode($status),
		], ['module_id' => $moduleId])->execute();
		\App\Log::trace('Exiting Settings_RealizationProcesses_Module_Model::updateStatusNotModify() method ...');
		return true;
	}
}

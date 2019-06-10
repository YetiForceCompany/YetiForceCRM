<?php
/**
 * Settings SLAPolicy Save Action class.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_SLAPolicy_Save_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$recordId = null;
		if (!$request->isEmpty('record')) {
			$recordId = $request->getInteger('record');
		}
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		if (!empty($recordId)) {
			$recordModel = Settings_SLAPolicy_Record_Model::getInstanceById($recordId);
		} else {
			$recordModel = Settings_SLAPolicy_Record_Model::getCleanInstance();
		}
		$recordModel->set('name', $request->getByType('name', 'Text'));
		$recordModel->set('operational_hours', $request->getInteger('operational_hours'));
		$recordModel->set('tabid', \App\Module::getModuleId($request->getByType('source_module', 2)));
		$conditions = \App\Condition::getConditionsFromRequest(\App\Json::decode($request->getByType('conditions', 'Text')));
		$recordModel->set('conditions', \App\Json::encode($conditions));
		$recordModel->set('reaction_time', $request->getByType('reaction_time', 'TimePeriod'));
		$recordModel->set('idle_time', $request->getByType('idle_time', 'TimePeriod'));
		$recordModel->set('resolve_time', $request->getByType('resolve_time', 'TimePeriod'));
		$recordModel->set('business_hours', $request->getByType('business_hours', 'Text'));
		$recordModel->save();
		header('location: ' . $moduleModel->getDefaultUrl());
	}
}

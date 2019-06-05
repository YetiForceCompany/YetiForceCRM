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
		$recordModel->set('conditions', $request->getByType('conditions', 'Text'));
		$recordModel->save();
		header('location: ' . $moduleModel->getDefaultUrl());
	}
}

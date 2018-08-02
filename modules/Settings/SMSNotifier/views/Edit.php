<?php
/**
 * Edit View Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Edit View Class.
 */
class Settings_SMSNotifier_Edit_View extends Settings_Vtiger_BasicModal_View
{
	/**
	 * Check Permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(\App\Request $request)
	{
		parent::checkPermission($request);
		$record = $request->get('record');
		$moduleName = $request->getModule(false);
		if ($record) {
			$recordModel = Settings_SMSNotifier_Record_Model::getInstanceById($record, $moduleName);
			if (!$recordModel->getProviderInstance()) {
				throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
			}
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		parent::preProcess($request);
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);

		if ($recordId) {
			$recordModel = Settings_SMSNotifier_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
		} else {
			$recordModel = Settings_SMSNotifier_Record_Model::getCleanInstance($qualifiedModuleName);
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('MODULE_MODEL', $recordModel->getModule());
		$viewer->assign('PROVIDERS', $recordModel->getModule()->getAllProviders());
		$viewer->view('Edit.tpl', $qualifiedModuleName);
		parent::postProcess($request);
	}
}

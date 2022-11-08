<?php

/**
 * Automatic assignment edit view.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MailServers_Edit_View extends Settings_Vtiger_Index_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		if ($request->isEmpty('record')) {
			$recordModel = Settings_MailServers_Record_Model::getCleanInstance();
		} else {
			$recordModel = Settings_MailServers_Record_Model::getInstanceById($request->getInteger('record'));
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('STRUCTURE', $recordModel->getModule()->getEditViewStructure($recordModel));
		$viewer->assign('RECORD_ID', $recordModel->getId());
		$viewer->view('Edit.tpl', $qualifiedModuleName);
	}
}

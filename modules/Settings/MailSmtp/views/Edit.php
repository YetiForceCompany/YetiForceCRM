<?php

/**
 * Edit view class for MailSmtp.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_MailSmtp_Edit_View extends Settings_Vtiger_Index_View
{
	/**
	 * Function proccess.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$record = !$request->isEmpty('record') ? $request->getInteger('record') : '';
		if ($record) {
			$recordModel = Settings_MailSmtp_Record_Model::getInstanceById($record);
		} else {
			$recordModel = Settings_MailSmtp_Record_Model::getCleanInstance();
		}
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('QUALIFIED_MODULE', $moduleName);
		$viewer->view('Edit.tpl', $moduleName);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return array - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Settings.' . $request->getModule() . '.resources.Edit',
		]));
	}
}

<?php

/**
 * Companies detail view class
 * @package YetiForce.Settings.View
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Companies_Detail_View extends Settings_Vtiger_Index_View
{

	/**
	 * Process
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$record = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);
		$recordModel = Settings_Companies_Record_Model::getInstance($record);

		$viewer = $this->getViewer($request);
		$viewer->assign('COMPANY_COLUMNS', Settings_Companies_Module_Model::getColumnNames());
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('DetailView.tpl', $qualifiedModuleName);
	}
}

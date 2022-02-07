<?php

/**
 * Companies detail view class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Companies_Detail_View extends Settings_Vtiger_Index_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$record = $request->getInteger('record');
		$qualifiedModuleName = $request->getModule(false);
		$recordModel = Settings_Companies_Record_Model::getInstance($record);
		if (null === Settings_Companies_ListView_Model::$recordsCount) {
			Settings_Companies_ListView_Model::$recordsCount = (new \App\Db\Query())->from('s_#__companies')->count();
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('REMOVE_BTN', Settings_Companies_ListView_Model::$recordsCount > 1);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('DetailView.tpl', $qualifiedModuleName);
	}
}

<?php
/**
 * Settings SLAPolicy Edit View class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_SLAPolicy_Edit_View extends Settings_Roles_Index_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$record = $request->getByType('record', 'Alnum');
		$recordModel = Settings_SLAPolicy_Record_Model::getCleanInstance();
		if (!empty($record)) {
			$recordModel = Settings_SLAPolicy_Record_Model::getInstanceById($record);
		}
		$viewer->assign('ADVANCE_CRITERIA', null);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('EditView.tpl', $qualifiedModuleName);
	}
}

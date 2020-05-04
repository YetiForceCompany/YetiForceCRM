<?php

/**
 * Settings ApiAddress Configuration view class.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Settings_MeetingServices_Edit_View class.
 */
class Settings_MeetingServices_Edit_View extends \App\Controller\ModalSettings
{
	/**
	 * Show modal footer.
	 *
	 * @var bool
	 */
	public $showFooter = false;

	public function process(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$record = !$request->isEmpty('record') ? $request->getInteger('record') : '';
		if ($record) {
			$recordModel = Settings_MeetingServices_Record_Model::getInstanceById($record);
		} else {
			$recordModel = Settings_MeetingServices_Record_Model::getCleanInstance();
		}
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE_NAME', $request->getModule());
		$viewer->assign('QUALIFIED_MODULE', $moduleName);
		$viewer->view('Edit.tpl', $moduleName);
	}
}

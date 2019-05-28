<?php
/**
 * Settings BusinessHours Edit View class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_BusinessHours_Edit_View extends Settings_Vtiger_Index_View
{
	/**
	 * {@inheritdoc}
	 */
	public function getBreadcrumbTitle(App\Request $request)
	{
		$moduleName = $request->getModule();
		return \App\Language::translate('LBL_BUSINESS_HOURS', $moduleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$this->initialize($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->view('EditView.tpl', $qualifiedModuleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function initialize(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$record = $request->getInteger('record');
		if (!empty($record)) {
			$recordModel = Settings_BusinessHours_Record_Model::getInstanceById($record);
			$viewer->assign('MODE', 'edit');
		} else {
			$recordModel = new Settings_BusinessHours_Record_Model();
			$viewer->assign('MODE', '');
		}
		$allDays = \App\Fields\Picklist::getValues('dayoftheweek');
		$viewer->assign('ALL_DAYS', $allDays);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', $moduleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Settings.Vtiger.resources.Edit',
			'modules.Settings.' . $request->getModule() . '.resources.Edit',
		]));
	}
}

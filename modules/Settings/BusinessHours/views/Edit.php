<?php
/**
 * Settings BusinessHours Edit View class.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_BusinessHours_Edit_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function getBreadcrumbTitle(App\Request $request)
	{
		return \App\Language::translate('LBL_BUSINESS_HOURS', $request->getModule());
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$this->initialize($request);
		$this->getViewer($request)->view('EditView.tpl', $request->getModule(false));
	}

	/** {@inheritdoc} */
	public function initialize(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$recordId = null;
		if ($request->has('record') && !$request->isEmpty('record', true)) {
			$recordId = $request->getInteger('record');
			$recordModel = Settings_BusinessHours_Record_Model::getInstanceById($recordId);
			$viewer->assign('MODE', 'edit');
		} else {
			$recordModel = Settings_BusinessHours_Record_Model::getCleanInstance();
			$viewer->assign('MODE', '');
		}
		$viewer->assign('DAYS_OF_THE_WEEK', \App\Fields\Date::getShortDaysOfWeek());
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('MODULE', $request->getModule());
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Settings.' . $request->getModule() . '.resources.Edit',
		]));
	}
}

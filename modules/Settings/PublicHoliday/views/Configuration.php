<?php

/**
 * Settings PublicHoliday configuration view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_PublicHoliday_Configuration_View extends Settings_Vtiger_Index_View
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('list');
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		if ($mode = $request->getMode()) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		$moduleModel = Settings_PublicHoliday_Module_Model::getInstance();
		$viewer = $this->getViewer($request);
		$viewer->assign('YEAR', date('Y'));
		$viewer->assign('DATE', '');
		$viewer->assign('HOLIDAYS', $moduleModel->getHolidaysByRange([]));
		$viewer->view('Configuration.tpl', $request->getModule(false));
	}

	/**
	 * Returns view for holiday item list by date filtering.
	 *
	 * @param \App\Request $request
	 */
	public function list(App\Request $request): void
	{
		$viewer = $this->getViewer($request);
		$moduleModel = Settings_PublicHoliday_Module_Model::getInstance();
		$date = !$request->isEmpty('date', true) ? $request->getDateRange('date') : [];
		$viewer->assign('DATE', $date ? implode(',', App\Fields\Date::formatRangeToDisplay($date)) : '');
		$viewer->assign('HOLIDAYS', $moduleModel->getHolidaysByRange($date));
		$viewer->view('ConfigurationItems.tpl', $request->getModule(false));
	}

	/** {@inheritdoc} */
	public function getHeaderCss(App\Request $request)
	{
		$view = $request->get('view', App\Purifier::STANDARD);
		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles([
			"modules.Settings.{$request->getModule()}.{$view}",
		]));
	}
}

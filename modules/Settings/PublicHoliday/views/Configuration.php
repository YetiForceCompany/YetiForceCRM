<?php

/**
 * Settings PublicHoliday configuration view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_PublicHoliday_Configuration_View extends Settings_Vtiger_Index_View
{
	/**
	 * Process
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleModel = Settings_PublicHoliday_Module_Model::getInstance();
		$viewer = $this->getViewer($request);
		$viewer->assign('YEAR', date('Y'));
		$viewer->assign('DATE', '');
		$viewer->assign('HOLIDAYS', $moduleModel->getHolidaysByRange($range));
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
		$viewer->view('Configuration.tpl', $request->getModule(false));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderCss(App\Request $request)
	{
		$viewCssPath = [
			'modules',
			'Settings',
			$request->getModule(),
			!$request->isEmpty('view') ? $request->get('view', 'Text') : 'Configuration',
		];
		$viewCss = $this->checkAndConvertCssStyles([
			implode('.', $viewCssPath),
		]);
		$cssArray = array_merge($viewCss, parent::getHeaderCss($request));
		return $cssArray;
	}
}

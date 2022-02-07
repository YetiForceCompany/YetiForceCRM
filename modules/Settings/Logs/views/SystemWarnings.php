<?php
/**
 * Logs index view class.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Settings_Logs_SystemWarnings_View extends Settings_Vtiger_Index_View
{
	use \App\Controller\ExposeMethod;

	/**
	 * Construct.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('systemWarnings');
		$this->exposeMethod('getWarningsList');
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
		} else {
			$this->getViewer($request)->view('SettingsIndexHeader.tpl', $request->getModule(false));
		}
	}

	/**
	 * Displays warnings system.
	 *
	 * @param \App\Request $request
	 */
	public function systemWarnings(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$folders = array_values(\App\SystemWarnings::getFolders());
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->assign('FOLDERS', \App\Json::encode($folders));
		$viewer->view('SystemWarnings.tpl', $qualifiedModuleName);
	}

	/**
	 * Displays a list of system warnings.
	 *
	 * @param \App\Request $request
	 */
	public function getWarningsList(App\Request $request)
	{
		$folder = $request->getArray('folder', 'Text');
		$active = $request->getBoolean('active');
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$list = \App\SystemWarnings::getWarnings($folder, $active);
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->assign('WARNINGS_LIST', $list);
		$viewer->view('SystemWarningsList.tpl', $qualifiedModuleName);
	}
}

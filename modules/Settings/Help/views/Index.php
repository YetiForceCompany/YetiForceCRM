<?php
/**
 * Help index view class.
 *
 * @package Settings.View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Settings_Help_Index_View extends Settings_Vtiger_Index_View
{
	use \App\Controller\ExposeMethod;

	/**
	 * Construct.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('github');
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
		} else {
			$this->getViewer($request)->view('SettingsIndexHeader.tpl', $request->getModule(false));
		}
	}

	/**
	 * Index.
	 *
	 * @param \App\Request $request
	 */
	public function index(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}

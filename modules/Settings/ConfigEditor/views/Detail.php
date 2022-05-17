<?php
/**
 * Config editor detail view file.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
/**
 * Config editor detail view class.
 */
class Settings_ConfigEditor_Detail_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_CONFIG_EDITOR';

	/** @var array config names */
	protected $configNames = ['Relation', 'Performance'];

	/**
	 * Get config names.
	 *
	 * @return array
	 */
	public function getConfigNames(): array
	{
		return $this->configNames;
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$qualifiedName = $request->getModule(false);
		$activeTab = 'Main';
		if ($request->has('tab')) {
			$activeTab = $request->getByType('tab', \App\Purifier::STANDARD);
		}
		$moduleModel = Settings_ConfigEditor_Module_Model::getInstance()->init('Main');

		$viewer = $this->getViewer($request);
		$viewer->assign('MODEL', $moduleModel);
		$viewer->assign('ACTIVE_TAB', $activeTab);
		$viewer->assign('CONFIG_NAMES', $this->getConfigNames());
		$viewer->view('Detail.tpl', $qualifiedName);
	}
}

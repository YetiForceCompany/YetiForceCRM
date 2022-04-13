<?php

/**
 * WebserviceUsers List View Class.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_WebserviceUsers_List_View extends Settings_Vtiger_List_View
{
	/**
	 * Initiate data values for listview.
	 *
	 * @param \App\Request  $request
	 * @param Vtiger_Viewer $viewer
	 */
	public function initializeListViewContents(App\Request $request, Vtiger_Viewer $viewer)
	{
		$qualifiedModuleName = $request->getModule(false);
		if (!$request->has('typeApi')) {
			$request->set('typeApi', current(\Api\Core\Containers::$list));
		}
		$typeApi = $request->getByType('typeApi', \App\Purifier::STANDARD);
		$this->listViewModel = Settings_Vtiger_ListView_Model::getInstance($qualifiedModuleName);
		$this->listViewModel->getModule()->typeApi = $typeApi;
		parent::initializeListViewContents($request, $viewer);
		$viewer->assign('TYPE_API', $typeApi);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $request->getModule());
		$viewer->assign('PARENT_MODULE', $request->getByType('parent', 'Standard'));
		parent::process($request);
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'libraries.clipboard.dist.clipboard',
		]));
	}
}

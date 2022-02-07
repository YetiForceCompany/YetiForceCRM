<?php

/**
 * OSSEmployees detail view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSEmployees_Detail_View extends Vtiger_Detail_View
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showRelatedRecords');
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();
		//Added to remove the module specific js, as they depend on inventory files
		$moduleEditFile = 'modules.' . $moduleName . '.resources.Edit';
		$moduleDetailFile = 'modules.' . $moduleName . '.resources.Detail';
		unset($headerScriptInstances[$moduleEditFile], $headerScriptInstances[$moduleDetailFile]);
		$jsFileNames = [
			"modules.$moduleName.resources.Detail",
		];
		$jsFileNames[] = $moduleEditFile;
		return array_merge($headerScriptInstances, $this->checkAndConvertJsScripts($jsFileNames));
	}
}

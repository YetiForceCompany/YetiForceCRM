<?php

/**
 * Services TreeView View Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Services_TreeRecords_View extends Products_TreeRecords_View
{
	public function getFooterScripts(App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);

		$moduleName = $request->getModule();
		$modulePopUpFile = 'modules.' . $moduleName . '.resources.Edit';
		unset($headerScriptInstances[$modulePopUpFile]);

		return array_merge($headerScriptInstances, $this->checkAndConvertJsScripts([
			'modules.Products.resources.Edit', $modulePopUpFile
		]));
	}
}

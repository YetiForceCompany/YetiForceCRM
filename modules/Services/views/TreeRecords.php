<?php

/**
 * Services TreeView View Class
 * @package YetiForce.TreeView
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Services_TreeRecords_View extends Products_TreeRecords_View
{

	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);

		$moduleName = $request->getModule();
		$modulePopUpFile = 'modules.' . $moduleName . '.resources.Edit';
		unset($headerScriptInstances[$modulePopUpFile]);

		$jsFileNames = [
			'modules.Products.resources.Edit',
		];
		$jsFileNames[] = $modulePopUpFile;
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}

<?php

/**
 * EditFieldByModal View Class for OSSSoldServices
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class OSSSoldServices_EditFieldByModal_View extends Assets_EditFieldByModal_View
{

	public function getModalScripts(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$viewName = $request->get('view');

		$scripts = [
			"modules.Vtiger.resources.$viewName",
			"modules.Assets.resources.$viewName",
			"modules.$moduleName.resources.$viewName"
		];

		$scriptInstances = $this->checkAndConvertJsScripts($scripts);
		return $scriptInstances;
	}
}

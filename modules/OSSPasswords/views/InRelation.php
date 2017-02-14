<?php

//The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html

class OSSPasswords_InRelation_View extends Vtiger_RelatedList_View
{

	public function getScripts(Vtiger_Request $request)
	{
		$jsFileNames = [
			'libraries.jquery.clipboardjs.clipboard',
			'modules.OSSPasswords.resources.showRelatedModulePass',
		];
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $jsScriptInstances;
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('RELATED_SCRIPTS', $this->getScripts($request));
		return parent::process($request);
	}
}

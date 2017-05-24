<?php

/**
 * OSSPasswords InRelation view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSPasswords_InRelation_View extends Vtiger_RelatedList_View
{

	public function getScripts(\App\Request $request)
	{
		$jsFileNames = [
			'libraries.jquery.clipboardjs.clipboard',
			'modules.OSSPasswords.resources.showRelatedModulePass',
		];
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $jsScriptInstances;
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('RELATED_SCRIPTS', $this->getScripts($request));
		return parent::process($request);
	}
}

<?php

/**
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSPasswords_InRelation_View extends Vtiger_RelatedList_View
{
	public function getScripts(\App\Request $request)
	{
		return $this->checkAndConvertJsScripts([
			'libraries.clipboard.dist.clipboard',
			'modules.OSSPasswords.resources.showRelatedModulePass',
		]);
	}

	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('RELATED_SCRIPTS', $this->getScripts($request));

		return parent::process($request);
	}
}

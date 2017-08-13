<?php

class EmailTemplates_Edit_View extends Vtiger_Edit_View
{

	/**
	 * Function to get the list of Script models to be included
	 * @param \App\Request $request
	 * @return Vtiger_JsScript_Model[]
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$parentScript = parent::getFooterScripts($request);
		$fileNames = [
			'libraries.jquery.clipboardjs.clipboard',
		];
		$scriptInstances = $this->checkAndConvertJsScripts($fileNames);
		return array_merge($parentScript, $scriptInstances);
	}
}

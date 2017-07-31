<?php

/**
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class OSSPasswords_Detail_View extends Vtiger_Detail_View
{

	protected $record = false;

	public function getFooterScripts(\App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$jsFileNames = array(
			'modules.OSSPasswords.resources.gen_pass',
			'libraries.jquery.clipboardjs.clipboard',
			'modules.OSSPasswords.resources.zClipDetailView'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($jsScriptInstances, $headerScriptInstances);
		return $headerScriptInstances;
	}

	/**
	 * Function to get Ajax is enabled or not
	 * @param Vtiger_Record_Model record model
	 * @return <boolean> true/false
	 */
	public function isAjaxEnabled($recordModel)
	{
		return false;
	}
}

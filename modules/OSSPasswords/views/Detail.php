<?php
/* OpenSaaS 
* Rules: http://opensaas.pl/rules.html
*/
class OSSPasswords_Detail_View extends Vtiger_Detail_View {
	protected $record = false;

	public function getFooterScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
            'modules.OSSPasswords.resources.gen_pass',
            'libraries.jquery.ZeroClipboard.ZeroClipboard',
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
	function isAjaxEnabled($recordModel) {
		return false;
	}
}

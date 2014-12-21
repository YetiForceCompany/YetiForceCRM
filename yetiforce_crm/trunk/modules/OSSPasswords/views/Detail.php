<?php
/* OpenSaaS 
* Rules: http://opensaas.pl/rules.html
*/
class OSSPasswords_Detail_View extends Vtiger_Detail_View {
	protected $record = false;

	public function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
            'layouts.vlayout.modules.OSSPasswords.resources.gen_pass',
            'layouts.vlayout.modules.OSSPasswords.resources.ZeroClipboard',
            'layouts.vlayout.modules.OSSPasswords.resources.zClipDetailView'
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
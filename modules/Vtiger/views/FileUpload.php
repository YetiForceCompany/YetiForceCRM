<?php
/**
 * FileUpload View Class
 * @package YetiForce.ModalView
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * FileUpload view class
 */
class Vtiger_FileUpload_View extends Vtiger_BasicModal_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$this->preProcess($request);
		$viewer->assign('INPUT_NAME', $request->get('inputName'));
		$viewer->assign('FILE_TYPE', $request->get('fileType'));
		$viewer->view('FileUpload.tpl', $moduleName);
		$this->postProcess($request);
	}

	public function getModalScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getModalScripts($request);
		$scripts = [
			'libraries.jquery.multiplefileupload.jquery_MultiFile'
		];
		$scriptInstances = $this->checkAndConvertJsScripts($scripts);
		return array_merge($scriptInstances, $headerScriptInstances);
	}
}

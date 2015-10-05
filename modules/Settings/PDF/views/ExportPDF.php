<?php

/**
 * Export PDF Modal View Class for PDF Settings
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_PDF_ExportPDF_View extends Vtiger_BasicModal_View
{
	public function checkPermission(Vtiger_Request $request)
	{
		return true;
//		$moduleName = $request->getModule();
//		if (!Users_Privileges_Model::isPermitted($moduleName, $actionName)) {
//			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
//		}
	}
	function process(Vtiger_Request $request)
	{
		$this->preProcess($request);
		$moduleName = $request->getModule(false);
		$viewer = $this->getViewer($request);

		$db = PearDatabase::getInstance();
		
		$query = 'SELECT `pdfid`, `module_name`, `primary_name` FROM `a_yf_pdf`;';
		$result = $db->pquery($query, []);
		$templates = [];
		$i = 0;
		while($row = $db->fetchByAssoc($result)) {
			$templates[$i]['id'] = $row['pdfid'];
			$templates[$i]['module_name'] = $row['module_name'];
			$templates[$i]['primary_name'] = $row['primary_name'];
		}
		$viewer->assign('TEMPLATES', $templates);
		$exportValues = "&record={$request->get('record')}&frommodule={$request->get('frommodule')}";
		$viewer->assign('EXPORT_VARS', $exportValues);
		$viewer->assign('PDF_MODULE', $moduleName);
		$viewer->view('ExportPDF.tpl', $moduleName);
		$this->postProcess($request);
	}
}

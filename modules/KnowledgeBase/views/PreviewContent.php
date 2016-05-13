<?php

/**
 * @package YetiForce.Views
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class KnowledgeBase_PreviewContent_View extends Vtiger_Index_View
{

	public function process(Vtiger_Request $request, $display = true)
	{
		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		// Exctracts type from record field 'views'
		$type = str_replace('PLL_', '', $recordModel->get('knowledgebase_view'));
		// Changes views type to template name 
		$template = ucfirst(strtolower($type)) . 'View.tpl';

		if ($type === 'PRESENTATION') {
			$content = explode('<div style="page-break-after:always;"><span style="display:none;"> </span></div>', $recordModel->get('content'));
		} else {
			$content = $recordModel->get('content');
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('IS_POPUP', false);
		$viewer->assign('TEMPLATE', $template);
		$viewer->assign('CONTENT', $content);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('MODULE_NAME', $moduleName);
		if ($display) {
			$viewer->view('ContentsView.tpl', $moduleName);
		}
	}
}

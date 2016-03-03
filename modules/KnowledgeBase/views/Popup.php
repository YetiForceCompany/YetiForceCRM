<?php

/**
 * Popup view for KnowledgeBase module
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 */
class KnowledgeBase_Popup_View extends Vtiger_Popup_View
{
	function process (Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $this->getModule($request);
		$recordModel = Vtiger_Record_Model::getInstanceById($request->get('record'));
		// Exctracts type from record field 'views'
		$type = str_replace('PLL_', '', $recordModel->get('knowledgebase_view'));
		// Changes views type to template name 
		$template = ucfirst(strtolower($type)) . 'View.tpl';
		if ($type === 'PRESENTATION') {
			$content = explode('<div><span style="display:none;"> </span></div>', $recordModel->get('content'));
		} else {
			$content = $recordModel->get('content');
		}
		$this->initializeListViewContents($request, $viewer);
		$viewer->assign('POPUP', true);
		$viewer->assign('TEMPLATE', $template);
		$viewer->assign('CONTENT', $content);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->view($template, $moduleName);
	}
}

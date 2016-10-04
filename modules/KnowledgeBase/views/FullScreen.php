<?php

/**
 * Popup view for KnowledgeBase module
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 */
class KnowledgeBase_FullScreen_View extends Vtiger_Popup_View
{

	public function process(Vtiger_Request $request)
	{
		$previewView = new KnowledgeBase_PreviewContent_View();
		$previewView->process($request, false);
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$recordModel = Vtiger_Record_Model::getInstanceById($request->get('record'));
		$type = str_replace('PLL_', '', $recordModel->get('knowledgebase_view'));
		$template = ucfirst(strtolower($type)) . 'View.tpl';
		$viewer->assign('IS_POPUP', true);
		$viewer->view($template, $moduleName);
	}
}

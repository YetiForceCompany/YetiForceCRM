<?php
/**
 * Detail
 * @package YetiForce.Models
 * @license licenses/License.html
 * @author Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 */
class KnowledgeBase_Detail_View extends Vtiger_Detail_View
{
	function showModuleSummaryView($request)
	{
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		if (!$this->record) {
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		
		$recordModel = $this->record->getRecord();
		$columnFields = $recordModel->entity->column_fields;
		// Exctracts type from record field 'views'
		$type = str_replace('PLL_', '', $columnFields['knowledgebase_view']);
		// Changes views type to template name 
		$template = ucfirst(strtolower($type)) . 'View.tpl';
		
		if ($type === 'PRESENTATION') {
			$columnFields['content'] = explode('<div><span> </span></div>', $columnFields['content']);
		}
		
		$viewer = $this->getViewer($request);
		$viewer->assign('TEMPLATE', $template);
		$viewer->assign('CONTENT', $columnFields['content']);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('MODULE_NAME', $moduleName);

		return $viewer->view('ContentsView.tpl', $moduleName, true);
	}
}

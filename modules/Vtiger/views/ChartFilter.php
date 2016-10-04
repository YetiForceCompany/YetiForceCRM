<?php

/**
 * View to create chart with a filter
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Vtiger_ChartFilter_View extends Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIZARD_STEP', $request->get('step'));
		switch ($request->get('step')) {
			case 'step1':
				$modules = vtlib\Functions::getAllModules(true, false, 0);
				$chartTypes = [
					'Pie' => 'LBL_PIE_CHART',
					'Barchat' => 'LBL_VERTICAL_BAR_CHART',
					'Horizontal' => 'LBL_HORIZONTAL_BAR_CHART',
					'Line' => 'LBL_LINE_CHART',
					'Funnel' => 'LBL_FUNNEL_CHART',
				];
				$viewer->assign('CHART_TYPES', $chartTypes);
				//Since comments is not treated as seperate module 
				unset($modules['ModComments']);
				$viewer->assign('MODULES', $modules);
				break;
			case 'step2':
				$selectedModule = $request->get('selectedModule');
				$filters = CustomView_Record_Model::getAllByGroup($selectedModule);
				$viewer->assign('ALLFILTERS', $filters);
				break;
			case 'step3':
				$selectedModuleName = $request->get('selectedModule');
				$selectedModuleModel = Vtiger_Module_Model::getInstance($selectedModuleName);
				$viewer->assign('MODULE_FILEDS', $selectedModuleModel->getFields());
				$viewer->assign('SELECTED_MODULE', $selectedModuleName);
				break;
		}
		$viewer->view('dashboards/ChartFilter.tpl', $moduleName);
	}
}

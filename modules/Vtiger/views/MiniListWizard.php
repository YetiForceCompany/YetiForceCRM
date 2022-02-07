<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Vtiger_MiniListWizard_View extends Vtiger_Index_View
{
	/** @var \Vtiger_Widget_Model Widget Model */
	public $widgetModel;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		if (!$request->isEmpty('templateId', true) && \App\User::getCurrentUserModel()->isAdmin()) {
			$this->widgetModel = \Vtiger_Widget_Model::getInstanceWithTemplateId($request->getInteger('templateId'));
		} elseif (!$request->isEmpty('linkId')) {
			$linkData = \vtlib\Link::getLinkData($request->getInteger('linkId'));
			$this->widgetModel = \Vtiger_Widget_Model::getInstanceFromValues($linkData);
		} else {
			$this->widgetModel = new Vtiger_MiniListModel_Dashboard();
		}
	}

	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIZARD_STEP', $request->getByType('step', 2));
		$viewer->assign('WIDGET_MODEL', $this->widgetModel);
		$viewer->assign('WIDGET_ID', $this->widgetModel->getId());

		switch ($request->getByType('step', 2)) {
			case 'step1':
				$modules = vtlib\Functions::getAllModules(true, false, 0);
				//Since comments is not treated as seperate module
				unset($modules['ModComments']);
				$viewer->assign('MODULES', $modules);
				break;
			case 'step2':
				$selectedModule = $request->getByType('selectedModule', 2);
				if (!\App\Privilege::isPermitted($selectedModule)) {
					throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
				}
				$filters = CustomView_Record_Model::getAllByGroup($selectedModule);
				$viewer->assign('ALLFILTERS', $filters);
				$viewer->assign('SELECTED_MODULE', $selectedModule);
				break;
			case 'step3':
				$selectedModule = $request->getByType('selectedModule', 2);
				if (!\App\Privilege::isPermitted($selectedModule)) {
					throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
				}
				$queryGenerator = new \App\QueryGenerator($selectedModule);
				$queryGenerator->initForCustomViewById($request->getInteger('filterid'));
				$viewer->assign('FIELDS_BY_BLOCK', $queryGenerator->getModuleModel()->getFieldsByBlocks());
				$viewer->assign('LIST_VIEW_FIELDS', $queryGenerator->getListViewFields());
				$viewer->assign('QUERY_GENERATOR', $queryGenerator);
				$viewer->assign('SELECTED_MODULE', $selectedModule);
				break;
			default:
				break;
		}
		$viewer->view('dashboards/MiniListWizard.tpl', $moduleName);
	}
}

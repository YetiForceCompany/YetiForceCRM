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

class Settings_Workflows_CreateEntity_View extends Settings_Vtiger_Index_View
{
	/**
	 * Main process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$workflowModel = Settings_Workflows_Record_Model::getInstance($request->getInteger('for_workflow'));
		$referenceFieldName = '';
		$workflowModuleModel = $workflowModel->getModule();
		$viewer->assign('MAPPING_PANEL', $request->get('mappingPanel'));
		$viewer->assign('WORKFLOW_MODEL', $workflowModel);
		if ($request->has('relatedModule')) {
			$relatedModule = $request->getByType('relatedModule', 2);
			$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
			if ($fieldModel = current($relatedModuleModel->getReferenceFieldsForModule($workflowModel->getModule()->getName()))) {
				$referenceFieldName = $fieldModel->getName();
			}
			$viewer->assign('RELATED_MODULE_MODEL', $relatedModuleModel);
		}
		$viewer->assign('REFERENCE_FIELD_NAME', $referenceFieldName);
		$viewer->assign('FIELD_EXPRESSIONS', Settings_Workflows_Module_Model::getExpressions());
		$viewer->assign('MODULE_MODEL', $workflowModuleModel);
		$viewer->assign('SOURCE_MODULE', $workflowModuleModel->getName());
		$viewer->assign('RELATED_MODULE_MODEL_NAME', '');
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('CreateEntity.tpl', $qualifiedModuleName);
	}
}

<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_PickListDependency_IndexAjax_View extends Settings_PickListDependency_Edit_View
{
	use \App\Controller\ExposeMethod,
	 App\Controller\ClearProcess;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getDependencyGraph');
	}

	public function getDependencyGraph(\App\Request $request)
	{
		$qualifiedName = $request->getModule(false);
		$module = $request->getByType('sourceModule', 2);
		$sourceField = $request->getByType('sourcefield', 2);
		$targetField = $request->getByType('targetfield', 2);
		$recordModel = Settings_PickListDependency_Record_Model::getInstance($module, $sourceField, $targetField);
		$valueMapping = $recordModel->getPickListDependency();
		$nonMappedSourceValues = $recordModel->getNonMappedSourcePickListValues();

		$viewer = $this->getViewer($request);
		$viewer->assign('MAPPED_VALUES', $valueMapping);
		$viewer->assign('SOURCE_PICKLIST_VALUES', $recordModel->getSourcePickListValues());
		$viewer->assign('TARGET_PICKLIST_VALUES', $recordModel->getTargetPickListValues());
		$viewer->assign('NON_MAPPED_SOURCE_VALUES', $nonMappedSourceValues);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedName);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->view('DependencyGraph.tpl', $qualifiedName);
	}
}

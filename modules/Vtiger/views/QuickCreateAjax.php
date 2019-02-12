<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_QuickCreateAjax_View extends Vtiger_IndexAjax_View
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModuleActionPermission($request->getModule(), 'CreateView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$moduleModel = $recordModel->getModule();
		$fieldList = $moduleModel->getFields();
		foreach (array_intersect($request->getKeys(), array_keys($fieldList)) as $fieldName) {
			$fieldModel = $fieldList[$fieldName];
			if ($fieldModel->isWritable()) {
				$fieldModel->getUITypeModel()->setValueFromRequest($request, $recordModel);
			}
		}
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE);
		$recordStructure = $recordStructureInstance->getStructure();
		$mappingRelatedField = \App\ModuleHierarchy::getRelationFieldByHierarchy($moduleName);

		$fieldValues = [];
		$sourceRelatedField = $moduleModel->getValuesFromSource($request);
		foreach ($sourceRelatedField as $fieldName => &$fieldValue) {
			if (isset($recordStructure[$fieldName])) {
				$fieldvalue = $recordStructure[$fieldName]->get('fieldvalue');
				if (empty($fieldvalue)) {
					$recordStructure[$fieldName]->set('fieldvalue', $fieldValue);
				}
			} else {
				if (isset($fieldList[$fieldName])) {
					$fieldModel = $fieldList[$fieldName];
					$fieldModel->set('fieldvalue', $fieldValue);
					$fieldValues[$fieldName] = $fieldModel;
				}
			}
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', \App\Json::encode(\App\Fields\Picklist::getPicklistDependencyDatasource($moduleName)));
		$viewer->assign('QUICKCREATE_LINKS', Vtiger_QuickCreateView_Model::getInstance($moduleName)->getLinks([]));
		$viewer->assign('MAPPING_RELATED_FIELD', \App\Json::encode($mappingRelatedField));
		$viewer->assign('SOURCE_RELATED_FIELD', $fieldValues);
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SINGLE_MODULE', 'SINGLE_' . $moduleName);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('VIEW', $request->getByType('view', 1));
		$viewer->assign('MODE', 'edit');
		$viewer->assign('SCRIPTS', $this->getFooterScripts($request));
		$viewer->assign('MAX_UPLOAD_LIMIT_MB', Vtiger_Util_Helper::getMaxUploadSize());
		$viewer->assign('MAX_UPLOAD_LIMIT', \AppConfig::main('upload_maxsize'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcessAjax(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		echo $viewer->view('QuickCreate.tpl', $request->getModule(), true);
		parent::postProcessAjax($request);
	}

	public function getFooterScripts(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$jsFileNames = [
			"modules.$moduleName.resources.Edit",
			"modules.$moduleName.resources.QuickCreate",
		];
		return $this->checkAndConvertJsScripts($jsFileNames);
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}

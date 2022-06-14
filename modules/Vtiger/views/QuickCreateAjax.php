<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
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
	public function checkPermission(App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModuleActionPermission($request->getModule(), 'CreateView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * @var Vtiger_Record_Model Record model instance.
	 */
	public $recordModel;
	/**
	 * @var Vtiger_Field_Model[] Field instances.
	 */
	public $fields;
	/**
	 * @var Vtiger_Field_Model[] Field instances.
	 */
	public $recordStructure;
	/**
	 * @var array Hidden inputs.
	 */
	public $hiddenInput = [];
	/**
	 * @var string From view name.
	 */
	public $fromView = 'QuickCreate';

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$this->recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$moduleModel = $this->recordModel->getModule();
		$this->fields = $moduleModel->getFields();
		$this->loadFieldValuesFromRequest($request);
		$viewer = $this->getViewer($request);
		$layout = $moduleModel->getLayoutTypeForQuickCreate();
		$viewLinks = Vtiger_QuickCreateView_Model::getInstance($moduleName)->getLinks([]);

		$eventHandler = new App\EventHandler();
		$eventHandler->setRecordModel($this->recordModel);
		$eventHandler->setModuleName($moduleName);
		$eventHandler->setParams([
			'mode' => 'QuickCreate',
			'layout' => $layout,
			'viewLinks' => $viewLinks,
			'viewInstance' => $this,
		]);
		$eventHandler->trigger('EditViewBefore');
		['layout' => $layout, 'viewLinks' => $viewLinks] = $eventHandler->getParams();

		$recordStructureInstance = $this->getRecordStructure();
		$this->recordStructure = $recordStructureInstance->getStructure();
		$fieldValues = $this->loadFieldValuesFromSource($request);
		if ('blocks' === $layout) {
			$blockModels = $moduleModel->getBlocks();
			$blockRecordStructure = $blockIdFieldMap = [];
			foreach ($this->recordStructure as $fieldModel) {
				$blockIdFieldMap[$fieldModel->getBlockId()][$fieldModel->getName()] = $fieldModel;
				$blockRecordStructure[$fieldModel->block->label][$fieldModel->name] = $fieldModel;
			}
			foreach ($blockModels as $blockModel) {
				if (isset($blockIdFieldMap[$blockModel->get('id')])) {
					$blockModel->setFields($blockIdFieldMap[$blockModel->get('id')]);
				}
			}
			$viewer->assign('RECORD_STRUCTURE', $blockRecordStructure);
			$viewer->assign('BLOCK_LIST', $blockModels);
		} else {
			$viewer->assign('RECORD_STRUCTURE', $this->recordStructure);
		}
		$isRelationOperation = $request->getBoolean('relationOperation');
		$viewer->assign('IS_RELATION_OPERATION', $isRelationOperation);
		if ($isRelationOperation) {
			$viewer->assign('SOURCE_MODULE', $request->getByType('sourceModule', \App\Purifier::ALNUM));
			$viewer->assign('SOURCE_RECORD', $request->getInteger('sourceRecord'));
		}
		$viewer->assign('LAYOUT', $layout);
		$viewer->assign('ADDRESS_BLOCK_LABELS', ['LBL_ADDRESS_INFORMATION', 'LBL_ADDRESS_MAILING_INFORMATION', 'LBL_ADDRESS_DELIVERY_INFORMATION', 'LBL_ADDRESS_BILLING', 'LBL_ADDRESS_SHIPPING']);
		$viewer->assign('QUICKCREATE_LINKS', $viewLinks);
		$viewer->assign('MAPPING_RELATED_FIELD', \App\Json::encode(\App\ModuleHierarchy::getRelationFieldByHierarchy($moduleName)));
		$viewer->assign('LIST_FILTER_FIELDS', \App\Json::encode(\App\ModuleHierarchy::getFieldsForListFilter($moduleName)));
		$viewer->assign('SOURCE_RELATED_FIELD', $fieldValues);
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SINGLE_MODULE', 'SINGLE_' . $moduleName);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('VIEW', $request->getByType('view', 1));
		$viewer->assign('MODE', 'edit');
		$viewer->assign('RECORD', $this->recordModel);
		$viewer->assign('HIDDEN_INPUT', $this->hiddenInput);
		$viewer->assign('FROM_VIEW', $this->fromView);
		$viewer->assign('SCRIPTS', $this->getFooterScripts($request));
	}

	/**
	 * Load field values from request.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function loadFieldValuesFromRequest(App\Request $request): void
	{
		foreach (array_intersect($request->getKeys(), array_keys($this->fields)) as $fieldName) {
			$fieldModel = $this->fields[$fieldName];
			if ($fieldModel->isWritable()) {
				$fieldModel->getUITypeModel()->setValueFromRequest($request, $this->recordModel);
			}
		}
	}

	/**
	 * Load field values from source.
	 *
	 * @param App\Request $request
	 *
	 * @return Vtiger_Field_Model[] Field instances
	 */
	public function loadFieldValuesFromSource(App\Request $request): array
	{
		$fieldValues = [];
		$sourceRelatedField = $this->recordModel->getModule()->getValuesFromSource($request);
		foreach ($sourceRelatedField as $fieldName => $fieldValue) {
			if ('' === $fieldValue) {
				continue;
			}
			if (isset($this->recordStructure[$fieldName])) {
				if ($this->fields[$fieldName]->isEditable() && ('' === $this->recordStructure[$fieldName]->get('fieldvalue') || null === $this->recordStructure[$fieldName]->get('fieldvalue'))) {
					$this->recordStructure[$fieldName]->set('fieldvalue', $fieldValue);
				}
			} else {
				if (isset($this->fields[$fieldName]) && $this->fields[$fieldName]->isEditable()) {
					$fieldModel = $this->fields[$fieldName];
					$fieldModel->set('fieldvalue', $fieldValue);
					$fieldValues[$fieldName] = $fieldModel;
				}
			}
		}
		return $fieldValues;
	}

	/**
	 * Get record structure.
	 *
	 * @return Vtiger_RecordStructure_Model
	 */
	public function getRecordStructure(): Vtiger_RecordStructure_Model
	{
		return Vtiger_RecordStructure_Model::getInstanceFromRecordModel($this->recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE);
	}

	/** {@inheritdoc} */
	public function postProcessAjax(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('QuickCreate.tpl', $request->getModule());
		parent::postProcessAjax($request);
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		return $this->checkAndConvertJsScripts([
			"modules.$moduleName.resources.Edit",
			"modules.$moduleName.resources.QuickCreate",
		]);
	}

	/** {@inheritdoc} */
	public function validateRequest(App\Request $request)
	{
		$request->validateWriteAccess();
	}
}

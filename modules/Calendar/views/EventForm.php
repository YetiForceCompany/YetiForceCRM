<?php

/**
 * Calendar EventForm View class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Calendar_EventForm_View extends Vtiger_QuickCreateAjax_View
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		if ($request->has('record')) {
			$this->record = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
			if (!$this->record->isEditable()) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		} else {
			parent::checkPermission($request);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		if ($request->has('record')) {
			$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($request->getInteger('record'));
			$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE);
			$recordStructure = $recordStructureInstance->getStructure();
			$viewer->assign('QUICKCREATE_LINKS', Vtiger_QuickCreateView_Model::getInstance($moduleName)->getLinks([]));
			$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', \App\Json::encode(\App\Fields\Picklist::getPicklistDependencyDatasource($moduleName)));
			$viewer->assign('MAPPING_RELATED_FIELD', \App\Json::encode(\App\ModuleHierarchy::getRelationFieldByHierarchy($moduleName)));
			$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
			$viewer->assign('RECORD_STRUCTURE', $recordStructure);
			$viewer->assign('SOURCE_RELATED_FIELD', []);
			$viewer->assign('RECORD', $recordModel);
			$viewer->assign('MODULE', $moduleName);
			$viewer->assign('RECORD_ID', $recordModel->getId());
			$viewer->assign('QUICK_CREATE_CONTENTS', $this->getQuickCreateContents(
				$request, $recordModel
			));
			$viewer->assign('SCRIPTS', $this->getFooterScripts($request));
			$viewer->assign('VIEW', $request->getByType('view'));
		} else {
			parent::process($request);
			$viewer->assign('RECORD', null);
			$viewer->assign('RECORD_ID', '');
			$viewer->assign('MODULE', $moduleName);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcessAjax(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('Extended/EventForm.tpl', $request->getModule());
	}

	private function getQuickCreateContents($request, $requestRecordModel)
	{
		$quickCreateContents = [];
		$module = 'Calendar';
		$info = [];
		if (!empty($requestRecordModel) && $module == $requestRecordModel->getModuleName()) {
			$recordModel = $requestRecordModel;
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($module);
		}
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
		uksort($recordStructure, function ($a, $b) use ($recordStructure) {
			$types = ['string', 'datetime', 'date', 'time', 'owner', 'sharedOwner', 'picklist', 'referenceLink', 'referenceProcess', 'referenceSubProcess', 'reference'];
			if (($val1 = array_search($recordStructure[$a]->getFieldDataType(), $types)) === false) {
				$val1 = 99;
			}
			if (($val2 = array_search($recordStructure[$b]->getFieldDataType(), $types)) === false) {
				$val2 = 99;
			}
			return strcasecmp($val1, $val2);
		});
		$fieldValues = [];
		$sourceRelatedField = $moduleModel->getValuesFromSource($request);
		foreach ($sourceRelatedField as $fieldName => $fieldValue) {
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
		$info['recordStructureModel'] = $recordStructureInstance;
		$info['recordStructure'] = $recordStructure;
		$info['moduleModel'] = $moduleModel;
		$quickCreateContents[$module] = $info;
		return $quickCreateContents;
	}
}

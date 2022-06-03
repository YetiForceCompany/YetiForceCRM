<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_PickListDependency_ListView_Model extends Settings_Vtiger_ListView_Model
{
	/*
	public function getListViewHeaders()
	{
		$field = new \App\Base();
		$field->set('name', 'sourceLabel');
		$field->set('label', 'Module');
		$field->set('sort', false);

		$field1 = new \App\Base();
		$field1->set('name', 'sourcefieldlabel');
		$field1->set('label', 'LBL_SOURCE_FIELD');
		$field1->set('sort', false);

		$field2 = new \App\Base();
		$field2->set('name', 'secondFieldlabel');
		$field2->set('label', 'LBL_SECOND_FIELD');
		$field2->set('sort', false);

		return [$field, $field1, $field2];
	}
	*/

	public function getListViewEntries($pagingModel)
	{
		//$forModule = $this->get('formodule');
		$moduleModel = $this->getModule();
		$moduleName = $moduleModel->getName();
		$parentModuleName = $moduleModel->getParentName();
		$qualifiedModuleName = $moduleName;
		$pagingModel->set('page', 1);
		if (!empty($parentModuleName)) {
			$qualifiedModuleName = $parentModuleName . ':' . $qualifiedModuleName;
		}
		$recordModelClass = Vtiger_Loader::getComponentClassName('Model', 'Record', $qualifiedModuleName);
		$listQuery = $this->getBasicListQuery();

		$orderBy = $this->getForSql('orderby');
		if (!empty($orderBy)) {
			if ('DESC' === $this->getForSql('sortorder')) {
				$listQuery->orderBy([$orderBy => SORT_DESC]);
			} else {
				$listQuery->orderBy([$orderBy => SORT_ASC]);
			}
		}
		$dataReader = $listQuery->createCommand()->query();
		$listViewRecordModels = [];
		while ($row = $dataReader->read()) {
			$moduleModel = Vtiger_Module_Model::getInstance($row['tabid']);
			$sourceFieldModel = Vtiger_Field_Model::getInstance($row['source_field'], $moduleModel);
			$secondFieldModel = Vtiger_Field_Model::getInstance($row['second_field'], $moduleModel);
			if (!$sourceFieldModel || !$secondFieldModel) {
				continue;
			}
			$row['sourceFieldLabel'] = \App\Language::translate($sourceFieldModel->getFieldLabel(), $moduleName);
			$row['secondFieldLabel'] = \App\Language::translate($secondFieldModel->getFieldLabel(), $moduleName);
			$row['moduleName'] = $moduleModel->getName();
			$thirdFieldModel = $row['third_field'] ? Vtiger_Field_Model::getInstance($row['third_field'], $moduleModel) : false;
			if ($row['third_field'] && !$thirdFieldModel) {
				continue;
			}
			if ($thirdFieldModel) {
				$row['thirdFieldLabel'] = \App\Language::translate($thirdFieldModel->getFieldLabel(), $moduleName);
			}

			$record = new $recordModelClass();
			$record->setData($row);
			if (method_exists($record, 'getModule') && method_exists($record, 'setModule')) {
				$record->setModule($moduleModel);
			}
			$listViewRecordModels[$record->getId()] = $record;
		}
		$dataReader->close();

		return $listViewRecordModels;
	}

	public function aagetListViewEntries($pagingModel)
	{
		$forModule = $this->get('formodule');

		// dorzudistinct(cić trzecią wartość ale jezeli jej nie uzywam?
		// jak przeniose to do s_yf_picklist_dependency to po prostu pobiorę
		$query = (new \App\Db\Query())->select(['id', 'tabid', 'source_field', 'second_field', 'third_field'])
			->from('s_yf_picklist_dependency');
		if (!empty($forModule)) {
			$query->where(['tabid' => \App\Module::getModuleId($forModule)]);
		}
		$dataReader = $query->distinct()->createCommand()->query();
		$dependentPicklists = [];
		$recordModelClass = Vtiger_Loader::getComponentClassName('Model', 'Record', 'Settings:PickListDependency');
		while ($row = $dataReader->read()) {
			$sourceField = $row['source_field'];
			$targetField = $row['second_field'];
			$thirdField = $row['third_field'];

			$moduleModel = Vtiger_Module_Model::getInstance($row['tabid']);
			$sourceFieldModel = Vtiger_Field_Model::getInstance($sourceField, $moduleModel);
			$targetFieldModel = Vtiger_Field_Model::getInstance($targetField, $moduleModel);
			//	$thirdFieldModel = Vtiger_Field_Model::getInstance($row['thirdfield'], $moduleModel);
			if (!$sourceFieldModel || !$targetFieldModel) {
				//	continue;
			}
			$sourceFieldLabel = $sourceFieldModel->getFieldLabel();

			$targetFieldLabel = $targetFieldModel->getFieldLabel();
			$moduleName = $moduleModel->getName();
			$dependentPicklists[] = [
				'id' => $row['id'],
				'sourcefield' => $sourceField,
				'sourcefieldlabel' => \App\Language::translate($sourceFieldLabel, $moduleName),
				'targetfield' => $targetField,
				'targetfieldlabel' => \App\Language::translate($targetFieldLabel, $moduleName),
				'thirdField' => 'thirdfield',
				'module' => $moduleName,
			];
			$record = new $recordModelClass();
			//	$record->set('id', $row['id']);
			$record->setData($row);
			$record->set('sourceModule', $moduleName);
			$record->set('sourceLabel', \App\Language::translate($moduleName, $moduleName));
			$listViewRecordModels[] = $record;
		}
		$dataReader->close();

		return $listViewRecordModels;
		/*
		$dependentPicklists = Vtiger_DependencyPicklist::getDependentPicklistFields($forModule);

		var_dump($dependentPicklists);

		$noOfRecords = \count($dependentPicklists);
		$recordModelClass = Vtiger_Loader::getComponentClassName('Model', 'Record', 'Settings:PickListDependency');
		$listViewRecordModels = [];

		foreach ($dependentPicklists as $values) {
			for ($i = 0; $i < $noOfRecords; ++$i) {
				//	$record = Settings_PickListDependency_Record_Model::getInstanceById($dependentPicklists[$i]['id']);
				$record = new $recordModelClass();
				$module = $dependentPicklists[$i]['module'];
				unset($dependentPicklists[$i]['module']);
				$record->setData($dependentPicklists[$i]);
				$record->set('sourceModule', $module);
				$record->set('sourceLabel', \App\Language::translate($module, $module));
				$listViewRecordModels[] = $record;
			}
		}
		$pagingModel->calculatePageRange($noOfRecords);

		return $listViewRecordModels;
		*/
	}
}

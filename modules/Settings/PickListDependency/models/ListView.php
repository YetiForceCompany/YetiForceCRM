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

class Settings_PickListDependency_ListView_Model extends Settings_Vtiger_ListView_Model
{
	public function getListViewEntries($pagingModel)
	{
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
		if ($this->get('forModule')) {
			$listQuery->where(['tabid' => \App\Module::getModuleId($this->get('forModule'))]);
		}
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
}

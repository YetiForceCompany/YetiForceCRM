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
	/**
	 * Function to get the list view header.
	 *
	 * @return <Array> - List of Vtiger_Field_Model instances
	 */
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
		$field2->set('name', 'targetfieldlabel');
		$field2->set('label', 'LBL_TARGET_FIELD');
		$field2->set('sort', false);

		return [$field, $field1, $field2];
	}

	/**
	 * Function to get the list view entries.
	 *
	 * @param Vtiger_Paging_Model $pagingModel
	 *
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance
	 */
	public function getListViewEntries($pagingModel)
	{
		$forModule = $this->get('formodule');

		$dependentPicklists = Vtiger_DependencyPicklist::getDependentPicklistFields($forModule);

		$noOfRecords = count($dependentPicklists);
		$recordModelClass = Vtiger_Loader::getComponentClassName('Model', 'Record', 'Settings:PickListDependency');

		$listViewRecordModels = [];
		for ($i = 0; $i < $noOfRecords; ++$i) {
			$record = new $recordModelClass();
			$module = $dependentPicklists[$i]['module'];
			unset($dependentPicklists[$i]['module']);
			$record->setData($dependentPicklists[$i]);
			$record->set('sourceModule', $module);
			$record->set('sourceLabel', \App\Language::translate($module, $module));
			$listViewRecordModels[] = $record;
		}
		$pagingModel->calculatePageRange($noOfRecords);

		return $listViewRecordModels;
	}
}

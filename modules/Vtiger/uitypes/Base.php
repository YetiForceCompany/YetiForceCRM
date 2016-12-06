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

class Vtiger_Base_UIType extends Vtiger_Base_Model
{

	public function isAjaxEditable()
	{
		return true;
	}

	/**
	 * If the field is sortable in ListView
	 */
	public function isListviewSortable()
	{
		return true;
	}

	/**
	 * Function to get the Template name for the current UI Type Object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/String.tpl';
	}

	/**
	 * Function to get the DB Insert Value, for the current field type with given User Value
	 * @param mixed $value
	 * @param \Vtiger_Record_Model $recordModel
	 * @return mixed
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if ($value === '' && in_array($this->getFieldModel()->getFieldType(), ['I', 'N', 'NN'])) {
			$value = 0;
		}
		if (is_null($value)) {
			$value = '';
		}
		return $value;
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		return $value;
	}

	/**
	 * Static function to get the UIType object from Vtiger Field Model
	 * @param Vtiger_Field_Model $fieldModel
	 * @return Vtiger_Base_UIType or UIType specific object instance
	 */
	public static function getInstanceFromField($fieldModel)
	{
		$fieldDataType = $fieldModel->getFieldDataType();
		$uiTypeClassSuffix = ucfirst($fieldDataType);
		$moduleName = $fieldModel->getModuleName();
		$moduleSpecificUiTypeClassName = $moduleName . '_' . $uiTypeClassSuffix . '_UIType';
		$uiTypeClassName = 'Vtiger_' . $uiTypeClassSuffix . '_UIType';
		$fallBackClassName = 'Vtiger_Base_UIType';

		$moduleSpecificFileName = 'modules.' . $moduleName . '.uitypes.' . $uiTypeClassSuffix;
		$uiTypeClassFileName = 'modules.Vtiger.uitypes.' . $uiTypeClassSuffix;

		$moduleSpecificFilePath = Vtiger_Loader::resolveNameToPath($moduleSpecificFileName);
		$completeFilePath = Vtiger_Loader::resolveNameToPath($uiTypeClassFileName);

		if (file_exists($moduleSpecificFilePath)) {
			$instance = new $moduleSpecificUiTypeClassName();
		} else if (file_exists($completeFilePath)) {
			$instance = new $uiTypeClassName();
		} else {
			$instance = new $fallBackClassName();
		}
		$instance->set('field', $fieldModel);
		return $instance;
	}

	/**
	 * Function to get the display value in edit view
	 * @param reference record id
	 * @return link
	 */
	public function getEditViewDisplayValue($value, $record = false)
	{
		return $value;
	}

	public function getListViewDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		return \vtlib\Functions::textLength($this->getDisplayValue($value, $record, $recordInstance, $rawText), $this->get('field')->get('maxlengthtext'));
	}

	public function getReletedListViewDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		return $this->getListViewDisplayValue($value, $record, $recordInstance, $rawText);
	}

	/**
	 * Function to get the Detailview template name for the current UI Type Object
	 * @return string - Template Name
	 */
	public function getDetailViewTemplateName()
	{
		return 'uitypes/StringDetailView.tpl';
	}

	/**
	 * Function to get Display value for RelatedList
	 * @param string $value
	 * @return string
	 */
	public function getRelatedListDisplayValue($value)
	{
		return $this->getDisplayValue($value);
	}

	public function getListSearchTemplateName()
	{
		return 'uitypes/FieldSearchView.tpl';
	}

	/**
	 * Get field model instance
	 * @return Vtiger_Field_Model
	 */
	public function getFieldModel()
	{
		return $this->get('field');
	}

	public function isActiveSearchView()
	{
		return true;
	}
}

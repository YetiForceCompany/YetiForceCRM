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

class Vtiger_Base_UIType extends \App\Base
{

	/**
	 * Verify the value
	 * @var bool
	 */
	protected $validate = false;

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
		return \App\Purifier::decodeHtml($value);
	}

	/**
	 * Set value from request 
	 * @param \App\Request $request
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function setValueFromRequest(\App\Request $request, Vtiger_Record_Model $recordModel)
	{
		$fieldName = $this->get('field')->getFieldName();
		$value = $request->get($fieldName, '');
		$this->validate($value);
		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}

	/**
	 * Verification of data
	 * @param string $value
	 * @param bool $isUserFormat
	 * @return null
	 * @throws \App\Exceptions\SaveRecord
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		//var_dump(get_class($this), $value);
		if (!is_string($value)) {
			throw new \App\Exceptions\SaveRecord('ERR_INCORRECT_VALUE_WHILE_SAVING_RECORD', 406);
		}
		if (App\Utils::getTextLength($value) > 255) {
			throw new \App\Exceptions\SaveRecord('ERR_VALUE_IS_TOO_LONG', 406);
		}
		$this->validate = true;
	}

	/**
	 * Function to get the display value, for the current field type with given DB Insert Value
	 * @param mixed $value
	 * @param int $record
	 * @param type $recordModel
	 * @param Vtiger_Record_Model $rawText
	 * @return mixed
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		return \App\Purifier::encodeHtml($value);
	}

	/**
	 * Function to get the edit value in display view
	 * @param mixed $value
	 * @param Vtiger_Record_Model $recordModel
	 * @return mixed
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return \App\Purifier::encodeHtml($value);
	}

	/**
	 * Function to get the list value in display view
	 * @param mixed $value
	 * @param int $record
	 * @param Vtiger_Record_Model $recordModel
	 * @param bool $rawText
	 * @return mixed
	 */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		return \vtlib\Functions::textLength($this->getDisplayValue($value, $record, $recordModel, $rawText), $this->get('field')->get('maxlengthtext'));
	}

	/**
	 * Function to get the related list value in display view
	 * @param mixed $value
	 * @param int $record
	 * @param Vtiger_Record_Model $recordModel
	 * @param bool $rawText
	 * @return mixed
	 */
	public function getRelatedListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		return $this->getListViewDisplayValue($value, $record, $recordModel, $rawText);
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
	 * Function to get the Template name for the current UI Type Object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/String.tpl';
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
	 * Function to get the Template name for the current UI Type object
	 * @return string - Template Name
	 */
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
}

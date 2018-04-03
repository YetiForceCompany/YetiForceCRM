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
	 * Verify the value.
	 *
	 * @var bool
	 */
	protected $validate = false;

	/**
	 * Function to get the DB Insert Value, for the current field type with given User Value.
	 *
	 * @param mixed                $value
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return mixed
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if ($value === '' && in_array($this->getFieldModel()->getFieldType(), ['I', 'N', 'NN'])) {
			return 0;
		}
		if (is_null($value)) {
			return '';
		}

		return \App\Purifier::decodeHtml($value);
	}

	/**
	 * Set value from request.
	 *
	 * @param \App\Request        $request
	 * @param Vtiger_Record_Model $recordModel
	 * @param string|bool         $requestFieldName
	 */
	public function setValueFromRequest(\App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$fieldName = $this->getFieldModel()->getFieldName();
		if (!$requestFieldName) {
			$requestFieldName = $fieldName;
		}
		$value = $request->get($requestFieldName, '');
		$this->validate($value, true);
		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}

	/**
	 * Verification of data.
	 *
	 * @param string $value
	 * @param bool   $isUserFormat
	 *
	 * @throws \App\Exceptions\Security
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if ($isUserFormat) {
			$value = \App\Purifier::decodeHtml($value);
		}
		if (!is_numeric($value) && (is_string($value) && $value !== strip_tags($value))) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $value, 406);
		}
		if (App\TextParser::getTextLength($value) > 255) {
			throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getFieldName() . '||' . $value, 406);
		}
		$this->validate = true;
	}

	/**
	 * Convert value before writing to the database.
	 *
	 * @param mixed               $value
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return mixed
	 */
	public function convertToSave($value, Vtiger_Record_Model $recordModel)
	{
		return $value;
	}

	/**
	 * Function to get the display value, for the current field type with given DB Insert Value.
	 *
	 * @param mixed                    $value       Field value
	 * @param int|bool                 $record      Record Id
	 * @param Vtiger_Record_Model|bool $recordModel
	 * @param bool                     $rawText     Return text or html
	 * @param int|bool                 $length      Length of the text
	 *
	 * @return mixed
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (is_int($length)) {
			$value = \App\TextParser::textTruncate($value, $length);
		}

		return \App\Purifier::encodeHtml($value);
	}

	/**
	 * Function to get the edit value in display view.
	 *
	 * @param mixed               $value
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return mixed
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return \App\Purifier::encodeHtml($value);
	}

	/**
	 * Function to get the list value in display view.
	 *
	 * @param mixed                    $value       Field value
	 * @param int                      $record|bool Record Id
	 * @param Vtiger_Record_Model|bool $recordModel
	 * @param bool                     $rawText     Return text or html
	 *
	 * @return mixed
	 */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		return $this->getDisplayValue($value, $record, $recordModel, $rawText, $this->getFieldModel()->get('maxlengthtext'));
	}

	/**
	 * Function to get the related list value in display view.
	 *
	 * @param mixed                    $value       Field value
	 * @param int                      $record|bool Record Id
	 * @param Vtiger_Record_Model|bool $recordModel
	 * @param bool                     $rawText     Return text or html
	 *
	 * @return mixed
	 */
	public function getRelatedListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		return $this->getListViewDisplayValue($value, $record, $recordModel, $rawText);
	}

	/**
	 * Function to get Display value for RelatedList.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function getRelatedListDisplayValue($value)
	{
		return $this->getListViewDisplayValue($value);
	}

	/**
	 * Static function to get the UIType object from Vtiger Field Model.
	 *
	 * @param Vtiger_Field_Model $fieldModel
	 *
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
		} elseif (file_exists($completeFilePath)) {
			$instance = new $uiTypeClassName();
		} else {
			$instance = new $fallBackClassName();
		}
		$instance->set('field', $fieldModel);

		return $instance;
	}

	/**
	 * Function to get the Template name for the current UI Type Object.
	 *
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/Edit/Base.tpl';
	}

	/**
	 * Function to get the Detailview template name for the current UI Type Object.
	 *
	 * @return string - Template Name
	 */
	public function getDetailViewTemplateName()
	{
		return 'uitypes/Detail/Base.tpl';
	}

	/**
	 * Function to get the Template name for the current UI Type object.
	 *
	 * @return string - Template Name
	 */
	public function getListSearchTemplateName()
	{
		return 'uitypes/Search/Base.tpl';
	}

	/**
	 * Get field model instance.
	 *
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
	 * If the field is sortable in ListView.
	 */
	public function isListviewSortable()
	{
		return true;
	}
}

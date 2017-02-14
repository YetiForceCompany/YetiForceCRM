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

class Vtiger_Reference_UIType extends Vtiger_Base_UIType
{

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/Reference.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getReferenceModule($value)
	{
		$fieldModel = $this->get('field');
		$referenceModuleList = $fieldModel->getReferenceList();
		$referenceEntityType = \vtlib\Functions::getCRMRecordType($value);
		if (!empty($referenceModuleList) && in_array($referenceEntityType, $referenceModuleList)) {
			return Vtiger_Module_Model::getInstance($referenceEntityType);
		} elseif (!empty($referenceModuleList) && in_array('Users', $referenceModuleList)) {
			return Vtiger_Module_Model::getInstance('Users');
		}
		return null;
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param int $value
	 * @param int $record
	 * @param Vtiger_Record_Model $recordInstance
	 * @param bool $rawText
	 * @return string
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$referenceModule = $this->getReferenceModule($value);
		if ($referenceModule && !empty($value)) {
			$referenceModuleName = $referenceModule->get('name');
			if ($referenceModuleName === 'Users' || $referenceModuleName === 'Groups') {
				$name = \App\Fields\Owner::getLabel($value);
			} else {
				$name = \App\Record::getLabel($value);
			}
			if ($rawText || $referenceModuleName === 'Users' || ($value && !\App\Privilege::isPermitted($referenceModuleName, 'DetailView', $value))) {
				return $name;
			}
			$name = vtlib\Functions::textLength($name, vglobal('href_max_length'));
			$linkValue = "<a class='moduleColor_$referenceModuleName' href='index.php?module=$referenceModuleName&view=" . $referenceModule->getDetailViewName() . "&record=$value' title='" . App\Language::translate($referenceModuleName, $referenceModuleName) . "'>$name</a>";
			return $linkValue;
		}
		return '';
	}

	/**
	 * Function to get the Display Value in ListView, for the current field type with given DB Insert Value
	 * @param mixed $value
	 * @return string
	 */
	public function getListViewDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$referenceModule = $this->getReferenceModule($value);
		if ($referenceModule && !empty($value)) {
			$referenceModuleName = $referenceModule->get('name');
			if ($referenceModuleName === 'Users' || $referenceModuleName === 'Groups') {
				$name = \App\Fields\Owner::getLabel($value);
			} else {
				$name = \App\Record::getLabel($value);
			}
			if ($rawText || $referenceModuleName === 'Users' || ($value && !\App\Privilege::isPermitted($referenceModuleName, 'DetailView', $value))) {
				return $name;
			}
			$name = vtlib\Functions::textLength($name, $this->get('field')->get('maxlengthtext'));
			$linkValue = "<a class='moduleColor_$referenceModuleName' href='index.php?module=$referenceModuleName&view=" . $referenceModule->getDetailViewName() . "&record=$value' title='" . App\Language::translate($referenceModuleName, $referenceModuleName) . "'>$name</a>";
			return $linkValue;
		}
		return '';
	}

	/**
	 * Function to get the display value in edit view
	 * @param reference record id
	 * @return link
	 */
	public function getEditViewDisplayValue($value, $record = false)
	{
		$referenceModuleName = $this->getReferenceModule($value);
		if ($referenceModuleName === 'Users' || $referenceModuleName === 'Groups') {
			$name = \App\Fields\Owner::getLabel($value);
		} else {
			$name = \App\Record::getLabel($value);
		}
		return $name;
	}

	public function getListSearchTemplateName()
	{
		$fieldModel = $this->get('field');
		$fieldName = $fieldModel->getName();
		if ($fieldName === 'modifiedby') {
			return 'uitypes/OwnerFieldSearchView.tpl';
		}
		if (AppConfig::performance('SEARCH_REFERENCE_BY_AJAX')) {
			return 'uitypes/ReferenceSearchView.tpl';
		}
		return parent::getListSearchTemplateName();
	}

	/**
	 * Function to get the DB Insert Value, for the current field type with given User Value
	 * @param mixed $value
	 * @param \Vtiger_Record_Model $recordModel
	 * @return mixed
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if (empty($value)) {
			$value = 0;
		}
		return $value;
	}
}

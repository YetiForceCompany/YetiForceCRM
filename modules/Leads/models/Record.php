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

/**
 * Record model for module leads.
 */
class Leads_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Get converted column.
	 *
	 * @return bool
	 */
	public function getConverted(): bool
	{
		if ($this->isNew()) {
			$returnVal = false;
		} elseif (\App\Cache::has('Leads.converted', $this->getId())) {
			$returnVal = (bool) \App\Cache::get('Leads.converted', $this->getId());
		} else {
			$returnVal = (bool) (new \App\Db\Query())->select(['converted'])->from('vtiger_leaddetails')->where(['leadid' => $this->getId()])->scalar();
			\App\Cache::save('Leads.converted', $this->getId(), $returnVal);
		}
		return $returnVal;
	}

	/** {@inheritdoc} */
	public function save()
	{
		parent::save();
		if (!$this->isNew()) {
			\App\Cache::delete('Leads.converted', $this->getId());
		}
	}

	/** {@inheritdoc} */
	public function delete()
	{
		parent::delete();
		\App\Cache::delete('Leads.converted', $this->getId());
	}

	/** {@inheritdoc} */
	public function isViewable()
	{
		return parent::isViewable() && !$this->getConverted();
	}

	/** {@inheritdoc} */
	public function isPermitted(string $action)
	{
		return parent::isPermitted($action) && !$this->getConverted();
	}

	/** {@inheritdoc} */
	public function isEditable(): bool
	{
		return parent::isEditable() && !$this->getConverted();
	}

	/**
	 * Function returns the url for converting lead.
	 *
	 * @return string
	 */
	public function getConvertLeadUrl()
	{
		return 'index.php?module=' . $this->getModuleName() . '&view=ConvertLead&record=' . $this->getId();
	}

	/**
	 * Function returns Account fields for Lead Convert.
	 *
	 * @return array
	 */
	public function getAccountFieldsForLeadConvert()
	{
		$accountsFields = [];
		$moduleName = 'Accounts';

		if (!\App\Privilege::isPermitted($moduleName, 'EditView')) {
			return;
		}

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		if ($moduleModel->isActive()) {
			$fieldModels = $moduleModel->getFields();
			//Fields that need to be shown
			$complusoryFields = []; //Field List in the conversion lead
			foreach ($fieldModels as $fieldName => $fieldModel) {
				if ($fieldModel->isMandatory() && 'assigned_user_id' != $fieldName) {
					$keyIndex = array_search($fieldName, $complusoryFields);
					if (false !== $keyIndex) {
						unset($complusoryFields[$keyIndex]);
					}
					$leadMappedField = $this->getConvertLeadMappedField($fieldName, $moduleName);
					if ($leadMappedField) {
						$fieldModel->set('fieldvalue', $this->get($leadMappedField));
					}
					if ('' == $fieldModel->get('fieldvalue')) {
						$fieldModel->set('fieldvalue', $fieldModel->getDefaultFieldValue());
					}
					$accountsFields[] = $fieldModel;
				}
			}
			foreach ($complusoryFields as $complusoryField) {
				$fieldModel = Vtiger_Field_Model::getInstance($complusoryField, $moduleModel);
				if ($fieldModel->getPermissions(false)) {
					$industryFieldModel = $moduleModel->getField($complusoryField);
					$industryLeadMappedField = $this->getConvertLeadMappedField($complusoryField, $moduleName);
					if ($industryLeadMappedField) {
						$industryFieldModel->set('fieldvalue', $this->get($industryLeadMappedField));
					} else {
						$industryFieldModel->set('fieldvalue', $fieldModel->getDefaultFieldValue());
					}
					$accountsFields[] = $industryFieldModel;
				}
			}
		}
		return $accountsFields;
	}

	/**
	 * Function returns field mapped to Leads field, used in Lead Convert for settings the field values.
	 *
	 * @param string $fieldName
	 * @param string $moduleName
	 *
	 * @return string
	 */
	public function getConvertLeadMappedField($fieldName, $moduleName)
	{
		$mappingFields = $this->get('mappingFields');

		if (!$mappingFields) {
			$mappingFields = [];
			$query = (new \App\Db\Query())->from('vtiger_convertleadmapping');

			$accountInstance = Vtiger_Module_Model::getInstance('Accounts');
			$accountFieldInstances = $accountInstance->getFieldsById();

			$leadInstance = Vtiger_Module_Model::getInstance('Leads');
			$leadFieldInstances = $leadInstance->getFieldsById();

			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				if (empty($row['leadfid'])) {
					continue;
				}
				$leadFieldInstance = $leadFieldInstances[$row['leadfid']];
				if (!$leadFieldInstance) {
					continue;
				}
				$leadFieldName = $leadFieldInstance->getName();
				if ($row['accountfid'] && isset($accountFieldInstances[$row['accountfid']])) {
					$mappingFields['Accounts'][$accountFieldInstances[$row['accountfid']]->getName()] = $leadFieldName;
				}
			}
			$dataReader->close();
			$this->set('mappingFields', $mappingFields);
		}
		return $mappingFields[$moduleName][$fieldName] ?? '';
	}

	/**
	 * Function returns the fields required for Lead Convert.
	 *
	 * @return Vtiger_Field_Model[]
	 */
	public function getConvertLeadFields()
	{
		$convertFields = [];
		$accountFields = $this->getAccountFieldsForLeadConvert();
		if (!empty($accountFields)) {
			$convertFields['Accounts'] = $accountFields;
		}
		return $convertFields;
	}
}

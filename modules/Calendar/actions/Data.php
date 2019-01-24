<?php

/**
 * Class calendar data action.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Calendar_Data_Action extends Import_Data_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function transformForImport($fieldData)
	{
		$moduleModel = Vtiger_Module_Model::getInstance($this->module);
		foreach ($fieldData as $fieldName => $fieldValue) {
			$fieldInstance = $moduleModel->getFieldByName($fieldName);
			if ($fieldInstance->getFieldDataType() === 'owner') {
				$fieldData[$fieldName] = $this->transformOwner($fieldInstance, $fieldValue);
			} elseif ($fieldInstance->getFieldDataType() === 'sharedOwner') {
				$fieldData[$fieldName] = $this->transformSharedOwner($fieldInstance, $fieldValue);
			} elseif ($fieldInstance->getFieldDataType() === 'multipicklist') {
				$fieldData[$fieldName] = $this->transformMultipicklist($fieldInstance, $fieldValue);
			} elseif (in_array($fieldInstance->getFieldDataType(), Vtiger_Field_Model::$referenceTypes)) {
				$fieldData[$fieldName] = $this->transformReference($fieldInstance, $fieldValue);
			} elseif ($fieldInstance->getFieldDataType() === 'picklist') {
				$fieldData[$fieldName] = $this->transformPicklist($fieldInstance, $fieldValue);
			} elseif ($fieldInstance->getFieldDataType() === 'tree' || $fieldInstance->getFieldDataType() === 'categoryMultipicklist') {
				$fieldData[$fieldName] = $this->transformTree($fieldInstance, $fieldValue);
			} elseif ($fieldInstance->getFieldDataType() === 'datetime' && $fieldValue !== '') {
				$fieldData[$fieldName] = $this->transformDate($fieldValue);
			} elseif ($fieldInstance->getFieldDataType() === 'date' && $fieldValue !== '') {
				$fieldData[$fieldName] = $this->transformDate($fieldValue);
			}
		}
		return $fieldData;
	}
}

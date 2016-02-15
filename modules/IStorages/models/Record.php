<?php

/**
 * Record Class for IStorages
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class IStorages_Record_Model extends Vtiger_Record_Model
{

	/**
	 * Function to retieve display value for a field
	 * @param <String> $fieldName - field name for which values need to get
	 * @return <String>
	 */
	public function getDisplayValue($fieldName, $recordId = false, $rawText = false)
	{
		// This is special field / displayed only in Products module [view=Detail relatedModule=IStorages]
		if ($fieldName == 'qtyinstock') {
			return $this->get($fieldName);
		}
		return parent::getDisplayValue($fieldName, $recordId, $rawText);
	}
}

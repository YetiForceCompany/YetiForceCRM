<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Documents_Field_Model extends Vtiger_Field_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$fieldName = $this->getName();
		if ($fieldName === 'filesize' && $recordModel) {
			$downloadType = $recordModel->get('filelocationtype');
			if ($downloadType === 'I') {
				$value = vtlib\Functions::showBytes($value);
			} else {
				$value = ' --';
			}

			return $value;
		}

		return parent::getDisplayValue($value, $record, $recordModel, $rawText, $length);
	}
}

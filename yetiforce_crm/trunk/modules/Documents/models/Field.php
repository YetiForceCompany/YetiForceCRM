<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Documents_Field_Model extends Vtiger_Field_Model {

	/**
	 * Function to retieve display value for a value
	 * @param <String> $value - value which need to be converted to display value
	 * @return <String> - converted display value
	 */
	public function getDisplayValue($value, $record=false, $recordInstance = false) {
		$fieldName = $this->getName();

		if($fieldName == 'filesize' && $recordInstance) {
			$downloadType = $recordInstance->get('filelocationtype');
			if($downloadType == 'I') {
				$filesize = $value;
				if($filesize < 1024)
					$value=$filesize.' B';
				elseif($filesize > 1024 && $filesize < 1048576)
					$value=round($filesize/1024,2).' KB';
				else if($filesize > 1048576)
					$value=round($filesize/(1024*1024),2).' MB';
			} else {
				$value = ' --';
			}
			return $value;
		}

		return parent::getDisplayValue($value, $record, $recordInstance);
	}
}
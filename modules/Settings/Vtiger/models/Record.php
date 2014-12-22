<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Roles Record Model Class
 */
abstract class Settings_Vtiger_Record_Model extends Vtiger_Base_Model {

	abstract function getId();
	abstract function getName();

	public function getRecordLinks() {

		$links = array();
		$recordLinks = array();
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}
	
	public function getDisplayValue($key) {
		return $this->get($key);
	}
}

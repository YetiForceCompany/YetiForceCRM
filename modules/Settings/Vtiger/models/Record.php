<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Roles Record Model Class.
 */
abstract class Settings_Vtiger_Record_Model extends App\Base
{
	/**
	 * Record ID.
	 *
	 * @return int
	 */
	abstract public function getId();

	/**
	 * Record name.
	 *
	 * @return string
	 */
	abstract public function getName();

	/**
	 * Get record links.
	 *
	 * @return Vtiger_Link_Model[]
	 */
	public function getRecordLinks(): array
	{
		$links = [];
		$recordLinks = [];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Get display value.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getDisplayValue(string $key)
	{
		return $this->get($key);
	}
}

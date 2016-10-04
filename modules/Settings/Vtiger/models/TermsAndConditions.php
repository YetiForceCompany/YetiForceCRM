<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_Vtiger_TermsAndConditions_Model extends Vtiger_Base_Model
{

	const tableName = 'vtiger_inventory_tandc';

	public function getText()
	{
		return $this->get('tandc');
	}

	public function setText($text)
	{
		return $this->set('tandc', $text);
	}

	public function getType()
	{
		return "Inventory";
	}

	public function save()
	{
		$db = PearDatabase::getInstance();
		$query = sprintf('SELECT 1 FROM %s', self::tableName);
		$result = $db->pquery($query, []);
		if ($db->num_rows($result) > 0) {
			$db->update(self::tableName, ['tandc' => $this->getText()]);
		} else {
			$db->insert(self::tableName, [
				'id' => $db->getUniqueID(self::tableName),
				'type' => $this->getType(),
				'tandc' => $this->getText()
			]);
		}
	}

	public static function getInstance()
	{
		$db = PearDatabase::getInstance();
		$query = sprintf('SELECT tandc FROM %s', self::tableName);
		$result = $db->pquery($query, array());
		$instance = new self();
		if ($db->num_rows($result) > 0) {
			$text = $db->query_result($result, 0, 'tandc');
			$instance->setText($text);
		}
		return $instance;
	}
}

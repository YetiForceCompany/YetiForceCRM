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
		return 'Inventory';
	}

	public function save()
	{
		$isExists = (new \App\Db\Query())->from(self::tableName)->exists();
		if ($isExists) {
			\App\Db::getInstance()->createCommand()->update(self::tableName, ['tandc' => $this->getText()])->execute();
		} else {
			\App\Db::getInstance()->createCommand()->insert(self::tableName, ['type' => $this->getType(),
				'tandc' => $this->getText()])->execute();
		}
	}

	public static function getInstance()
	{
		$row = (new App\Db\Query())->select(['tandc'])->from(self::tableName)->scalar();
		$instance = new self();
		if ($row) {
			$instance->setText($row);
		}
		return $instance;
	}
}

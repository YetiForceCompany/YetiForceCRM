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

class Settings_SMSNotifier_Module_Model extends Settings_Vtiger_Module_Model
{
	/**
	 * @var string
	 */
	public $baseTable = 'a_#__smsnotifier_servers';

	/**
	 * @var string
	 */
	public $baseIndex = 'id';

	/**
	 * @var string[]
	 */
	public $nameFields = [];

	/**
	 * @var string[]
	 */
	public $listFields = ['providertype' => 'FL_PROVIDER', 'isactive' => 'FL_STATUS'];

	/**
	 * @var string
	 */
	public $name = 'SMSNotifier';

	/**
	 * Function to get Create view url.
	 *
	 * @return string Url
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=' . $this->getName() . '&parent=' . $this->getParentName() . '&view=Edit';
	}

	/**
	 * Function to get List view url.
	 *
	 * @return string Url
	 */
	public function getListViewUrl()
	{
		return 'index.php?module=' . $this->getName() . '&parent=' . $this->getParentName() . '&view=List';
	}

	/**
	 * Function to get list of all providers.
	 *
	 * @return mixed
	 */
	public function getAllProviders()
	{
		if (empty($this->allProviders)) {
			$this->allProviders = SMSNotifier_Module_Model::getProviders();
		}
		return $this->allProviders;
	}
}

<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_CustomerPortal_Module_Model extends Settings_Vtiger_Module_Model
{

	public $name = 'CustomerPortal';

	/**
	 * Function to get Current portal user
	 * @return <Interger> userId
	 */
	public function getCurrentPortalUser()
	{
		$db = new App\db\Query();
		$db->select('prefvalue')->from('vtiger_customerportal_prefs')->where(['prefkey' => 'userid', 'tabid' => 0]);
		$dataReader = $db->createCommand()->query();
		if ($dataReader->count()) {
			return $dataReader->readColumn(0);
		}
		return false;
	}

	/**
	 * Function to get current default assignee from portal
	 * @return <Integer> userId
	 */
	public function getCurrentDefaultAssignee()
	{
		$db = new App\db\Query();
		$db->select('prefvalue')->from('vtiger_customerportal_prefs')->where(['prefkey' => 'defaultassignee', 'tabid' => 0]);
		$dataReader = $db->createCommand()->query();
		if ($dataReader->count()) {
			return $dataReader->readColumn(0);
		}
		return false;
	}

	/**
	 * Function to get list of portal modules
	 * @return <Array> list of portal modules <Vtiger_Module_Model>
	 */
	public function getModulesList()
	{
		if (!$this->portalModules) {
			$db = new App\db\Query();
			$db->select(['vtiger_customerportal_tabs.*', 'vtiger_customerportal_prefs.prefvalue', 'vtiger_tab.name'])
				->from('vtiger_customerportal_tabs')
				->innerJoin('vtiger_customerportal_prefs', 'vtiger_customerportal_prefs.tabid = vtiger_customerportal_tabs.tabid AND vtiger_customerportal_prefs.prefkey = :pref', ['pref' => 'showrelatedinfo'])
				->innerJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_customerportal_tabs.tabid AND vtiger_tab.presence = :pres', ['pres' => 0])
				->orderBy('vtiger_customerportal_tabs.sequence');
			$dataReader = $db->createCommand()->query();
			while ($row = $dataReader->read()) {
				$tabId = $row['tabid'];
				$moduleModel = Vtiger_Module_Model::getInstance($tabId);
				foreach ($row as $key => $value) {
					$moduleModel->set($key, $value);
				}
				$portalModules[$tabId] = $moduleModel;
			}
			$this->portalModules = $portalModules;
		}
		return $this->portalModules;
	}

	/**
	 * Function to save the details of Portal modules
	 */
	public function save()
	{
		$db = PearDatabase::getInstance();
		$privileges = $this->get('privileges');
		$defaultAssignee = $this->get('defaultAssignee');
		$portalModulesInfo = $this->get('portalModulesInfo');

		//Update details of view all record option for every module from Customer portal
		$updateQuery = "UPDATE vtiger_customerportal_prefs SET prefvalue = CASE ";
		foreach ($portalModulesInfo as $tabId => $moduleDetails) {
			$prefValue = $moduleDetails['prefValue'];
			$updateQuery .= " WHEN tabid = $tabId THEN $prefValue ";
		}
		$updateQuery .= " WHEN prefkey = ? THEN $privileges ";
		$updateQuery .= " WHEN prefkey = ? THEN $defaultAssignee ";
		$updateQuery .= " ELSE prefvalue END";

		$db->pquery($updateQuery, array('userid', 'defaultassignee'));

		//Update the sequence of every module in Customer portal
		$updateSequenceQuery = "UPDATE vtiger_customerportal_tabs SET visible = CASE ";

		foreach ($portalModulesInfo as $tabId => $moduleDetails) {
			$visible = $moduleDetails['visible'];
			$updateSequenceQuery .= " WHEN tabid = $tabId THEN $visible ";
		}

		$updateSequenceQuery .= " END, sequence = CASE ";
		foreach ($portalModulesInfo as $tabId => $moduleDetails) {
			$sequence = $moduleDetails['sequence'];
			$updateSequenceQuery .= " WHEN tabid = $tabId THEN $sequence ";
		}
		$updateSequenceQuery .= "END";

		$db->pquery($updateSequenceQuery, array());
	}
}

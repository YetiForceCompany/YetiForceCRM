<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Settings_Currency_Record_Model extends Settings_Vtiger_Record_Model
{

	public function getId()
	{
		return $this->get('id');
	}

	public function getName()
	{
		return $this->get('currency_name');
	}

	public function isDisabledRestricted()
	{
		$db = PearDatabase::getInstance();
		$disabledRestircted = $this->get('_disable_restricted');
		if (!empty($disabledRestircted)) {
			return $disabledRestircted;
		}
		$query = 'SELECT 1 FROM vtiger_users WHERE currency_id = ?';
		$params = array($this->getId());
		$result = $db->pquery($query, $params);

		$disabledRestircted = ($db->num_rows($result) > 0 ) ? true : false;
		$this->set('_disable_restricted', $disabledRestircted);
		return $disabledRestircted;
	}

	public function isBaseCurrency()
	{
		return ($this->get('defaultid') != '-11') ? false : true;
	}

	public function getRecordLinks()
	{
		if ($this->isBaseCurrency()) {
			//NO Edit and delete link for base currency 
			return array();
		}
		$editLink = array(
			'linkurl' => "javascript:Settings_Currency_Js.triggerEdit(event, '" . $this->getId() . "')",
			'linklabel' => 'LBL_EDIT',
			'linkicon' => 'glyphicon glyphicon-pencil'
		);
		$editLinkInstance = Vtiger_Link_Model::getInstanceFromValues($editLink);

		$deleteLink = array(
			'linkurl' => "javascript:Settings_Currency_Js.triggerDelete(event,'" . $this->getId() . "')",
			'linklabel' => 'LBL_DELETE',
			'linkicon' => 'glyphicon glyphicon-trash'
		);
		$deleteLinkInstance = Vtiger_Link_Model::getInstanceFromValues($deleteLink);
		return array($editLinkInstance, $deleteLinkInstance);
	}

	public function getDeleteStatus()
	{
		if ($this->has('deleted')) {
			return $this->get('deleted');
		}
		//by default non deleted
		return 0;
	}

	public function save()
	{
		$db = PearDatabase::getInstance();
		$ID = $this->getId();
		$tableName = Settings_Currency_Module_Model::tableName;
		if (!empty($ID)) {
			$db->update($tableName, [
				'currency_name' => $this->get('currency_name'),
				'currency_code' => $this->get('currency_code'),
				'currency_status' => $this->get('currency_status'),
				'currency_symbol' => $this->get('currency_symbol'),
				'conversion_rate' => $this->get('conversion_rate'),
				'deleted' => $this->getDeleteStatus()
				], 'id = ?', [$ID]);
		} else {
			$ID = $db->getUniqueID($tableName);
			$db->insert($tableName, [
				'id' => $ID,
				'currency_name' => $this->get('currency_name'),
				'currency_code' => $this->get('currency_code'),
				'currency_status' => $this->get('currency_status'),
				'currency_symbol' => $this->get('currency_symbol'),
				'conversion_rate' => $this->get('conversion_rate'),
				'defaultid' => 0,
				'deleted' => 0
			]);
		}
		return $ID;
	}

	public static function getInstance($ID)
	{
		$db = PearDatabase::getInstance();
		if (vtlib\Utils::isNumber($ID)) {
			$query = sprintf('SELECT * FROM %s WHERE id = ?', Settings_Currency_Module_Model::tableName);
		} else {
			$query = sprintf('SELECT * FROM %s WHERE currency_name = ?', Settings_Currency_Module_Model::tableName);
		}
		$result = $db->pquery($query, [$ID]);
		if ($db->getRowCount($result) > 0) {
			$row = $db->getRow($result);
			$instance = new self();
			$instance->setData($row);
		}
		return $instance;
	}

	public static function getAllNonMapped($includedIds = array())
	{
		$db = PearDatabase::getInstance();
		if (!is_array($includedIds)) {
			if (!empty($includedIds)) {
				$includedIds = array($includedIds);
			} else {
				$includedIds = array();
			}
		}

		$query = 'SELECT vtiger_currencies.* FROM vtiger_currencies 
                    LEFT JOIN vtiger_currency_info ON vtiger_currency_info.currency_name = vtiger_currencies.currency_name
                    WHERE vtiger_currency_info.currency_name IS NULL or vtiger_currency_info.deleted=1';
		$params = array();
		if (!empty($includedIds)) {
			$params = $includedIds;
			$query .= ' or vtiger_currency_info.id IN(' . generateQuestionMarks($includedIds) . ')';
		}
		$result = $db->pquery($query, $params);
		$currencyModelList = array();
		$num_rows = $db->num_rows($result);

		for ($i = 0; $i < $num_rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$modelInstance = new self();
			$modelInstance->setData($row);
			$currencyModelList[$row['currencyid']] = $modelInstance;
		}
		return $currencyModelList;
	}

	public static function getAll($excludedIds = [])
	{
		$db = PearDatabase::getInstance();

		if (!is_array($excludedIds)) {
			$excludedIds = [$excludedIds];
		}

		$query = sprintf('SELECT * FROM %s WHERE deleted = ? && currency_status = ?', Settings_Currency_Module_Model::tableName);
		$params = [0, 'Active'];
		if (!empty($excludedIds)) {
			$params[] = $excludedIds;
			$query .= ' && id NOT IN (' . generateQuestionMarks($excludedIds) . ')';
		}
		$result = $db->pquery($query, $params);
		$instanceList = [];
		while ($row = $db->getRow($result)) {
			$instanceList[$row['id']] = new Settings_Currency_Record_Model($row);
		}
		return $instanceList;
	}
}

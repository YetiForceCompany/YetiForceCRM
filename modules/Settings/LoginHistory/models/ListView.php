<?php

/**
 * 
 * @package YetiForce.Views
 * @license licenses/License.html
 * @author Mriusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_LoginHistory_ListView_Model extends Settings_Vtiger_ListView_Model
{

	/**
	 * Funtion to get the Login history basic query
	 * @return type
	 */
	public function getBasicListQuery()
	{
		$module = $this->getModule();
		$query = "SELECT login_id, user_name, user_ip, logout_time, login_time, vtiger_loginhistory.status FROM $module->baseTable";

		$search_key = $this->get('search_key');
		$value = $this->get('search_value');

		if (!empty($search_key) && !empty($value)) {
			$query .= " WHERE $module->baseTable.$search_key = '$value'";
		}
		$query .= " ORDER BY login_time DESC";
		return $query;
	}

	public function getListViewLinks()
	{
		return [];
	}

	/**
	 * Function which will get the list view count  
	 * @return - number of records 
	 */
	public function getListViewCount()
	{
		$module = $this->getModule();
		$query = (new \App\db\Query())->from($module->baseTable);
		$searchKey = $this->get('search_key');
		$value = $this->get('search_value');
		if (!empty($searchKey) && !empty($value)) {
			$query->where(["$module->baseTable.$searchKey" => $value]);
		}
		return $query->count();
	}
}

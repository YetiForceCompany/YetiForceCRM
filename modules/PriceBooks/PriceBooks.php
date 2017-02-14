<?php
/* * *******************************************************************************
 * * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 * ****************************************************************************** */

class PriceBooks extends CRMEntity
{

	public $table_name = 'vtiger_pricebook';
	public $table_index = 'pricebookid';
	public $tab_name = Array('vtiger_crmentity', 'vtiger_pricebook', 'vtiger_pricebookcf');
	public $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_pricebook' => 'pricebookid', 'vtiger_pricebookcf' => 'pricebookid');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = Array('vtiger_pricebookcf', 'pricebookid');
	public $column_fields = Array();
	// This is the list of fields that are in the lists.
	public $list_fields = Array(
		'Price Book Name' => Array('pricebook' => 'bookname'),
		'Active' => Array('pricebook' => 'active')
	);
	public $list_fields_name = Array(
		'Price Book Name' => 'bookname',
		'Active' => 'active'
	);

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['bookname', 'active', 'currency_id'];
	public $list_link_field = 'bookname';
	public $search_fields = Array(
		'Price Book Name' => Array('pricebook' => 'bookname')
	);
	public $search_fields_name = Array(
		'Price Book Name' => 'bookname'
	);
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	public $mandatory_fields = Array('bookname', 'currency_id', 'pricebook_no', 'createdtime', 'modifiedtime');
	// For Alphabetical search
	public $def_basicsearch_col = 'bookname';

	/** function used to get whether the pricebook has related with a product or not
	 * 	@param int $id - product id
	 * 	@return true or false - if there are no pricebooks available or associated pricebooks for the product is equal to total number of pricebooks then return false, else return true
	 */
	public function get_pricebook_noproduct($id)
	{

		\App\Log::trace('Entering get_pricebook_noproduct(' . $id . ') method ...');

		$query = 'select vtiger_crmentity.crmid, vtiger_pricebook.* from vtiger_pricebook inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_pricebook.pricebookid where vtiger_crmentity.deleted=0';
		$result = $this->db->pquery($query, array());
		$no_count = $this->db->num_rows($result);
		if ($no_count != 0) {
			$pb_query = 'select vtiger_crmentity.crmid, vtiger_pricebook.pricebookid,vtiger_pricebookproductrel.productid from vtiger_pricebook inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_pricebook.pricebookid inner join vtiger_pricebookproductrel on vtiger_pricebookproductrel.pricebookid=vtiger_pricebook.pricebookid where vtiger_crmentity.deleted=0 and vtiger_pricebookproductrel.productid=?';
			$result_pb = $this->db->pquery($pb_query, array($id));
			if ($no_count == $this->db->num_rows($result_pb)) {
				\App\Log::trace('Exiting get_pricebook_noproduct method ...');
				return false;
			} elseif ($this->db->num_rows($result_pb) == 0) {
				\App\Log::trace('Exiting get_pricebook_noproduct method ...');
				return true;
			} elseif ($this->db->num_rows($result_pb) < $no_count) {
				\App\Log::trace('Exiting get_pricebook_noproduct method ...');
				return true;
			}
		} else {
			\App\Log::trace('Exiting get_pricebook_noproduct method ...');
			return false;
		}
	}
	/*
	 * Function to get the primary query part of a report
	 * @param - $module Primary module name
	 * returns the query string formed on fetching the related data for report for primary module
	 */

	public function generateReportsQuery($module, $queryplanner)
	{
		$moduletable = $this->table_name;
		$moduleindex = $this->table_index;
		$modulecftable = $this->customFieldTable[0];
		$modulecfindex = $this->customFieldTable[1];

		$cfquery = '';
		if (isset($modulecftable) && $queryplanner->requireTable($modulecftable)) {
			$cfquery = "inner join $modulecftable as $modulecftable on $modulecftable.$modulecfindex=$moduletable.$moduleindex";
		}

		$query = "from $moduletable $cfquery
					inner join vtiger_crmentity on vtiger_crmentity.crmid=$moduletable.$moduleindex";
		if ($queryplanner->requireTable("vtiger_currency_info$module")) {
			$query .= "  left join vtiger_currency_info as vtiger_currency_info$module on vtiger_currency_info$module.id = $moduletable.currency_id";
		}
		if ($queryplanner->requireTable("vtiger_groups$module")) {
			$query .= " left join vtiger_groups as vtiger_groups$module on vtiger_groups$module.groupid = vtiger_crmentity.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_users$module")) {
			$query .= " left join vtiger_users as vtiger_users$module on vtiger_users$module.id = vtiger_crmentity.smownerid";
		}
		$query .= ' left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid';
		$query .= ' left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid';

		if ($queryplanner->requireTable('vtiger_lastModifiedByPriceBooks')) {
			$query .= ' left join vtiger_users as vtiger_lastModifiedByPriceBooks on vtiger_lastModifiedByPriceBooks.id = vtiger_crmentity.modifiedby ';
		}
		if ($queryplanner->requireTable('u_yf_crmentity_showners')) {
			$query .= ' LEFT JOIN u_yf_crmentity_showners ON u_yf_crmentity_showners.crmid = vtiger_crmentity.crmid';
		}
		if ($queryplanner->requireTable("vtiger_shOwners$module")) {
			$query .= ' LEFT JOIN vtiger_users AS vtiger_shOwners' . $module . ' ON vtiger_shOwners' . $module . '.id = u_yf_crmentity_showners.userid';
		}
		return $query;
	}
	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */

	public function generateReportsSecQuery($module, $secmodule, $queryplanner)
	{

		$matrix = $queryplanner->newDependencyMatrix();

		$matrix->setDependency('vtiger_crmentityPriceBooks', array('vtiger_usersPriceBooks', 'vtiger_groupsPriceBooks'));
		$matrix->setDependency('vtiger_pricebook', array('vtiger_crmentityPriceBooks', 'vtiger_currency_infoPriceBooks'));
		if (!$queryplanner->requireTable('vtiger_pricebook', $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module, $secmodule, 'vtiger_pricebook', 'pricebookid', $queryplanner);

		if ($queryplanner->requireTable('vtiger_crmentityPriceBooks', $matrix)) {
			$query .= ' left join vtiger_crmentity as vtiger_crmentityPriceBooks on vtiger_crmentityPriceBooks.crmid=vtiger_pricebook.pricebookid and vtiger_crmentityPriceBooks.deleted=0';
		}
		if ($queryplanner->requireTable('vtiger_currency_infoPriceBooks')) {
			$query .= ' left join vtiger_currency_info as vtiger_currency_infoPriceBooks on vtiger_currency_infoPriceBooks.id = vtiger_pricebook.currency_id';
		}
		if ($queryplanner->requireTable('vtiger_usersPriceBooks')) {
			$query .= ' left join vtiger_users as vtiger_usersPriceBooks on vtiger_usersPriceBooks.id = vtiger_crmentityPriceBooks.smownerid';
		}
		if ($queryplanner->requireTable('vtiger_groupsPriceBooks')) {
			$query .= ' left join vtiger_groups as vtiger_groupsPriceBooks on vtiger_groupsPriceBooks.groupid = vtiger_crmentityPriceBooks.smownerid';
		}
		if ($queryplanner->requireTable('vtiger_lastModifiedByPriceBooks')) {
			$query .= ' left join vtiger_users as vtiger_lastModifiedByPriceBooks on vtiger_lastModifiedByPriceBooks.id = vtiger_crmentityPriceBooks.smownerid';
		}
		if ($queryplanner->requireTable('vtiger_createdbyPriceBooks')) {
			$query .= ' left join vtiger_users as vtiger_createdbyPriceBooks on vtiger_createdbyPriceBooks.id = vtiger_crmentityPriceBooks.smcreatorid ';
		}
		return $query;
	}
	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */

	public function setRelationTables($secmodule = false)
	{
		$relTables = array(
			'Products' => array('vtiger_pricebookproductrel' => array('pricebookid', 'productid'), 'vtiger_pricebook' => 'pricebookid'),
			'Services' => array('vtiger_pricebookproductrel' => array('pricebookid', 'productid'), 'vtiger_pricebook' => 'pricebookid'),
		);
		if ($secmodule === false) {
			return $relTables;
		}
		return $relTables[$secmodule];
	}
}

<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 * ****************************************************************************** */
/* * *******************************************************************************
 * $Header$
 * Description:  Defines the Account SugarBean Account entity with the necessary
 * methods and variables.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 * ****************************************************************************** */

// Faq is used to store vtiger_faq information.
class Faq extends CRMEntity
{

	public $table_name = 'vtiger_faq';
	public $table_index = 'id';
	//fix for Custom Field for FAQ 
	public $tab_name = Array('vtiger_crmentity', 'vtiger_faq', 'vtiger_faqcf');
	public $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_faq' => 'id', 'vtiger_faqcomments' => 'faqid', 'vtiger_faqcf' => 'faqid');
	public $customFieldTable = Array('vtiger_faqcf', 'faqid');
	public $entity_table = 'vtiger_crmentity';
	public $column_fields = Array();
	// This is the list of vtiger_fields that are in the lists.
	public $list_fields = Array(
		'FAQ Id' => Array('faq' => 'id'),
		'Question' => Array('faq' => 'question'),
		'Category' => Array('faq' => 'category'),
		'Product Name' => Array('faq' => 'product_id'),
		'Created Time' => Array('crmentity' => 'createdtime'),
		'Modified Time' => Array('crmentity' => 'modifiedtime')
	);
	public $list_fields_name = Array(
		'FAQ Id' => '',
		'Question' => 'question',
		'Category' => 'faqcategories',
		'Product Name' => 'product_id',
		'Created Time' => 'createdtime',
		'Modified Time' => 'modifiedtime'
	);

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['question', 'faqcategories', 'product_id', 'createdtime', 'modifiedtime'];
	public $list_link_field = 'question';
	public $search_fields = Array(
		'Account Name' => Array('account' => 'accountname'),
		'City' => Array('accountaddress' => 'addresslevel5a'),
	);
	public $search_fields_name = Array(
		'Account Name' => 'accountname',
		'City' => 'addresslevel5a',
	);
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'DESC';
	public $mandatory_fields = Array('question', 'faq_answer', 'createdtime', 'modifiedtime');
	// For Alphabetical search
	public $def_basicsearch_col = 'question';

	/*
	 * Function to get the primary query part of a report
	 * @param - $module Primary module name
	 * returns the query string formed on fetching the related data for report for primary module
	 */

	public function generateReportsQuery($module, $queryPlanner)
	{
		$moduletable = $this->table_name;
		$moduleindex = $this->table_index;

		$query = "from $moduletable
					inner join vtiger_crmentity on vtiger_crmentity.crmid=$moduletable.$moduleindex
					left join vtiger_products as vtiger_products$module on vtiger_products$module.productid = vtiger_faq.product_id
					left join vtiger_groups as vtiger_groups$module on vtiger_groups$module.groupid = vtiger_crmentity.smownerid
					left join vtiger_users as vtiger_users$module on vtiger_users$module.id = vtiger_crmentity.smownerid
					left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
					left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
                    left join vtiger_users as vtiger_lastModifiedBy" . $module . ' on vtiger_lastModifiedBy' . $module . '.id = vtiger_crmentity.modifiedby';
		if ($queryPlanner->requireTable('u_yf_crmentity_showners')) {
			$query .= ' LEFT JOIN u_yf_crmentity_showners ON u_yf_crmentity_showners.crmid = vtiger_crmentity.crmid';
		}
		if ($queryPlanner->requireTable("vtiger_shOwners$module")) {
			$query .= ' LEFT JOIN vtiger_users AS vtiger_shOwners' . $module . ' ON vtiger_shOwners' . $module . '.id = u_yf_crmentity_showners.userid';
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
			'Documents' => array('vtiger_senotesrel' => array('crmid', 'notesid'), 'vtiger_faq' => 'id'),
		);
		if ($secmodule === false) {
			return $relTables;
		}
		return $relTables[$secmodule];
	}

	public function clearSingletonSaveFields()
	{
		$this->column_fields['comments'] = '';
	}
}

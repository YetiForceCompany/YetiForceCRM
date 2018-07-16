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
	public $tab_name = ['vtiger_crmentity', 'vtiger_faq', 'vtiger_faqcf'];
	public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_faq' => 'id', 'vtiger_faqcomments' => 'faqid', 'vtiger_faqcf' => 'faqid'];
	public $customFieldTable = ['vtiger_faqcf', 'faqid'];
	public $entity_table = 'vtiger_crmentity';
	public $column_fields = [];
	// This is the list of vtiger_fields that are in the lists.
	public $list_fields = [
		'FAQ Id' => ['faq' => 'id'],
		'Question' => ['faq' => 'question'],
		'Category' => ['faq' => 'category'],
		'Product Name' => ['faq' => 'product_id'],
		'Created Time' => ['crmentity' => 'createdtime'],
		'Modified Time' => ['crmentity' => 'modifiedtime'],
	];
	public $list_fields_name = [
		'FAQ Id' => '',
		'Question' => 'question',
		'Category' => 'faqcategories',
		'Product Name' => 'product_id',
		'Created Time' => 'createdtime',
		'Modified Time' => 'modifiedtime',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['question', 'faqcategories', 'product_id', 'createdtime', 'modifiedtime'];
	public $list_link_field = 'question';
	public $search_fields = [
		'Account Name' => ['account' => 'accountname'],
		'City' => ['accountaddress' => 'addresslevel5a'],
	];
	public $search_fields_name = [
		'Account Name' => 'accountname',
		'City' => 'addresslevel5a',
	];
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'DESC';
	public $mandatory_fields = ['question', 'faq_answer', 'createdtime', 'modifiedtime'];
	// For Alphabetical search
	public $def_basicsearch_col = 'question';

	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */

	public function setRelationTables($secmodule = false)
	{
		$relTables = [
			'Documents' => ['vtiger_senotesrel' => ['crmid', 'notesid'], 'vtiger_faq' => 'id'],
		];
		if ($secmodule === false) {
			return $relTables;
		}
		return $relTables[$secmodule];
	}
}

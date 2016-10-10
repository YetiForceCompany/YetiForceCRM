<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Services extends CRMEntity
{

	var $db, $log; // Used in class functions of CRMEntity
	var $table_name = 'vtiger_service';
	var $table_index = 'serviceid';
	var $column_fields = Array();

	/** Indicator if this is a custom module or standard module */
	var $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_servicecf', 'serviceid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_service', 'vtiger_servicecf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_service' => 'serviceid',
		'vtiger_servicecf' => 'serviceid',
		'vtiger_producttaxrel' => 'productid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Service No' => Array('service' => 'service_no'),
		'Service Name' => Array('service' => 'servicename'),
		'Commission Rate' => Array('service' => 'commissionrate'),
		'No of Units' => Array('service' => 'qty_per_unit'),
		'Price' => Array('service' => 'unit_price')
	);
	var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Service No' => 'service_no',
		'Service Name' => 'servicename',
		'Commission Rate' => 'commissionrate',
		'No of Units' => 'qty_per_unit',
		'Price' => 'unit_price'
	);
	// Make the field link to detail view
	var $list_link_field = 'servicename';
	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Service No' => Array('service' => 'service_no'),
		'Service Name' => Array('service' => 'servicename'),
		'Price' => Array('service' => 'unit_price')
	);
	var $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Service No' => 'service_no',
		'Service Name' => 'servicename',
		'Price' => 'unit_price'
	);
	// For Popup window record selection
	var $popup_fields = Array('servicename', 'service_usageunit', 'unit_price');
	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	var $sortby_fields = Array();
	// For Alphabetical search
	var $def_basicsearch_col = 'servicename';
	// Column value to use on detail view record text display
	var $def_detailview_recname = 'servicename';
	// Required Information for enabling Import feature
	var $required_fields = Array('servicename' => 1);
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('servicename', 'assigned_user_id');
	var $default_order_by = '';
	var $default_sort_order = 'ASC';
	var $unit_price;

	public function save_module($module)
	{
		//Inserting into service_taxrel table
		if (AppRequest::get('ajxaction') != 'DETAILVIEW' && AppRequest::get('action') != 'MassEditSave' && AppRequest::get('action') != 'ProcessDuplicates') {
			$this->insertTaxInformation('vtiger_producttaxrel', 'Services');
			$this->insertPriceInformation('vtiger_productcurrencyrel', 'Services');
		}
		// Update unit price value in vtiger_productcurrencyrel
		$this->updateUnitPrice();
	}

	/** 	function to save the service tax information in vtiger_servicetaxrel table
	 * 	@param string $tablename - vtiger_tablename to save the service tax relationship (servicetaxrel)
	 * 	@param string $module	 - current module name
	 * 	$return void
	 */
	public function insertTaxInformation($tablename, $module)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$log->debug("Entering into insertTaxInformation($tablename, $module) method ...");
		$tax_details = getAllTaxes();

		$tax_per = '';
		//Save the Product - tax relationship if corresponding tax check box is enabled
		//Delete the existing tax if any
		if ($this->mode == 'edit') {
			for ($i = 0; $i < count($tax_details); $i++) {
				$taxid = getTaxId($tax_details[$i]['taxname']);
				$sql = "delete from vtiger_producttaxrel where productid=? and taxid=?";
				$adb->pquery($sql, array($this->id, $taxid));
			}
		}
		for ($i = 0; $i < count($tax_details); $i++) {
			$tax_name = $tax_details[$i]['taxname'];
			$tax_checkname = $tax_details[$i]['taxname'] . "_check";
			if (AppRequest::get($tax_checkname) == 'on' || AppRequest::get($tax_checkname) == 1) {
				$taxid = getTaxId($tax_name);
				$tax_per = AppRequest::get($tax_name);
				if ($tax_per == '') {
					$log->debug("Tax selected but value not given so default value will be saved.");
					$tax_per = getTaxPercentage($tax_name);
				}

				$log->debug("Going to save the Product - $tax_name tax relationship");

				$query = "insert into vtiger_producttaxrel values(?,?,?)";
				$adb->pquery($query, array($this->id, $taxid, $tax_per));
			}
		}

		$log->debug("Exiting from insertTaxInformation($tablename, $module) method ...");
	}

	/** 	function to save the service price information in vtiger_servicecurrencyrel table
	 * 	@param string $tablename - vtiger_tablename to save the service currency relationship (servicecurrencyrel)
	 * 	@param string $module	 - current module name
	 * 	$return void
	 */
	public function insertPriceInformation($tablename, $module)
	{
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		$log = vglobal('log');
		$log->debug("Entering into insertPriceInformation($tablename, $module) method ...");
		//removed the update of currency_id based on the logged in user's preference : fix 6490


		$currency_details = getAllCurrencies('all');

		//Delete the existing currency relationship if any
		if ($this->mode == 'edit' && AppRequest::get('action') != 'MassEditSave' && AppRequest::get('action') != 'ProcessDuplicates') {
			for ($i = 0; $i < count($currency_details); $i++) {
				$curid = $currency_details[$i]['curid'];
				$sql = "delete from vtiger_productcurrencyrel where productid=? and currencyid=?";
				$adb->pquery($sql, array($this->id, $curid));
			}
		}

		$service_base_conv_rate = getBaseConversionRateForProduct($this->id, $this->mode, $module);

		//Save the Product - Currency relationship if corresponding currency check box is enabled
		for ($i = 0; $i < count($currency_details); $i++) {
			$curid = $currency_details[$i]['curid'];
			$curname = $currency_details[$i]['currencylabel'];
			$cur_checkname = 'cur_' . $curid . '_check';
			$cur_valuename = 'curname' . $curid;
			$base_currency_check = 'base_currency' . $curid;
			$requestPrice = CurrencyField::convertToDBFormat(AppRequest::get('unit_price'), null, true);
			$actualPrice = CurrencyField::convertToDBFormat(AppRequest::get($cur_valuename), null, true);
			if (AppRequest::get($cur_valuename) == 'on' || AppRequest::get($cur_checkname) == 1) {
				$conversion_rate = $currency_details[$i]['conversionrate'];
				$actual_conversion_rate = $service_base_conv_rate * $conversion_rate;
				$converted_price = $actual_conversion_rate * $requestPrice;

				$log->debug("Going to save the Product - $curname currency relationship");

				$query = "insert into vtiger_productcurrencyrel values(?,?,?,?)";
				$adb->pquery($query, array($this->id, $curid, $converted_price, $actualPrice));

				// Update the Product information with Base Currency choosen by the User.
				if (AppRequest::get('base_currency') == $cur_valuename) {
					$adb->pquery("update vtiger_service set currency_id=?, unit_price=? where serviceid=?", array($curid, $actualPrice, $this->id));
				}
			} else {
				$curid = \vtlib\Functions::userCurrencyId($current_user->id);
				$adb->pquery("update vtiger_service set currency_id=? where serviceid=?", array($curid, $this->id));
			}
		}

		$log->debug("Exiting from insertPriceInformation($tablename, $module) method ...");
	}

	public function updateUnitPrice()
	{
		$prod_res = $this->db->pquery("select unit_price, currency_id from vtiger_service where serviceid=?", array($this->id));
		$prod_unit_price = $this->db->query_result($prod_res, 0, 'unit_price');
		$prod_base_currency = $this->db->query_result($prod_res, 0, 'currency_id');

		$query = "update vtiger_productcurrencyrel set actual_price=? where productid=? and currencyid=?";
		$params = array($prod_unit_price, $this->id, $prod_base_currency);
		$this->db->pquery($query, $params);
	}

	/**
	 * Return query to use based on given modulename, fieldname
	 * Useful to handle specific case handling for Popup
	 */
	public function getQueryByModuleField($module, $fieldname, $srcrecord)
	{
		// $srcrecord could be empty
	}

	/**
	 * Get list view query.
	 */
	public function getListQuery($module, $where = '')
	{
		$query = "SELECT vtiger_crmentity.*, $this->table_name.*";

		// Select Custom Field Table Columns if present
		if (!empty($this->customFieldTable))
			$query .= ", " . $this->customFieldTable[0] . ".* ";

		$query .= " FROM $this->table_name";

		$query .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		// Consider custom table join as well.
		if (!empty($this->customFieldTable)) {
			$query .= " INNER JOIN " . $this->customFieldTable[0] . " ON " . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
				" = $this->table_name.$this->table_index";
		}
		$query .= " LEFT JOIN vtiger_groups
						ON vtiger_groups.groupid = vtiger_crmentity.smownerid
					LEFT JOIN vtiger_users
						ON vtiger_users.id = vtiger_crmentity.smownerid ";
		$current_user = vglobal('current_user');
		$query .= $this->getNonAdminAccessControlQuery($module, $current_user);
		$query .= sprintf('WHERE vtiger_crmentity.deleted = 0 %s', $where);
		return $query;
	}

	/**
	 * Apply security restriction (sharing privilege) query part for List view.
	 */
	public function getListViewSecurityParameter($module)
	{
		$current_user = vglobal('current_user');
		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		require('user_privileges/sharing_privileges_' . $current_user->id . '.php');

		$sec_query = '';
		$tabid = \includes\Modules::getModuleId($module);

		if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tabid] == 3) {

			$sec_query .= " && (vtiger_crmentity.smownerid in($current_user->id) || vtiger_crmentity.smownerid IN
					(
						SELECT vtiger_user2role.userid FROM vtiger_user2role
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid
						INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid
						WHERE vtiger_role.parentrole LIKE '" . $current_user_parent_role_seq . "::%'
					)
					OR vtiger_crmentity.smownerid IN
					(
						SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per
						WHERE userid=" . $current_user->id . " && tabid=" . $tabid . "
					)
					OR
						(";

			// Build the query based on the group association of current user.
			if (sizeof($current_user_groups) > 0) {
				$sec_query .= " vtiger_groups.groupid IN (" . implode(",", $current_user_groups) . ") || ";
			}
			$sec_query .= " vtiger_groups.groupid IN
						(
							SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid
							FROM vtiger_tmp_read_group_sharing_per
							WHERE userid=" . $current_user->id . " and tabid=" . $tabid . "
						)";
			$sec_query .= ")
				)";
		}
		return $sec_query;
	}

	/**
	 * Create query to export the records.
	 */
	public function create_export_query($where)
	{
		$current_user = vglobal('current_user');

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery('Services', "detail_view");

		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list
					FROM vtiger_crmentity INNER JOIN $this->table_name ON vtiger_crmentity.crmid=$this->table_name.$this->table_index";

		if (!empty($this->customFieldTable)) {
			$query .= " INNER JOIN " . $this->customFieldTable[0] . " ON " . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
				" = $this->table_name.$this->table_index";
		}

		$query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";
		$query .= " LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id && vtiger_users.status='Active'";
		$query .= $this->getNonAdminAccessControlQuery('Services', $current_user);
		$where_auto = " vtiger_crmentity.deleted=0";

		if ($where != '')
			$query .= " WHERE ($where) && $where_auto";
		else
			$query .= " WHERE $where_auto";

		return $query;
	}

	/**
	 * Transform the value while exporting
	 */
	public function transform_export_value($key, $value)
	{
		return parent::transform_export_value($key, $value);
	}

	/**
	 * Function which will give the basic query to find duplicates
	 */
	public function getDuplicatesQuery($module, $table_cols, $field_values, $ui_type_arr, $select_cols = '')
	{
		$select_clause = sprintf("SELECT %s.%s AS recordid, vtiger_users_last_import.deleted,%s", $this->table_name, $this->table_index, $table_cols);

		// Select Custom Field Table Columns if present
		if (isset($this->customFieldTable))
			$query .= ", " . $this->customFieldTable[0] . ".* ";

		$from_clause = " FROM $this->table_name";

		$from_clause .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		// Consider custom table join as well.
		if (isset($this->customFieldTable)) {
			$from_clause .= " INNER JOIN " . $this->customFieldTable[0] . " ON " . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
				" = $this->table_name.$this->table_index";
		}
		$from_clause .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
							LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";
		$where_clause = "	WHERE vtiger_crmentity.deleted = 0";
		$where_clause .= $this->getListViewSecurityParameter($module);

		if (isset($select_cols) && trim($select_cols) != '') {
			$sub_query = "SELECT $select_cols FROM  $this->table_name AS t " .
				" INNER JOIN vtiger_crmentity AS crm ON crm.crmid = t." . $this->table_index;
			// Consider custom table join as well.
			if (isset($this->customFieldTable)) {
				$sub_query .= " INNER JOIN " . $this->customFieldTable[0] . " tcf ON tcf." . $this->customFieldTable[1] . " = t.$this->table_index";
			}
			$sub_query .= " WHERE crm.deleted=0 GROUP BY $select_cols HAVING COUNT(*)>1";
		} else {
			$sub_query = "SELECT $table_cols $from_clause $where_clause GROUP BY $table_cols HAVING COUNT(*)>1";
		}


		$query = $select_clause . $from_clause .
			" LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=" . $this->table_name . "." . $this->table_index .
			" INNER JOIN (" . $sub_query . ") AS temp ON " . get_on_clause($field_values, $ui_type_arr, $module) .
			$where_clause .
			" ORDER BY $table_cols," . $this->table_name . "." . $this->table_index . " ASC";

		return $query;
	}
	/**
	 * Handle saving related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	// function save_related_module($module, $crmid, $with_module, $with_crmid) { }

	/**
	 * Handle deleting related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function delete_related_module($module, $crmid, $with_module, $with_crmid) { }

	/**
	 * Handle getting related list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }

	/** 	function used to get the list of pricebooks which are related to the service
	 * 	@param int $id - service id
	 * 	@return array - array which will be returned from the function GetRelatedList
	 */
	public function get_service_pricebooks($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		global $currentModule, $singlepane_view, $mod_strings;
		$log = LoggerManager::getInstance();
		$log->debug("Entering get_service_pricebooks(" . $id . ") method ...");

		$related_module = vtlib\Functions::getModuleName($rel_tab_id);
		\vtlib\Deprecated::checkFileAccessForInclusion("modules/$related_module/$related_module.php");
		require_once("modules/$related_module/$related_module.php");
		$focus = new $related_module();
		$singular_modname = vtlib_toSingular($related_module);

		if ($singlepane_view == 'true')
			$returnset = "&return_module=$currentModule&return_action=DetailView&return_id=$id";
		else
			$returnset = "&return_module=$currentModule&return_action=CallRelatedList&return_id=$id";

		$button = '';
		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 1, '') == 'yes' && isPermitted($currentModule, 'EditView', $id) == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_ADD_TO') . " " . \includes\Language::translate($related_module) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"AddServiceToPriceBooks\";this.form.module.value=\"$currentModule\"' type='submit' name='button'" .
					" value='" . \includes\Language::translate('LBL_ADD_TO') . " " . \includes\Language::translate($singular_modname) . "'>&nbsp;";
			}
		}

		$query = sprintf('SELECT vtiger_crmentity.crmid,
			vtiger_pricebook.*,
			vtiger_pricebookproductrel.productid as prodid
			FROM vtiger_pricebook
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_pricebook.pricebookid
			INNER JOIN vtiger_pricebookproductrel
				ON vtiger_pricebookproductrel.pricebookid = vtiger_pricebook.pricebookid
			INNER JOIN vtiger_pricebookcf
				ON vtiger_pricebookcf.pricebookid = vtiger_pricebook.pricebookid
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_pricebookproductrel.productid = %s', $id);
		$log->debug("Exiting get_product_pricebooks method ...");

		$return_value = GetRelatedList($currentModule, $related_module, $focus, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_service_pricebooks method ...");
		return $return_value;
	}

	/** 	Function to display the Services which are related to the PriceBook
	 * 	@param string $query - query to get the list of products which are related to the current PriceBook
	 * 	@param object $focus - PriceBook object which contains all the information of the current PriceBook
	 * 	@param string $returnset - return_module, return_action and return_id which are sequenced with & to pass to the URL which is optional
	 * 	return array $return_data which will be formed like array('header'=>$header,'entries'=>$entries_list) where as $header contains all the header columns and $entries_list will contain all the Service entries
	 */
	public function getPriceBookRelatedServices($query, $focus, $returnset = '')
	{
		$log = vglobal('log');
		$log->debug("Entering getPriceBookRelatedServices(" . $query . "," . get_class($focus) . "," . $returnset . ") method ...");

		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		$current_language = vglobal('current_language');
		$current_module_strings = return_module_language($current_language, 'Services');
		$no_of_decimal_places = getCurrencyDecimalPlaces();
		$listMaxEntriesPerPage = AppConfig::main('list_max_entries_per_page');
		global $urlPrefix;

		global $theme;
		$pricebook_id = AppRequest::get('record');
		$theme_path = "themes/" . $theme . "/";
		$image_path = $theme_path . "images/";

		$computeCount = AppRequest::get('withCount');
		if (AppConfig::performance('LISTVIEW_COMPUTE_PAGE_COUNT') === true ||
			((boolean) $computeCount) == true) {
			$noofrows = $adb->query_result($adb->query(vtlib\Functions::mkCountQuery($query)), 0, 'count');
		} else {
			$noofrows = null;
		}
		$module = 'PriceBooks';
		$relatedmodule = 'Services';
		if (!$_SESSION['rlvs'][$module][$relatedmodule]) {
			$modObj = new ListViewSession();
			$modObj->sortby = $focus->default_order_by;
			$modObj->sorder = $focus->default_sort_order;
			$_SESSION['rlvs'][$module][$relatedmodule] = get_object_vars($modObj);
		}
		if (AppRequest::get('relmodule') == $relatedmodule) {
			$relmodule = AppRequest::get('relmodule');
			if ($_SESSION['rlvs'][$module][$relmodule]) {
				setSessionVar($_SESSION['rlvs'][$module][$relmodule], $noofrows, $listMaxEntriesPerPage, $module, $relmodule);
			}
		}
		global $relationId;
		$start = RelatedListViewSession::getRequestCurrentPage($relationId, $query);
		$navigation_array = VT_getSimpleNavigationValues($start, $listMaxEntriesPerPage, $noofrows);

		$limit_start_rec = ($start - 1) * $listMaxEntriesPerPage;

		if ($adb->isPostgres())
			$list_result = $adb->pquery($query .
				" OFFSET $limit_start_rec LIMIT $listMaxEntriesPerPage", array());
		else
			$list_result = $adb->pquery($query .
				" LIMIT $limit_start_rec, $listMaxEntriesPerPage", array());

		$header = array();
		$header[] = $current_module_strings['LBL_LIST_SERVICE_NAME'];
		if (getFieldVisibilityPermission('Services', $current_user->id, 'unit_price') == '0')
			$header[] = $current_module_strings['LBL_SERVICE_UNIT_PRICE'];
		$header[] = $current_module_strings['LBL_PB_LIST_PRICE'];
		if (isPermitted("PriceBooks", "EditView", "") == 'yes' || isPermitted("PriceBooks", "Delete", "") == 'yes')
			$header[] = \includes\Language::translate('LBL_ACTION');

		$currency_id = $focus->column_fields['currency_id'];
		$numRows = $adb->num_rows($list_result);
		for ($i = 0; $i < $numRows; $i++) {
			$entity_id = $adb->query_result($list_result, $i, "crmid");
			$unit_price = $adb->query_result($list_result, $i, "unit_price");
			if ($currency_id != null) {
				$prod_prices = getPricesForProducts($currency_id, array($entity_id), 'Services');
				$unit_price = $prod_prices[$entity_id];
			}
			$listprice = $adb->query_result($list_result, $i, "listprice");
			$field_name = $entity_id . "_listprice";

			$entries = Array();
			$entries[] = textlength_check($adb->query_result($list_result, $i, "servicename"));
			if (getFieldVisibilityPermission('Services', $current_user->id, 'unit_price') == '0')
				$entries[] = CurrencyField::convertToUserFormat($unit_price, null, true);

			$entries[] = CurrencyField::convertToUserFormat($listprice, null, true);
			$action = "";
			if (isPermitted("PriceBooks", "EditView", "") == 'yes' && isPermitted('Services', 'EditView', $entity_id) == 'yes') {
				$action .= '<img style="cursor:pointer;" src="themes/images/editfield.gif" border="0" onClick="fnvshobj(this,\'editlistprice\'),editProductListPrice(\'' . $entity_id . '\',\'' . $pricebook_id . '\',\'' . number_format($listprice, $no_of_decimal_places, '.', '') . '\')" alt="' . \includes\Language::translate('LBL_EDIT_BUTTON') . '" title="' . \includes\Language::translate('LBL_EDIT_BUTTON') . '"/>';
			} else {
				$action .= '<img src="' . vtiger_imageurl('blank.gif', $theme) . '" border="0" />';
			}
			if (isPermitted("PriceBooks", "Delete", "") == 'yes' && isPermitted('Services', 'Delete', $entity_id) == 'yes') {
				if ($action != "")
					$action .= '&nbsp;|&nbsp;';
				$action .= '<img src="themes/images/delete.gif" onclick="if(confirm(\'' . \includes\Language::translate('ARE_YOU_SURE') . '\')) deletePriceBookProductRel(' . $entity_id . ',' . $pricebook_id . ');" alt="' . \includes\Language::translate('LBL_DELETE') . '" title="' . \includes\Language::translate('LBL_DELETE') . '" style="cursor:pointer;" border="0">';
			}
			if ($action != "")
				$entries[] = $action;
			$entries_list[] = $entries;
		}
		$navigationOutput[] = getRecordRangeMessage($list_result, $limit_start_rec, $noofrows);
		$navigationOutput[] = getRelatedTableHeaderNavigation($navigation_array, '', $module, $relatedmodule, $focus->id);
		$return_data = array('header' => $header, 'entries' => $entries_list, 'navigation' => $navigationOutput);

		$log->debug("Exiting getPriceBookRelatedServices method ...");
		return $return_data;
	}

	/**
	 * Move the related records of the specified list of id's to the given record.
	 * @param String This module name
	 * @param Array List of Entity Id's from which related records need to be transfered
	 * @param Integer Id of the the Record to which the related records are to be moved
	 */
	public function transferRelatedRecords($module, $transferEntityIds, $entityId)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$log->debug("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

		$rel_table_arr = Array("PriceBooks" => "vtiger_pricebookproductrel", "Documents" => "vtiger_senotesrel");

		$tbl_field_arr = Array("vtiger_inventoryproductrel" => "id", "vtiger_pricebookproductrel" => "pricebookid", "vtiger_senotesrel" => "notesid");

		$entity_tbl_field_arr = Array("vtiger_inventoryproductrel" => "productid", "vtiger_pricebookproductrel" => "productid", "vtiger_senotesrel" => "crmid");

		foreach ($transferEntityIds as $transferId) {
			foreach ($rel_table_arr as $rel_module => $rel_table) {
				$id_field = $tbl_field_arr[$rel_table];
				$entity_id_field = $entity_tbl_field_arr[$rel_table];
				// IN clause to avoid duplicate entries
				$sel_result = $adb->pquery("select $id_field from $rel_table where $entity_id_field=? " .
					" and $id_field not in (select $id_field from $rel_table where $entity_id_field=?)", array($transferId, $entityId));
				$res_cnt = $adb->num_rows($sel_result);
				if ($res_cnt > 0) {
					for ($i = 0; $i < $res_cnt; $i++) {
						$id_field_value = $adb->query_result($sel_result, $i, $id_field);
						$adb->pquery("update $rel_table set $entity_id_field=? where $entity_id_field=? and $id_field=?", array($entityId, $transferId, $id_field_value));
					}
				}
			}
		}

		parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
		$log->debug("Exiting transferRelatedRecords...");
	}
	/*
	 * Function to get the primary query part of a report
	 * @param - $module primary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */

	public function generateReportsQuery($module, $queryPlanner)
	{
		$current_user = vglobal('current_user');

		$matrix = $queryPlanner->newDependencyMatrix();
		$matrix->setDependency('vtiger_seproductsrel', array('vtiger_crmentityRelServices', 'vtiger_accountRelServices', 'vtiger_leaddetailsRelServices', 'vtiger_servicecf'));
		$query = "from vtiger_service
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_service.serviceid";
		if ($queryPlanner->requireTable("vtiger_servicecf")) {
			$query .= " left join vtiger_servicecf on vtiger_service.serviceid = vtiger_servicecf.serviceid";
		}
		if ($queryPlanner->requireTable("vtiger_usersServices")) {
			$query .= " left join vtiger_users as vtiger_usersServices on vtiger_usersServices.id = vtiger_crmentity.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_groupsServices")) {
			$query .= " left join vtiger_groups as vtiger_groupsServices on vtiger_groupsServices.groupid = vtiger_crmentity.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_seproductsrel")) {
			$query .= " left join vtiger_seproductsrel on vtiger_seproductsrel.productid= vtiger_service.serviceid";
		}
		if ($queryPlanner->requireTable("vtiger_crmentityRelServices")) {
			$query .= " left join vtiger_crmentity as vtiger_crmentityRelServices on vtiger_crmentityRelServices.crmid = vtiger_seproductsrel.crmid and vtiger_crmentityRelServices.deleted = 0";
		}
		if ($queryPlanner->requireTable("vtiger_accountRelServices")) {
			$query .= " left join vtiger_account as vtiger_accountRelServices on vtiger_accountRelServices.accountid=vtiger_seproductsrel.crmid";
		}
		if ($queryPlanner->requireTable("vtiger_leaddetailsRelServices")) {
			$query .= " left join vtiger_leaddetails as vtiger_leaddetailsRelServices on vtiger_leaddetailsRelServices.leadid = vtiger_seproductsrel.crmid";
		}
		if ($queryPlanner->requireTable("vtiger_lastModifiedByServices")) {
			$query .= " left join vtiger_users as vtiger_lastModifiedByServices on vtiger_lastModifiedByServices.id = vtiger_crmentity.modifiedby";
		}
		if ($queryPlanner->requireTable("innerService")) {
			$query .= " LEFT JOIN (
					SELECT vtiger_service.serviceid,
							(CASE WHEN (vtiger_service.currency_id = 1 ) THEN vtiger_service.unit_price
								ELSE (vtiger_service.unit_price / vtiger_currency_info.conversion_rate) END
							) AS actual_unit_price
					FROM vtiger_service
					LEFT JOIN vtiger_currency_info ON vtiger_service.currency_id = vtiger_currency_info.id
					LEFT JOIN vtiger_productcurrencyrel ON vtiger_service.serviceid = vtiger_productcurrencyrel.productid
					AND vtiger_productcurrencyrel.currencyid = " . $current_user->currency_id . "
				) AS innerService ON innerService.serviceid = vtiger_service.serviceid";
		}
		return $query;
	}
	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */

	public function generateReportsSecQuery($module, $secmodule, $queryPlanner)
	{
		$current_user = vglobal('current_user');
		$matrix = $queryPlanner->newDependencyMatrix();
		$matrix->setDependency('vtiger_service', array('actual_unit_price', 'vtiger_currency_info', 'vtiger_productcurrencyrel', 'vtiger_servicecf', 'vtiger_crmentityServices'));
		$matrix->setDependency('vtiger_crmentityServices', array('vtiger_usersServices', 'vtiger_groupsServices', 'vtiger_lastModifiedByServices'));
		if (!$queryPlanner->requireTable("vtiger_service", $matrix)) {
			return '';
		}
		$query = $this->getRelationQuery($module, $secmodule, "vtiger_service", "serviceid", $queryPlanner);
		if ($queryPlanner->requireTable("innerService")) {
			$query .= " LEFT JOIN (
			SELECT vtiger_service.serviceid,
			(CASE WHEN (vtiger_service.currency_id = " . $current_user->currency_id . " ) THEN vtiger_service.unit_price
			WHEN (vtiger_productcurrencyrel.actual_price IS NOT NULL) THEN vtiger_productcurrencyrel.actual_price
			ELSE (vtiger_service.unit_price / vtiger_currency_info.conversion_rate) * " . $current_user->conv_rate . " END
			) AS actual_unit_price FROM vtiger_service
            LEFT JOIN vtiger_currency_info ON vtiger_service.currency_id = vtiger_currency_info.id
            LEFT JOIN vtiger_productcurrencyrel ON vtiger_service.serviceid = vtiger_productcurrencyrel.productid
			AND vtiger_productcurrencyrel.currencyid = " . $current_user->currency_id . ")
            AS innerService ON innerService.serviceid = vtiger_service.serviceid";
		}
		if ($queryPlanner->requireTable("vtiger_crmentityServices", $matrix)) {
			$query .= " left join vtiger_crmentity as vtiger_crmentityServices on vtiger_crmentityServices.crmid=vtiger_service.serviceid and vtiger_crmentityServices.deleted=0";
		}
		if ($queryPlanner->requireTable("vtiger_servicecf")) {
			$query .= " left join vtiger_servicecf on vtiger_service.serviceid = vtiger_servicecf.serviceid";
		}
		if ($queryPlanner->requireTable("vtiger_usersServices")) {
			$query .= " left join vtiger_users as vtiger_usersServices on vtiger_usersServices.id = vtiger_crmentityServices.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_groupsServices")) {
			$query .= " left join vtiger_groups as vtiger_groupsServices on vtiger_groupsServices.groupid = vtiger_crmentityServices.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_lastModifiedByServices")) {
			$query .= " left join vtiger_users as vtiger_lastModifiedByServices on vtiger_lastModifiedByServices.id = vtiger_crmentityServices.modifiedby ";
		}
		if ($queryPlanner->requireTable("vtiger_createdbyServices")) {
			$query .= " left join vtiger_users as vtiger_createdbyServices on vtiger_createdbyServices.id = vtiger_crmentityServices.smcreatorid ";
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
			'PriceBooks' => array('vtiger_pricebookproductrel' => array('productid', 'pricebookid'), 'vtiger_service' => 'serviceid'),
			'Documents' => array('vtiger_senotesrel' => array('crmid', 'notesid'), 'vtiger_service' => 'serviceid'),
		);
		if ($secmodule === false) {
			return $relTables;
		}
		return $relTables[$secmodule];
	}

	// Function to unlink all the dependent entities of the given Entity by Id
	public function unlinkDependencies($module, $id)
	{
		$log = vglobal('log');
		$this->db->pquery('DELETE from vtiger_seproductsrel WHERE productid=? or crmid=?', array($id, $id));

		parent::unlinkDependencies($module, $id);
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	public function vtlib_handler($moduleName, $eventType)
	{

		require_once('include/utils/utils.php');
		$adb = PearDatabase::getInstance();

		if ($eventType == 'module.postinstall') {
			$moduleInstance = vtlib\Module::getInstance($moduleName);
			$moduleInstance->allowSharing();

			$ttModuleInstance = vtlib\Module::getInstance('HelpDesk');
			$ttModuleInstance->setRelatedList($moduleInstance, 'Services', array('select'));

			$leadModuleInstance = vtlib\Module::getInstance('Leads');
			$leadModuleInstance->setRelatedList($moduleInstance, 'Services', array('select'));

			$accModuleInstance = vtlib\Module::getInstance('Accounts');
			$accModuleInstance->setRelatedList($moduleInstance, 'Services', array('select'));

			$conModuleInstance = vtlib\Module::getInstance('Contacts');
			$conModuleInstance->setRelatedList($moduleInstance, 'Services', array('select'));

			$pbModuleInstance = vtlib\Module::getInstance('PriceBooks');
			$pbModuleInstance->setRelatedList($moduleInstance, 'Services', array('select'), 'get_pricebook_services');

			// Initialize module sequence for the module
			\includes\fields\RecordNumber::setNumber($moduleName, 'SER', 1);
			// Mark the module as Standard module
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', array($moduleName));
		} else if ($eventType == 'module.disabled') {

		} else if ($eventType == 'module.enabled') {

		} else if ($eventType == 'module.preuninstall') {

		} else if ($eventType == 'module.preupdate') {

		} else if ($eventType == 'module.postupdate') {
			$ServicesModule = vtlib\Module::getInstance('Services');
			vtlib\Access::setDefaultSharing($ServicesModule);
		}
	}

	/** Function to unlink an entity with given Id from another entity */
	public function unlinkRelationship($id, $return_module, $return_id, $relatedName = false)
	{
		global $currentModule;
		$log = LoggerManager::getInstance();
		$log->fatal('id:--' . $id);
		$log->fatal('return_module:--' . $return_module);
		$log->fatal('return_id:---' . $return_id);
		if ($return_module == 'Accounts') {
			$focus = CRMEntity::getInstance($return_module);
			$entityIds = $focus->getRelatedContactsIds($return_id);
			array_push($entityIds, $return_id);
			$entityIds = implode(',', $entityIds);
			$return_modules = "'Accounts','Contacts'";
		} else {
			$entityIds = $return_id;
			$return_modules = "'" . $return_module . "'";
		}
		if ($relatedName && $relatedName != 'get_related_list') {
			parent::unlinkRelationship($id, $return_module, $return_id, $relatedName);
		} else {
			$where = '(relcrmid= ? && module IN (?) && crmid IN (?)) || (crmid= ? && relmodule IN (?) && relcrmid IN (?))';
			$params = [$id, $return_modules, $entityIds, $id, $return_modules, $entityIds];
			$this->db->delete('vtiger_crmentityrel', $where, $params);
		}
	}

	/**
	 * Function to get Product's related Products
	 * @param  integer   $id      - productid
	 * returns related Products record in array format
	 */
	public function get_services($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_products(" . $id . ") method ...");
		$this_module = $currentModule;

		$related_module = vtlib\Functions::getModuleName($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		if ($singlepane_view == 'true')
			$returnset = '&return_module=' . $this_module . '&return_action=DetailView&return_id=' . $id;
		else
			$returnset = '&return_module=' . $this_module . '&return_action=CallRelatedList&return_id=' . $id;

		$button = '';

		if ($actions && $this->ismember_check() === 0) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />" .
					"<input title='" . \includes\Language::translate('LBL_NEW') . " " . \includes\Language::translate($singular_modname) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";' type='submit' name='button'" .
					" value='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "'>&nbsp;";
			}
		}

		$query = "SELECT vtiger_service.serviceid, vtiger_service.servicename,
			vtiger_service.service_no, vtiger_service.commissionrate,
			vtiger_service.service_usageunit, vtiger_service.unit_price,
			vtiger_crmentity.crmid, vtiger_crmentity.smownerid
			FROM vtiger_service
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_service.serviceid
			INNER JOIN vtiger_servicecf
				ON vtiger_service.serviceid = vtiger_servicecf.serviceid
			LEFT JOIN vtiger_crmentityrel ON vtiger_crmentityrel.relcrmid = vtiger_service.serviceid && vtiger_crmentityrel.module='Services'
			LEFT JOIN vtiger_users
				ON vtiger_users.id=vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			WHERE vtiger_crmentity.deleted = 0 && vtiger_crmentityrel.crmid = $id ";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_products method ...");
		return $return_value;
	}
}

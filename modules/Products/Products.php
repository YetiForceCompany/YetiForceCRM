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

class Products extends CRMEntity
{

	var $db, $log; // Used in class functions of CRMEntity
	var $table_name = 'vtiger_products';
	var $table_index = 'productid';
	var $column_fields = Array();

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_productcf', 'productid');
	var $tab_name = Array('vtiger_crmentity', 'vtiger_products', 'vtiger_productcf');
	var $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_products' => 'productid', 'vtiger_productcf' => 'productid', 'vtiger_seproductsrel' => 'productid', 'vtiger_producttaxrel' => 'productid');
	// This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array(
		'Product Name' => Array('products' => 'productname'),
		'Part Number' => Array('products' => 'productcode'),
		'Commission Rate' => Array('products' => 'commissionrate'),
		'Qty/Unit' => Array('products' => 'qty_per_unit'),
		'Unit Price' => Array('products' => 'unit_price')
	);
	var $list_fields_name = Array(
		'Product Name' => 'productname',
		'Part Number' => 'productcode',
		'Commission Rate' => 'commissionrate',
		'Qty/Unit' => 'qty_per_unit',
		'Unit Price' => 'unit_price'
	);
	var $list_link_field = 'productname';
	var $search_fields = Array(
		'Product Name' => Array('products' => 'productname'),
		'Part Number' => Array('products' => 'productcode'),
		'Unit Price' => Array('products' => 'unit_price')
	);
	var $search_fields_name = Array(
		'Product Name' => 'productname',
		'Part Number' => 'productcode',
		'Unit Price' => 'unit_price'
	);
	var $required_fields = Array(
		'productname' => 1
	);
	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	var $sortby_fields = Array();
	var $def_basicsearch_col = 'productname';
	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = '';
	var $default_sort_order = 'ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('createdtime', 'modifiedtime', 'productname', 'assigned_user_id');
	// Josh added for importing and exporting -added in patch2
	var $unit_price;

	public function save_module($module)
	{
		//Inserting into product_taxrel table
		if (AppRequest::get('ajxaction') != 'DETAILVIEW' && AppRequest::get('action') != 'MassEditSave' && AppRequest::get('action') != 'ProcessDuplicates') {
			$this->insertTaxInformation('vtiger_producttaxrel', 'Products');
			$this->insertPriceInformation('vtiger_productcurrencyrel', 'Products');
		}

		// Update unit price value in vtiger_productcurrencyrel
		$this->updateUnitPrice();
		//Inserting into attachments
		$this->insertIntoAttachment($this->id, 'Products');
	}

	/** 	function to save the product tax information in vtiger_producttaxrel table
	 * 	@param string $tablename - vtiger_tablename to save the product tax relationship (producttaxrel)
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
					$log->debug('Tax selected but value not given so default value will be saved.');
					$tax_per = getTaxPercentage($tax_name);
				}

				$log->debug("Going to save the Product - $tax_name tax relationship");

				$query = 'insert into vtiger_producttaxrel values(?,?,?)';
				$adb->pquery($query, array($this->id, $taxid, $tax_per));
			}
		}

		$log->debug("Exiting from insertTaxInformation($tablename, $module) method ...");
	}

	/** 	function to save the product price information in vtiger_productcurrencyrel table
	 * 	@param string $tablename - vtiger_tablename to save the product currency relationship (productcurrencyrel)
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
		if ($this->mode == 'edit' && AppRequest::get('action') !== 'MassEditSave') {
			for ($i = 0; $i < count($currency_details); $i++) {
				$curid = $currency_details[$i]['curid'];
				$sql = "delete from vtiger_productcurrencyrel where productid=? and currencyid=?";
				$adb->pquery($sql, array($this->id, $curid));
			}
		}

		$product_base_conv_rate = getBaseConversionRateForProduct($this->id, $this->mode);
		$currencySet = 0;
		//Save the Product - Currency relationship if corresponding currency check box is enabled
		for ($i = 0; $i < count($currency_details); $i++) {
			$curid = $currency_details[$i]['curid'];
			$curname = $currency_details[$i]['currencylabel'];
			$cur_checkname = 'cur_' . $curid . '_check';
			$cur_valuename = 'curname' . $curid;

			$requestPrice = CurrencyField::convertToDBFormat(AppRequest::get('unit_price'), null, true);
			$actualPrice = CurrencyField::convertToDBFormat(AppRequest::get($cur_valuename), null, true);
			if (AppRequest::get($cur_valuename) == 'on' || AppRequest::get($cur_valuename) == 1) {
				$conversion_rate = $currency_details[$i]['conversionrate'];
				$actual_conversion_rate = $product_base_conv_rate * $conversion_rate;
				$converted_price = $actual_conversion_rate * $requestPrice;

				$log->debug("Going to save the Product - $curname currency relationship");

				$query = "insert into vtiger_productcurrencyrel values(?,?,?,?)";
				$adb->pquery($query, array($this->id, $curid, $converted_price, $actualPrice));

				// Update the Product information with Base Currency choosen by the User.
				if (AppRequest::get('base_currency') == $cur_valuename) {
					$currencySet = 1;
					$adb->pquery("update vtiger_products set currency_id=?, unit_price=? where productid=?", array($curid, $actualPrice, $this->id));
				}
			}
			if (!$currencySet) {
				$curid = \vtlib\Functions::userCurrencyId($current_user->id);
				$adb->pquery("update vtiger_products set currency_id=? where productid=?", array($curid, $this->id));
			}
		}

		$log->debug("Exiting from insertPriceInformation($tablename, $module) method ...");
	}

	public function updateUnitPrice()
	{
		$prod_res = $this->db->pquery("select unit_price, currency_id from vtiger_products where productid=?", array($this->id));
		$prod_unit_price = $this->db->query_result($prod_res, 0, 'unit_price');
		$prod_base_currency = $this->db->query_result($prod_res, 0, 'currency_id');

		$query = "update vtiger_productcurrencyrel set actual_price=? where productid=? and currencyid=?";
		$params = array($prod_unit_price, $this->id, $prod_base_currency);
		$this->db->pquery($query, $params);
	}

	public function insertIntoAttachment($id, $module)
	{
		$adb = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
		$log->debug("Entering into insertIntoAttachment($id,$module) method.");

		$file_saved = false;
		foreach ($_FILES as $fileindex => $files) {
			$fileInstance = \includes\fields\File::loadFromRequest($files);
			if ($fileInstance->validate('image')) {
				if (AppRequest::get($fileindex . '_hidden') != '')
					$files['original_name'] = AppRequest::get($fileindex . '_hidden');
				else
					$files['original_name'] = stripslashes($files['name']);
				$files['original_name'] = str_replace('"', '', $files['original_name']);
				$file_saved = $this->uploadAndSaveFile($id, $module, $files);
			}
		}

		//Updating image information in main table of products
		$existingImageSql = 'SELECT name FROM vtiger_seattachmentsrel INNER JOIN vtiger_attachments ON
								vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid LEFT JOIN vtiger_products ON
								vtiger_products.productid = vtiger_seattachmentsrel.crmid WHERE vtiger_seattachmentsrel.crmid = ?';
		$existingImages = $adb->pquery($existingImageSql, array($id));
		$numOfRows = $adb->num_rows($existingImages);
		$productImageMap = array();

		for ($i = 0; $i < $numOfRows; $i++) {
			$imageName = $adb->query_result($existingImages, $i, "name");
			array_push($productImageMap, decode_html($imageName));
		}
		$commaSeperatedFileNames = implode(",", $productImageMap);

		$adb->pquery('UPDATE vtiger_products SET imagename = ? WHERE productid = ?', array($commaSeperatedFileNames, $id));

		//Remove the deleted vtiger_attachments from db - Products
		if ($module == 'Products' && AppRequest::get('del_file_list') != '') {
			$del_file_list = explode("###", trim(AppRequest::get('del_file_list'), "###"));
			foreach ($del_file_list as $del_file_name) {
				$attach_res = $adb->pquery("select vtiger_attachments.attachmentsid from vtiger_attachments inner join vtiger_seattachmentsrel on vtiger_attachments.attachmentsid=vtiger_seattachmentsrel.attachmentsid where crmid=? and name=?", array($id, $del_file_name));
				$attachments_id = $adb->query_result($attach_res, 0, 'attachmentsid');

				$del_res1 = $adb->pquery("delete from vtiger_attachments where attachmentsid=?", array($attachments_id));
				$del_res2 = $adb->pquery("delete from vtiger_seattachmentsrel where attachmentsid=?", array($attachments_id));
			}
		}

		$log->debug("Exiting from insertIntoAttachment($id,$module) method.");
	}

	/** 	function used to get the list of leads which are related to the product
	 * 	@param int $id - product id
	 * 	@return array - array which will be returned from the function GetRelatedList
	 */
	public function get_leads($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_leads(" . $id . ") method ...");
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

		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "'>&nbsp;";
			}
		}

		$query = sprintf('SELECT vtiger_leaddetails.leadid, vtiger_crmentity.crmid, vtiger_leaddetails.firstname, vtiger_leaddetails.lastname, vtiger_leaddetails.company, vtiger_leadaddress.phone, vtiger_leadsubdetails.website, vtiger_leaddetails.email, case when (vtiger_users.user_name not like \"\") then vtiger_users.user_name else vtiger_groups.groupname end as user_name, vtiger_crmentity.smownerid, vtiger_products.productname, vtiger_products.qty_per_unit, vtiger_products.unit_price, vtiger_products.expiry_date
			FROM vtiger_leaddetails
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
			INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
			INNER JOIN vtiger_leadsubdetails ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid
			INNER JOIN vtiger_seproductsrel ON vtiger_seproductsrel.crmid=vtiger_leaddetails.leadid
			INNER JOIN vtiger_products ON vtiger_seproductsrel.productid = vtiger_products.productid
			INNER JOIN vtiger_leadscf ON vtiger_leaddetails.leadid = vtiger_leadscf.leadid
			LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			WHERE vtiger_crmentity.deleted = 0 && vtiger_products.productid = %s', $id);

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_leads method ...");
		return $return_value;
	}

	/** 	function used to get the list of accounts which are related to the product
	 * 	@param int $id - product id
	 * 	@return array - array which will be returned from the function GetRelatedList
	 */
	public function get_accounts($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_accounts(" . $id . ") method ...");
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

		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "'>&nbsp;";
			}
		}

		$query = sprintf('SELECT vtiger_account.accountid, vtiger_crmentity.crmid, vtiger_account.accountname, vtiger_account.website, vtiger_account.phone, case when (vtiger_users.user_name not like \"\") then vtiger_users.user_name else vtiger_groups.groupname end as user_name, vtiger_crmentity.smownerid, vtiger_products.productname, vtiger_products.qty_per_unit, vtiger_products.unit_price, vtiger_products.expiry_date
			FROM vtiger_account
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_account.accountid
			INNER JOIN vtiger_accountaddress ON vtiger_accountaddress.accountaddressid = vtiger_account.accountid
			INNER JOIN vtiger_seproductsrel ON vtiger_seproductsrel.crmid=vtiger_account.accountid
			INNER JOIN vtiger_products ON vtiger_seproductsrel.productid = vtiger_products.productid
			INNER JOIN vtiger_accountscf ON vtiger_account.accountid = vtiger_accountscf.accountid
			LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			WHERE vtiger_crmentity.deleted = 0 && vtiger_products.productid = %s', $id);

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_accounts method ...");
		return $return_value;
	}

	/** 	function used to get the list of contacts which are related to the product
	 * 	@param int $id - product id
	 * 	@return array - array which will be returned from the function GetRelatedList
	 */
	public function get_contacts($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_contacts(" . $id . ") method ...");
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

		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "'>&nbsp;";
			}
		}

		$query = sprintf('SELECT vtiger_contactdetails.firstname, vtiger_contactdetails.lastname, vtiger_contactdetails.title, vtiger_contactdetails.parentid, vtiger_contactdetails.email, vtiger_contactdetails.phone, vtiger_crmentity.crmid, case when (vtiger_users.user_name not like \"\") then vtiger_users.user_name else vtiger_groups.groupname end as user_name, vtiger_crmentity.smownerid, vtiger_products.productname, vtiger_products.qty_per_unit, vtiger_products.unit_price, vtiger_products.expiry_date,vtiger_account.accountname
			FROM vtiger_contactdetails
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
			INNER JOIN vtiger_seproductsrel ON vtiger_seproductsrel.crmid=vtiger_contactdetails.contactid
			INNER JOIN vtiger_contactaddress ON vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid
			INNER JOIN vtiger_contactsubdetails ON vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid
			INNER JOIN vtiger_customerdetails ON vtiger_contactdetails.contactid = vtiger_customerdetails.customerid
			INNER JOIN vtiger_contactscf ON vtiger_contactdetails.contactid = vtiger_contactscf.contactid
			INNER JOIN vtiger_products ON vtiger_seproductsrel.productid = vtiger_products.productid
			LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.parentid
			WHERE vtiger_crmentity.deleted = 0 && vtiger_products.productid = %s', $id);

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_contacts method ...");
		return $return_value;
	}

	/** 	function used to get the list of tickets which are related to the product
	 * 	@param int $id - product id
	 * 	@return array - array which will be returned from the function GetRelatedList
	 */
	public function get_tickets($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_tickets(" . $id . ") method ...");
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

		if ($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'product_id', 'readwrite') == '0') {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "'>&nbsp;";
			}
		}

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' =>
				'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT  case when (vtiger_users.user_name not like \"\") then $userNameSql else vtiger_groups.groupname end as user_name, vtiger_users.id,
			vtiger_products.productid, vtiger_products.productname,
			vtiger_troubletickets.ticketid,
			vtiger_troubletickets.parent_id, vtiger_troubletickets.title,
			vtiger_troubletickets.status, vtiger_troubletickets.priority,
			vtiger_crmentity.crmid, vtiger_crmentity.smownerid,
			vtiger_crmentity.modifiedtime, vtiger_troubletickets.ticket_no
			FROM vtiger_troubletickets
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_troubletickets.ticketid
			LEFT JOIN vtiger_products
				ON vtiger_products.productid = vtiger_troubletickets.product_id
			LEFT JOIN vtiger_ticketcf ON vtiger_troubletickets.ticketid = vtiger_ticketcf.ticketid
			LEFT JOIN vtiger_users
				ON vtiger_users.id = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_products.productid = " . $id;

		$log->debug("Exiting get_tickets method ...");

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_tickets method ...");
		return $return_value;
	}

	/** 	function used to get the list of pricebooks which are related to the product
	 * 	@param int $id - product id
	 * 	@return array - array which will be returned from the function GetRelatedList
	 */
	public function get_product_pricebooks($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		global $singlepane_view, $currentModule;
		$log = LoggerManager::getInstance();
		$log->debug("Entering get_product_pricebooks(" . $id . ") method ...");

		$related_module = vtlib\Functions::getModuleName($rel_tab_id);
		\vtlib\Deprecated::checkFileAccessForInclusion("modules/$related_module/$related_module.php");
		require_once("modules/$related_module/$related_module.php");
		$focus = new $related_module();
		$singular_modname = vtlib_toSingular($related_module);

		$button = '';
		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes' && isPermitted($currentModule, 'EditView', $id) == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_ADD_TO') . " " . \includes\Language::translate($related_module) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"AddProductToPriceBooks\";this.form.module.value=\"$currentModule\"' type='submit' name='button'" .
					" value='" . \includes\Language::translate('LBL_ADD_TO') . " " . \includes\Language::translate($related_module) . "'>&nbsp;";
			}
		}

		if ($singlepane_view == 'true')
			$returnset = '&return_module=Products&return_action=DetailView&return_id=' . $id;
		else
			$returnset = '&return_module=Products&return_action=CallRelatedList&return_id=' . $id;


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

		return $return_value;
	}

	/** 	function used to get the number of vendors which are related to the product
	 * 	@param int $id - product id
	 * 	@return int number of rows - return the number of products which do not have relationship with vendor
	 */
	public function product_novendor()
	{
		$log = vglobal('log');
		$log->debug("Entering product_novendor() method ...");
		$query = "SELECT vtiger_products.productname, vtiger_crmentity.deleted
			FROM vtiger_products
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_products.productid
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_products.vendor_id is NULL";
		$result = $this->db->pquery($query, array());
		$log->debug("Exiting product_novendor method ...");
		return $this->db->num_rows($result);
	}

	/**
	 * Function to get Product's related Products
	 * @param  integer   $id      - productid
	 * returns related Products record in array format
	 */
	public function get_products($id, $cur_tab_id, $rel_tab_id, $actions = false)
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

		$query = "SELECT vtiger_products.productid, vtiger_products.productname,
			vtiger_products.productcode, vtiger_products.commissionrate,
			vtiger_products.qty_per_unit, vtiger_products.unit_price,
			vtiger_crmentity.crmid, vtiger_crmentity.smownerid
			FROM vtiger_products
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_products.productid
			INNER JOIN vtiger_productcf
				ON vtiger_products.productid = vtiger_productcf.productid
			LEFT JOIN vtiger_seproductsrel ON vtiger_seproductsrel.crmid = vtiger_products.productid && vtiger_seproductsrel.setype='Products'
			LEFT JOIN vtiger_users
				ON vtiger_users.id=vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			WHERE vtiger_crmentity.deleted = 0 && vtiger_seproductsrel.productid = $id ";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_products method ...");
		return $return_value;
	}

	/**
	 * Function to get Product's related Products
	 * @param  integer   $id      - productid
	 * returns related Products record in array format
	 */
	public function get_parent_products($id)
	{
		global $singlepane_view;
		$log = LoggerManager::getInstance();
		$log->debug("Entering get_products(" . $id . ") method ...");

		$focus = new Products();

		$button = '';

		if (isPermitted("Products", 1, "") == 'yes') {
			$button .= '<input title="' . \includes\Language::translate('LBL_NEW_PRODUCT') . '" accessyKey="F" class="button" onclick="this.form.action.value=\'EditView\';this.form.module.value=\'Products\';this.form.return_module.value=\'Products\';this.form.return_action.value=\'DetailView\'" type="submit" name="button" value="' . \includes\Language::translate('LBL_NEW_PRODUCT') . '">&nbsp;';
		}
		if ($singlepane_view == 'true')
			$returnset = '&return_module=Products&return_action=DetailView&is_parent=1&return_id=' . $id;
		else
			$returnset = '&return_module=Products&return_action=CallRelatedList&is_parent=1&return_id=' . $id;

		$query = "SELECT vtiger_products.productid, vtiger_products.productname,
			vtiger_products.productcode, vtiger_products.commissionrate,
			vtiger_products.qty_per_unit, vtiger_products.unit_price,
			vtiger_crmentity.crmid, vtiger_crmentity.smownerid
			FROM vtiger_products
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_products.productid
			INNER JOIN vtiger_seproductsrel ON vtiger_seproductsrel.productid = vtiger_products.productid && vtiger_seproductsrel.setype='Products'
			INNER JOIN vtiger_productcf ON vtiger_products.productid = vtiger_productcf.productid

			WHERE vtiger_crmentity.deleted = 0 && vtiger_seproductsrel.crmid = $id ";

		$log->debug("Exiting get_products method ...");
		return GetRelatedList('Products', 'Products', $focus, $query, $button, $returnset);
	}

	/** 	function used to get the export query for product
	 * 	@param reference $where - reference of the where variable which will be added with the query
	 * 	@return string $query - return the query which will give the list of products to export
	 */
	public function create_export_query($where)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$log->debug("Entering create_export_query(" . $where . ") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Products", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list FROM " . $this->table_name . "
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_products.productid
			LEFT JOIN vtiger_productcf
				ON vtiger_products.productid = vtiger_productcf.productid
			LEFT JOIN vtiger_vendor
				ON vtiger_vendor.vendorid = vtiger_products.vendor_id";

		$query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";
		$query .= " LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id && vtiger_users.status='Active'";
		$query .= $this->getNonAdminAccessControlQuery('Products', $current_user);
		$where_auto = " vtiger_crmentity.deleted=0";

		if ($where != '')
			$query .= " WHERE ($where) && $where_auto";
		else
			$query .= " WHERE $where_auto";

		$log->debug("Exiting create_export_query method ...");
		return $query;
	}

	/** Function to check if the product is parent of any other product
	 */
	public function isparent_check()
	{
		$adb = PearDatabase::getInstance();
		$isparent_query = $adb->pquery(getListQuery("Products") . " && (vtiger_products.productid IN (SELECT productid from vtiger_seproductsrel WHERE vtiger_seproductsrel.productid = ? && vtiger_seproductsrel.setype='Products'))", array($this->id));
		$isparent = $adb->num_rows($isparent_query);
		return $isparent;
	}

	/** Function to check if the product is member of other product
	 */
	public function ismember_check()
	{
		$adb = PearDatabase::getInstance();
		$ismember_query = $adb->pquery(getListQuery("Products") . " && (vtiger_products.productid IN (SELECT crmid from vtiger_seproductsrel WHERE vtiger_seproductsrel.crmid = ? && vtiger_seproductsrel.setype='Products'))", array($this->id));
		$ismember = $adb->num_rows($ismember_query);
		return $ismember;
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

		$rel_table_arr = Array("HelpDesk" => "vtiger_troubletickets", "Products" => "vtiger_seproductsrel", "Attachments" => "vtiger_seattachmentsrel",
			"PriceBooks" => "vtiger_pricebookproductrel", "Leads" => "vtiger_seproductsrel",
			"Accounts" => "vtiger_seproductsrel", "Contacts" => "vtiger_seproductsrel",
			"Documents" => "vtiger_senotesrel", 'Assets' => 'vtiger_assets',);

		$tbl_field_arr = Array("vtiger_troubletickets" => "ticketid", "vtiger_seproductsrel" => "crmid", "vtiger_seattachmentsrel" => "attachmentsid",
			"vtiger_inventoryproductrel" => "id", "vtiger_pricebookproductrel" => "pricebookid", "vtiger_seproductsrel" => "crmid",
			"vtiger_senotesrel" => "notesid", 'vtiger_assets' => 'assetsid');

		$entity_tbl_field_arr = Array("vtiger_troubletickets" => "product_id", "vtiger_seproductsrel" => "crmid", "vtiger_seattachmentsrel" => "crmid",
			"vtiger_inventoryproductrel" => "productid", "vtiger_pricebookproductrel" => "productid", "vtiger_seproductsrel" => "productid",
			"vtiger_senotesrel" => "crmid", 'vtiger_assets' => 'product');

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
		$log->debug("Exiting transferRelatedRecords...");
	}
	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */

	public function generateReportsSecQuery($module, $secmodule, $queryplanner)
	{
		$current_user = vglobal('current_user');
		$matrix = $queryplanner->newDependencyMatrix();

		$matrix->setDependency("vtiger_crmentityProducts", array("vtiger_groupsProducts", "vtiger_usersProducts", "vtiger_lastModifiedByProducts"));
		$matrix->setDependency("vtiger_products", array("innerProduct", "vtiger_crmentityProducts", "vtiger_productcf", "vtiger_vendorRelProducts"));
		//query planner Support  added
		if (!$queryplanner->requireTable('vtiger_products', $matrix)) {
			return '';
		}
		$query = $this->getRelationQuery($module, $secmodule, "vtiger_products", "productid", $queryplanner);
		if ($queryplanner->requireTable("innerProduct")) {
			$query .= " LEFT JOIN (
				    SELECT vtiger_products.productid,
						    (CASE WHEN (vtiger_products.currency_id = 1 ) THEN vtiger_products.unit_price
							    ELSE (vtiger_products.unit_price / vtiger_currency_info.conversion_rate) END
						    ) AS actual_unit_price
				    FROM vtiger_products
				    LEFT JOIN vtiger_currency_info ON vtiger_products.currency_id = vtiger_currency_info.id
				    LEFT JOIN vtiger_productcurrencyrel ON vtiger_products.productid = vtiger_productcurrencyrel.productid
				    && vtiger_productcurrencyrel.currencyid = " . $current_user->currency_id . "
			    ) AS innerProduct ON innerProduct.productid = vtiger_products.productid";
		}
		if ($queryplanner->requireTable("vtiger_crmentityProducts")) {
			$query .= " left join vtiger_crmentity as vtiger_crmentityProducts on vtiger_crmentityProducts.crmid=vtiger_products.productid and vtiger_crmentityProducts.deleted=0";
		}
		if ($queryplanner->requireTable("vtiger_productcf")) {
			$query .= " left join vtiger_productcf on vtiger_products.productid = vtiger_productcf.productid";
		}
		if ($queryplanner->requireTable("vtiger_groupsProducts")) {
			$query .= " left join vtiger_groups as vtiger_groupsProducts on vtiger_groupsProducts.groupid = vtiger_crmentityProducts.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_usersProducts")) {
			$query .= " left join vtiger_users as vtiger_usersProducts on vtiger_usersProducts.id = vtiger_crmentityProducts.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_vendorRelProducts")) {
			$query .= " left join vtiger_vendor as vtiger_vendorRelProducts on vtiger_vendorRelProducts.vendorid = vtiger_products.vendor_id";
		}
		if ($queryplanner->requireTable("vtiger_lastModifiedByProducts")) {
			$query .= " left join vtiger_users as vtiger_lastModifiedByProducts on vtiger_lastModifiedByProducts.id = vtiger_crmentityProducts.modifiedby ";
		}
		if ($queryplanner->requireTable("vtiger_createdbyProducts")) {
			$query .= " left join vtiger_users as vtiger_createdbyProducts on vtiger_createdbyProducts.id = vtiger_crmentityProducts.smcreatorid ";
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
			'HelpDesk' => array('vtiger_troubletickets' => array('product_id', 'ticketid'), 'vtiger_products' => 'productid'),
			'Quotes' => array('vtiger_inventoryproductrel' => array('productid', 'id'), 'vtiger_products' => 'productid'),
			'Leads' => array('vtiger_seproductsrel' => array('productid', 'crmid'), 'vtiger_products' => 'productid'),
			'Accounts' => array('vtiger_seproductsrel' => array('productid', 'crmid'), 'vtiger_products' => 'productid'),
			'Contacts' => array('vtiger_seproductsrel' => array('productid', 'crmid'), 'vtiger_products' => 'productid'),
			'PriceBooks' => array('vtiger_pricebookproductrel' => array('productid', 'pricebookid'), 'vtiger_products' => 'productid'),
			'Documents' => array('vtiger_senotesrel' => array('crmid', 'notesid'), 'vtiger_products' => 'productid'),
		);
		if ($secmodule === false) {
			return $relTables;
		}
		return $relTables[$secmodule];
	}

	public function deleteProduct2ProductRelation($record, $return_id, $is_parent)
	{
		$adb = PearDatabase::getInstance();
		if ($is_parent == 0) {
			$sql = "delete from vtiger_seproductsrel WHERE crmid = ? && productid = ?";
			$adb->pquery($sql, array($record, $return_id));
		} else {
			$sql = "delete from vtiger_seproductsrel WHERE crmid = ? && productid = ?";
			$adb->pquery($sql, array($return_id, $record));
		}
	}

	// Function to unlink all the dependent entities of the given Entity by Id
	public function unlinkDependencies($module, $id)
	{
		$log = vglobal('log');
		//Backup Campaigns-Product Relation
		$cmp_q = 'SELECT campaignid FROM vtiger_campaign WHERE product_id = ?';
		$cmp_res = $this->db->pquery($cmp_q, array($id));
		if ($this->db->num_rows($cmp_res) > 0) {
			$cmp_ids_list = array();
			for ($k = 0; $k < $this->db->num_rows($cmp_res); $k++) {
				$cmp_ids_list[] = $this->db->query_result($cmp_res, $k, "campaignid");
			}
			$params = array($id, RB_RECORD_UPDATED, 'vtiger_campaign', 'product_id', 'campaignid', implode(",", $cmp_ids_list));
			$this->db->pquery('INSERT INTO vtiger_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
		}
		//we have to update the product_id as null for the campaigns which are related to this product
		$this->db->pquery('UPDATE vtiger_campaign SET product_id=0 WHERE product_id = ?', array($id));

		$this->db->pquery('DELETE from vtiger_seproductsrel WHERE productid=? or crmid=?', array($id, $id));

		parent::unlinkDependencies($module, $id);
	}

	// Function to unlink an entity with given Id from another entity
	public function unlinkRelationship($id, $return_module, $return_id, $relatedName = false)
	{
		$log = vglobal('log');
		if (empty($return_module) || empty($return_id))
			return;

		if ($return_module == 'Leads' || $return_module == 'Contacts') {
			$sql = 'DELETE FROM vtiger_seproductsrel WHERE productid = ? && crmid = ?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif ($return_module == 'Vendors') {
			$sql = 'UPDATE vtiger_products SET vendor_id = ? WHERE productid = ?';
			$this->db->pquery($sql, array(null, $id));
		} elseif ($return_module == 'Accounts') {
			$sql = 'DELETE FROM vtiger_seproductsrel WHERE productid = ? && (crmid = ? || crmid IN (SELECT contactid FROM vtiger_contactdetails WHERE parentid=?))';
			$param = array($id, $return_id, $return_id);
			$this->db->pquery($sql, $param);
		} else {
			parent::unlinkRelationship($id, $return_module, $return_id, $relatedName);
		}
	}

	public function save_related_module($module, $crmid, $with_module, $with_crmids, $relatedName = false)
	{
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();

		if (!is_array($with_crmids))
			$with_crmids = Array($with_crmids);
		foreach ($with_crmids as $with_crmid) {
			if ($with_module == 'Leads' || $with_module == 'Accounts' ||
				$with_module == 'Contacts' || $with_module == 'Products') {
				$query = $db->pquery("SELECT * from vtiger_seproductsrel WHERE crmid=? and productid=?", array($crmid, $with_crmid));
				if ($db->getRowCount($query) == 0) {
					$db->insert('vtiger_seproductsrel', [
						'crmid' => $with_crmid,
						'productid' => $crmid,
						'setype' => $with_module,
						'rel_created_user' => $currentUser->getId(),
						'rel_created_time' => date('Y-m-d H:i:s')
					]);
				}
			} else {
				parent::save_related_module($module, $crmid, $with_module, $with_crmid, $relatedName);
			}
		}
	}
}

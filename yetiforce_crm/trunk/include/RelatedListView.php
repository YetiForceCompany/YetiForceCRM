<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 ********************************************************************************/


require_once('include/utils/UserInfoUtil.php');
require_once("include/utils/utils.php");
require_once("include/ListView/ListViewSession.php");
require_once("include/ListView/RelatedListViewSession.php");

if(!function_exists('GetRelatedList')) {
	function GetRelatedList($module,$relatedmodule,$focus,$query,$button,$returnset,$id='',
			$edit_val='',$del_val='',$skipActions=false) {
		return GetRelatedListBase($module, $relatedmodule, $focus, $query, $button, $returnset, $id, $edit_val, $del_val, $skipActions);
	}
}

if(!function_exists('GetHistory')) {
	function GetHistory($parentmodule,$query,$id) {
		return GetHistoryBase($parentmodule,$query,$id);
	}
}

/** Function to get related list entries in detailed array format
  * @param $module -- modulename:: Type string
  * @param $relatedmodule -- relatedmodule:: Type string
  * @param $focus -- focus:: Type object
  * @param $query -- query:: Type string
  * @param $button -- buttons:: Type string
  * @param $returnset -- returnset:: Type string
  * @param $id -- id:: Type string
  * @param $edit_val -- edit value:: Type string
  * @param $del_val -- delete value:: Type string
  * @returns $related_entries -- related entires:: Type string array
  *
  */

function GetRelatedListBase($module,$relatedmodule,$focus,$query,$button,$returnset,$id='',$edit_val='',$del_val='',$skipActions=false)
{

}

/** Function to get related list entries in detailed array format
  * @param $parentmodule -- parentmodulename:: Type string
  * @param $query -- query:: Type string
  * @param $id -- id:: Type string
  * @returns $return_data -- return data:: Type string array
  *
  */

function GetHistoryBase($parentmodule,$query,$id)
{


}

/**	Function to display the Products which are related to the PriceBook
 *	@param string $query - query to get the list of products which are related to the current PriceBook
 *	@param object $focus - PriceBook object which contains all the information of the current PriceBook
 *	@param string $returnset - return_module, return_action and return_id which are sequenced with & to pass to the URL which is optional
 *	return array $return_data which will be formed like array('header'=>$header,'entries'=>$entries_list) where as $header contains all the header columns and $entries_list will contain all the Product entries
 */
function getPriceBookRelatedProducts($query,$focus,$returnset='')
{
	global $log;
	$log->debug("Entering getPriceBookRelatedProducts(".$query.",".get_class($focus).",".$returnset.") method ...");

	global $adb;
	global $app_strings;
	global $mod_strings;
	global $current_language,$current_user;
	$current_module_strings = return_module_language($current_language, 'PriceBook');
    $no_of_decimal_places = getCurrencyDecimalPlaces();
	global $list_max_entries_per_page;
	global $urlPrefix;

	global $theme;
	$pricebook_id = vtlib_purify($_REQUEST['record']);
	$theme_path="themes/".$theme."/";
	$image_path=$theme_path."images/";

	$computeCount = $_REQUEST['withCount'];
	if(PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false) === true ||
			((boolean) $computeCount) == true){
		$noofrows = $adb->query_result($adb->query(Vtiger_Functions::mkCountQuery($query)),0,'count');
	}else{
		$noofrows = null;
	}

	$module = 'PriceBooks';
	$relatedmodule = 'Products';
	if(!$_SESSION['rlvs'][$module][$relatedmodule])
	{
		$modObj = new ListViewSession();
		$modObj->sortby = $focus->default_order_by;
		$modObj->sorder = $focus->default_sort_order;
		$_SESSION['rlvs'][$module][$relatedmodule] = get_object_vars($modObj);
	}


	if(isset($_REQUEST['relmodule']) && $_REQUEST['relmodule']!='' && $_REQUEST['relmodule'] == $relatedmodule) {
		$relmodule = vtlib_purify($_REQUEST['relmodule']);
		if($_SESSION['rlvs'][$module][$relmodule]) {
			setSessionVar($_SESSION['rlvs'][$module][$relmodule],$noofrows,$list_max_entries_per_page,$module,$relmodule);
		}
	}
	global $relationId;
	$start = RelatedListViewSession::getRequestCurrentPage($relationId, $query);
	$navigation_array =  VT_getSimpleNavigationValues($start, $list_max_entries_per_page,
			$noofrows);

	$limit_start_rec = ($start-1) * $list_max_entries_per_page;

	if( $adb->dbType == "pgsql")
		$list_result = $adb->pquery($query.
				" OFFSET $limit_start_rec LIMIT $list_max_entries_per_page", array());
	else
		$list_result = $adb->pquery($query.
				" LIMIT $limit_start_rec, $list_max_entries_per_page", array());

	$header=array();
	$header[]=$mod_strings['LBL_LIST_PRODUCT_NAME'];
	if(getFieldVisibilityPermission('Products', $current_user->id, 'productcode') == '0')
		$header[]=$mod_strings['LBL_PRODUCT_CODE'];
	if(getFieldVisibilityPermission('Products', $current_user->id, 'unit_price') == '0')
		$header[]=$mod_strings['LBL_PRODUCT_UNIT_PRICE'];
	$header[]=$mod_strings['LBL_PB_LIST_PRICE'];
	if(isPermitted("PriceBooks","EditView","") == 'yes' || isPermitted("PriceBooks","Delete","") == 'yes')
		$header[]=$mod_strings['LBL_ACTION'];

	$currency_id = $focus->column_fields['currency_id'];
	$numRows = $adb->num_rows($list_result);
	for($i=0; $i<$numRows; $i++) {
		$entity_id = $adb->query_result($list_result,$i,"crmid");
		$unit_price = 	$adb->query_result($list_result,$i,"unit_price");
		if($currency_id != null) {
			$prod_prices = getPricesForProducts($currency_id, array($entity_id));
			$unit_price = $prod_prices[$entity_id];
		}
		$listprice = $adb->query_result($list_result,$i,"listprice");
		$field_name=$entity_id."_listprice";

		$entries = Array();
		$entries[] = textlength_check($adb->query_result($list_result,$i,"productname"));
		if(getFieldVisibilityPermission('Products', $current_user->id, 'productcode') == '0')
			$entries[] = $adb->query_result($list_result,$i,"productcode");
		if(getFieldVisibilityPermission('Products', $current_user->id, 'unit_price') == '0')
			$entries[] = CurrencyField::convertToUserFormat($unit_price, null, true);

		$entries[] = CurrencyField::convertToUserFormat($listprice, null, true);
		$action = "";
		if(isPermitted("PriceBooks","EditView","") == 'yes' && isPermitted('Products', 'EditView', $entity_id) == 'yes') {
			$action .= '<img style="cursor:pointer;" src="'. vtiger_imageurl('editfield.gif', $theme).'" border="0" onClick="fnvshobj(this,\'editlistprice\'),editProductListPrice(\''.$entity_id.'\',\''.$pricebook_id.'\',\''.number_format($listprice, $no_of_decimal_places,'.','').'\')" alt="'.$app_strings["LBL_EDIT_BUTTON"].'" title="'.$app_strings["LBL_EDIT_BUTTON"].'"/>';
		} else {
			$action .= '<img src="'. vtiger_imageurl('blank.gif', $theme).'" border="0" />';
		}
		if(isPermitted("PriceBooks","Delete","") == 'yes' && isPermitted('Products', 'Delete', $entity_id) == 'yes') {
			if($action != "")
				$action .= '&nbsp;|&nbsp;';
			$action .= '<img src="'. vtiger_imageurl('delete.gif', $theme).'" onclick="if(confirm(\''.$app_strings['ARE_YOU_SURE'].'\')) deletePriceBookProductRel('.$entity_id.','.$pricebook_id.');" alt="'.$app_strings["LBL_DELETE"].'" title="'.$app_strings["LBL_DELETE"].'" style="cursor:pointer;" border="0">';
		}
		if($action != "")
			$entries[] = $action;
		$entries_list[] = $entries;
	}
	$navigationOutput[] =  getRecordRangeMessage($list_result, $limit_start_rec,$noofrows);
	$navigationOutput[] = getRelatedTableHeaderNavigation($navigation_array, '',$module,
			$relatedmodule,$focus->id);
	$return_data = array('header'=>$header,'entries'=>$entries_list,'navigation'=>$navigationOutput);

	$log->debug("Exiting getPriceBookRelatedProducts method ...");
	return $return_data;
}

function CheckFieldPermission($fieldname,$module) {
	global $current_user,$adb;
	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	if($fieldname == '' || $module == '') {
		return "false";
	}

	if(getFieldVisibilityPermission($module, $current_user->id, $fieldname) == '0') {
		return "true";
	}
	return "false";
}

function CheckColumnPermission($tablename, $columnname, $module)
{
	global $adb;
	
	static $cache = array();
	
	$cachekey = $module . ":" . $tablename . ":" . $columnname;
	if (!array_key_exists($cachekey, $cache)) {
		$res = $adb->pquery("select fieldname from vtiger_field where tablename=? and columnname=? and vtiger_field.presence in (0,2)", array($tablename, $columnname));
		$fieldname = $adb->query_result($res, 0, 'fieldname');
		$cache[$cachekey] = CheckFieldPermission($fieldname, $module);
	}

	return $cache[$cachekey];
}
?>
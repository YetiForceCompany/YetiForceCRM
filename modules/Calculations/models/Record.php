<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

/**
 * Calculations Record Model Class
 */
class Calculations_Record_Model extends Inventory_Record_Model {
	function getHierarchy() {
		$focus = CRMEntity::getInstance($this->getModuleName());
		$hierarchy = $focus->getHierarchy($this->getId());
		$i=0;
		foreach($hierarchy['entries'] as $rowtId => $rowInfo) {
			preg_match('/<a href="+/', $rowInfo[0], $matches);
			if($matches != null) {
				preg_match('/[.\s]+/', $rowInfo[0], $dashes);
				preg_match("/<a(.*)>(.*)<\/a>/i",$rowInfo[0], $name);

				$recordModel = Vtiger_Record_Model::getCleanInstance('Calculations');
				$recordModel->setId($rowtId);
				$hierarchy['entries'][$rowtId][0] = $dashes[0]."<a href=".$recordModel->getDetailViewUrl().">".$name[2]."</a>";
			}
		}
		return $hierarchy;
	}
	/**
	 * This Function adds the specified product quantity to the Product Quantity in Stock
	 * @param type $recordId
	 */
	function addStockToProducts($recordId) {
		$db = PearDatabase::getInstance();

		$recordModel = Inventory_Record_Model::getInstanceById($recordId);
		$relatedProducts = $recordModel->getProducts();

		foreach ($relatedProducts as $key => $relatedProduct) {
			if($relatedProduct['qty'.$key]){
				$productId = $relatedProduct['hdnProductId'.$key];
				$result = $db->pquery("SELECT qtyinstock FROM vtiger_products WHERE productid=?", array($productId));
				$qty = $db->query_result($result,0,"qtyinstock");
				$stock = $qty + $relatedProduct['qty'.$key];
				$db->pquery("UPDATE vtiger_products SET qtyinstock=? WHERE productid=?", array($stock, $productId));
			}
		}
	}
	function getCurrencyInfo() {
		$moduleName = $this->getModuleName();
		$currencyInfo = $this->getInventoryCurrencyInfo($moduleName, $this->getId());
		return $currencyInfo;
	}
	function getInventoryCurrencyInfo($module, $id)	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');

		$log->debug("Entering into function getInventoryCurrencyInfo($module, $id).");

		$focus = new $module();

		$res = $adb->pquery("select currency_id, {$focus->table_name}.conversion_rate as conv_rate, vtiger_currency_info.* from {$focus->table_name} "
			. "inner join vtiger_currency_info on {$focus->table_name}.currency_id = vtiger_currency_info.id where {$focus->table_index}=?", array($id), true);

		$currency_info = array();
		$currency_info['currency_id'] = $adb->query_result($res, 0, 'currency_id');
		$currency_info['conversion_rate'] = $adb->query_result($res, 0, 'conv_rate');
		$currency_info['currency_name'] = $adb->query_result($res, 0, 'currency_name');
		$currency_info['currency_code'] = $adb->query_result($res, 0, 'currency_code');
		$currency_info['currency_symbol'] = $adb->query_result($res, 0, 'currency_symbol');

		$log->debug("Exit from function getInventoryCurrencyInfo($module, $id).");

		return $currency_info;
	}
	function getInventoryTaxType($module, $id)	{
		return '';
	}
	function getProductTaxes() {
		return '';
	}
	function getProducts() {
		$numOfCurrencyDecimalPlaces = getCurrencyDecimalPlaces();
		$relatedProducts = $this->getAssociatedProducts($this->getModuleName(), $this->getEntity());
		$relatedProducts[1]['final_details']['grandTotal'] = number_format($this->get('hdnGrandTotal'), $numOfCurrencyDecimalPlaces,'.','');
		$relatedProducts[1]['final_details']['total_purchase'] = number_format($this->get('total_purchase'), $numOfCurrencyDecimalPlaces,'.','');
		$relatedProducts[1]['final_details']['total_margin'] = number_format($this->get('total_margin'), $numOfCurrencyDecimalPlaces,'.','');
		$relatedProducts[1]['final_details']['total_marginp'] = number_format($this->get('total_marginp'), $numOfCurrencyDecimalPlaces,'.','');
		return $relatedProducts;
	}
	function getAssociatedProducts($module,$focus,$seid='')	{
		$log = vglobal('log');
		$log->debug("Entering Calculations_Record_Model getAssociatedProducts(".$module.",".get_class($focus).",".$seid."='') method ...");
		$adb = PearDatabase::getInstance();
		$output = '';
		global $theme;

		$no_of_decimal_places = getCurrencyDecimalPlaces();
		$theme_path="themes/".$theme."/";
		$image_path=$theme_path."images/";
		$product_Detail = Array();

		if($module == 'Calculations') {
			$query="SELECT
						case when vtiger_products.productid != '' then vtiger_products.productname else vtiger_service.servicename end as productname,
						case when vtiger_products.productid != '' then vtiger_products.productcode else vtiger_service.service_no end as productcode,
						case when vtiger_products.productid != '' then vtiger_products.unit_price else vtiger_service.unit_price end as unit_price,
						case when vtiger_products.productid != '' then vtiger_products.qtyinstock else 'NA' end as qtyinstock,
						case when vtiger_products.productid != '' then 'Products' else 'Services' end as entitytype,
									vtiger_calculationsproductrel.listprice,
									vtiger_calculationsproductrel.description AS product_description,
									vtiger_calculationsproductrel.*,vtiger_crmentity.deleted,
									vtiger_products.usageunit,
									vtiger_service.service_usageunit
									FROM vtiger_calculationsproductrel
									LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_calculationsproductrel.productid
									LEFT JOIN vtiger_products
											ON vtiger_products.productid=vtiger_calculationsproductrel.productid
									LEFT JOIN vtiger_service
											ON vtiger_service.serviceid=vtiger_calculationsproductrel.productid
									WHERE id=?
									ORDER BY sequence_no";
				$params = array($focus->id);
		}elseif($module == 'Products')	{
			$query="SELECT
									vtiger_products.productid,
									vtiger_products.productcode,
									vtiger_products.productname,
									vtiger_products.unit_price,
									vtiger_products.usageunit,
									vtiger_products.qtyinstock,vtiger_crmentity.deleted,
									vtiger_crmentity.description AS product_description,
									'Products' AS entitytype
									FROM vtiger_products
									INNER JOIN vtiger_crmentity
											ON vtiger_crmentity.crmid=vtiger_products.productid
									WHERE vtiger_crmentity.deleted=0
											AND productid=?";
				$params = array($seid);
		}	elseif($module == 'Services')	{
			$query="SELECT
									vtiger_service.serviceid AS productid,
									'NA' AS productcode,
									vtiger_service.servicename AS productname,
									vtiger_service.unit_price AS unit_price,
									vtiger_service.service_usageunit AS usageunit,
									'NA' AS qtyinstock,vtiger_crmentity.deleted,
									vtiger_crmentity.description AS product_description,
									'Services' AS entitytype
									FROM vtiger_service
									INNER JOIN vtiger_crmentity
											ON vtiger_crmentity.crmid=vtiger_service.serviceid
									WHERE vtiger_crmentity.deleted=0
											AND serviceid=?";
				$params = array($seid);
		}

		$result = $adb->pquery($query, $params);
		$num_rows=$adb->num_rows($result);
		for($i=1;$i<=$num_rows;$i++)
		{
			$deleted = $adb->query_result($result,$i-1,'deleted');
			$hdnProductId = $adb->query_result($result,$i-1,'productid');
			$hdnProductcode = $adb->query_result($result,$i-1,'productcode');
			$productname=$adb->query_result($result,$i-1,'productname');
			$productdescription=$adb->query_result($result,$i-1,'product_description');
			$comment= decode_html($adb->query_result($result,$i-1,'comment'));
			$qtyinstock=$adb->query_result($result,$i-1,'qtyinstock');
			$qty=$adb->query_result($result,$i-1,'quantity');
			$unitprice=$adb->query_result($result,$i-1,'unit_price');
			$listprice=$adb->query_result($result,$i-1,'listprice');
			$entitytype=$adb->query_result($result,$i-1,'entitytype');
			if ( $entitytype == 'Services' ) {
				$usageunit=vtranslate($adb->query_result($result,$i-1,'service_usageunit'), $entitytype);
			}
			else {
				$usageunit=vtranslate($adb->query_result($result,$i-1,'usageunit'), $entitytype);
			}
			$rbh=$adb->query_result($result,$i-1,'rbh');
			$purchase=$adb->query_result($result,$i-1,'purchase');
			$margin=$adb->query_result($result,$i-1,'margin');
			$marginp=$adb->query_result($result,$i-1,'marginp');
			
			
			if(($deleted) || (!isset($deleted))){
				$product_Detail[$i]['productDeleted'.$i] = true;
			}elseif(!$deleted){
				$product_Detail[$i]['productDeleted'.$i] = false;
			}
			
			if (!empty($entitytype)) {
				$product_Detail[$i]['entityType'.$i]=$entitytype;
			}

			if($listprice == '')
				$listprice = $unitprice;
			if($qty =='')
				$qty = 1;

			//calculate productTotal
			$productTotal = $qty*$listprice;

			//Delete link in First column
			if($i != 1)
			{
				$product_Detail[$i]['delRow'.$i]="Del";
			}
			if(empty($focus->mode) && $seid!=''){
				$sub_prod_query = $adb->pquery("SELECT crmid as prod_id from vtiger_seproductsrel WHERE productid=? AND setype='Products'",array($seid));
			} else {
				$sub_prod_query = $adb->pquery("SELECT productid as prod_id from vtiger_inventorysubproductrel WHERE id=? AND sequence_no=?",array($focus->id,$i));
			}
			$subprodid_str='';
			$subprodname_str='';
			$subProductArray = array();
			if($adb->num_rows($sub_prod_query)>0){
				for($j=0;$j<$adb->num_rows($sub_prod_query);$j++){
					$sprod_id = $adb->query_result($sub_prod_query,$j,'prod_id');
					$sprod_name = $subProductArray[] = getProductName($sprod_id);
					$str_sep = "";
					if($j>0) $str_sep = ":";
					$subprodid_str .= $str_sep.$sprod_id;
					$subprodname_str .= $str_sep." - ".$sprod_name;
				}
			}

			$subprodname_str = str_replace(":","<br>",$subprodname_str);

			$product_Detail[$i]['subProductArray'.$i] = $subProductArray;
			$product_Detail[$i]['hdnProductId'.$i] = $hdnProductId;
			$product_Detail[$i]['productName'.$i]= from_html($productname);
			/* Added to fix the issue Product Pop-up name display*/
			if($_REQUEST['action'] == 'CreateSOPDF' || $_REQUEST['action'] == 'CreatePDF' || $_REQUEST['action'] == 'SendPDFMail')
				$product_Detail[$i]['productName'.$i]= htmlspecialchars($product_Detail[$i]['productName'.$i]);
			$product_Detail[$i]['hdnProductcode'.$i] = $hdnProductcode;
			$product_Detail[$i]['productDescription'.$i]= from_html($productdescription);
			if($module == 'Potentials' || $module == 'Products' || $module == 'Services') {
				$product_Detail[$i]['comment'.$i]= $productdescription;
			}else {
				$product_Detail[$i]['comment'.$i]= $comment;
			}

			$listprice = number_format($listprice, $no_of_decimal_places,'.','');
			$product_Detail[$i]['qty'.$i]=decimalFormat($qty);
			$product_Detail[$i]['listPrice'.$i]=$listprice;
			$product_Detail[$i]['unitPrice'.$i]=number_format($unitprice, $no_of_decimal_places,'.','');
			$product_Detail[$i]['usageUnit'.$i]=$usageunit;
			$product_Detail[$i]['productTotal'.$i]=number_format($productTotal, $no_of_decimal_places,'.','');
			$product_Detail[$i]['subproduct_ids'.$i]=$subprodid_str;
			$product_Detail[$i]['subprod_names'.$i]=$subprodname_str;
			$product_Detail[$i]['rbh'.$i]=number_format($rbh, $no_of_decimal_places,'.','');
			$product_Detail[$i]['purchase'.$i]=number_format($purchase, $no_of_decimal_places,'.','');
			$product_Detail[$i]['margin'.$i]=number_format($margin, $no_of_decimal_places,'.','');
			$product_Detail[$i]['marginp'.$i]=number_format($marginp, $no_of_decimal_places,'.','');
		}
		$log->debug("Exiting Calculations_Record_Model getAssociatedProducts method ...");
		return $product_Detail;

	}
}

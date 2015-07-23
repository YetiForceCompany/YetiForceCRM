<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

function SaveInventory(&$focus, $module, $update_prod_stock = 'false', $updateDemand = '')
{
	$adb = PearDatabase::getInstance();
	$log = vglobal('log');
	$id = $focus->id;
	$log->debug("Entering into function SaveInventory($module).");
	//Added to get the convertid
	if (isset($_REQUEST['convert_from']) && $_REQUEST['convert_from'] != '') {
		$id = $_REQUEST['return_id'];
	} else if (isset($_REQUEST['duplicate_from']) && $_REQUEST['duplicate_from'] != '') {
		$id = $_REQUEST['duplicate_from'];
	}

	$ext_prod_arr = Array();
	if ($focus->mode == 'edit') {
		if ($_REQUEST['taxtype'] == 'group')
			$all_available_taxes = getAllTaxes('available', '', 'edit', $id);
		$return_old_values = '';
		if ($module != 'PurchaseOrder') {
			$return_old_values = 'return_old_values';
		}

		//we will retrieve the existing product details and store it in a array and then delete all the existing product details and save new values, retrieve the old value and update stock only for SO, Quotes and Invoice not for PO
		//$ext_prod_arr = deleteInventoryProductDetails($focus->id,$return_old_values);
		deleteInventoryProductDetails($focus);
	} else {
		if ($_REQUEST['taxtype'] == 'group')
			$all_available_taxes = getAllTaxes('available', '', 'edit', $id);
	}
	$tot_no_prod = $_REQUEST['totalProductCount']; // przekazuje ilosc dodanych produkow lub usług
	//If the taxtype is group then retrieve all available taxes, else retrive associated taxes for each product inside loop
	$prod_seq = 1;
	for ($i = 1; $i <= $tot_no_prod; $i++) {
		//if the product is deleted then we should avoid saving the deleted products
		if ($_REQUEST["deleted" . $i] == 1)
			continue;

		$prod_id = $_REQUEST['hdnProductId' . $i];
		if (isset($_REQUEST['productDescription' . $i]))
			$description = $_REQUEST['productDescription' . $i];
		/* else{
		  $desc_duery = "select vtiger_crmentity.description AS product_description from vtiger_crmentity where vtiger_crmentity.crmid=?";
		  $desc_res = $adb->pquery($desc_duery,array($prod_id));
		  $description = $adb->query_result($desc_res,0,"product_description");
		  } */
		$qty = $_REQUEST['qty' . $i];
		$listprice = $_REQUEST['listPrice' . $i];
		$comment = $_REQUEST['comment' . $i];

		//we have to update the Product stock for PurchaseOrder if $update_prod_stock is true
		if ($module == 'PurchaseOrder' && $update_prod_stock == 'true') {
			addToProductStock($prod_id, $qty);
		}
		if ($module == 'SalesOrder') {
			if ($updateDemand == '-') {
				deductFromProductDemand($prod_id, $qty);
			} elseif ($updateDemand == '+') {
				addToProductDemand($prod_id, $qty);
			}
		}

		$query = "insert into vtiger_inventoryproductrel(id, productid, sequence_no, quantity, listprice, comment, description) values(?,?,?,?,?,?,?)";
		$qparams = array($focus->id, $prod_id, $prod_seq, $qty, $listprice, $comment, $description);
		$adb->pquery($query, $qparams);

		$lineitem_id = $adb->getLastInsertID();

		$sub_prod_str = $_REQUEST['subproduct_ids' . $i];
		if (!empty($sub_prod_str)) {
			$sub_prod = split(":", $sub_prod_str);
			for ($j = 0; $j < count($sub_prod); $j++) {
				$query = "insert into vtiger_inventorysubproductrel(id, sequence_no, productid) values(?,?,?)";
				$qparams = array($focus->id, $prod_seq, $sub_prod[$j]);
				$adb->pquery($query, $qparams);
			}
		}
		$prod_seq++;

		if ($module != 'PurchaseOrder') {
			//update the stock with existing details
			updateStk($prod_id, $qty, $focus->mode, $ext_prod_arr, $module);
		}

		//we should update discount and tax details
		$updatequery = "update vtiger_inventoryproductrel set ";
		$updateparams = array();

		//set the discount percentage or discount amount in update query, then set the tax values
		if ($_REQUEST['discount_type' . $i] == 'percentage') {
			$updatequery .= " discount_percent=?,";
			array_push($updateparams, $_REQUEST['discount_percentage' . $i]);
		} elseif ($_REQUEST['discount_type' . $i] == 'amount') {
			$updatequery .= " discount_amount=?,";
			$discount_amount = $_REQUEST['discount_amount' . $i];
			array_push($updateparams, $discount_amount);
		}
		if ($_REQUEST['taxtype'] == 'group') {
			for ($tax_count = 0; $tax_count < count($all_available_taxes); $tax_count++) {
				$tax_name = $all_available_taxes[$tax_count]['taxname'];
				$tax_val = $all_available_taxes[$tax_count]['percentage'];
				$request_tax_name = $tax_name . "_group_percentage";
				if (isset($_REQUEST[$request_tax_name]))
					$tax_val = $_REQUEST[$request_tax_name];
				$updatequery .= " $tax_name = ?,";
				array_push($updateparams, $tax_val);
			}
			$updatequery = trim($updatequery, ',') . " where id=? and productid=? and lineitem_id = ?";
			array_push($updateparams, $focus->id, $prod_id, $lineitem_id);
		}
		else {
			$taxes_for_product = getTaxDetailsForProduct($prod_id, 'all');
			for ($tax_count = 0; $tax_count < count($taxes_for_product); $tax_count++) {
				$tax_name = $taxes_for_product[$tax_count]['taxname'];
				$request_tax_name = $tax_name . "_percentage" . $i;

				$updatequery .= " $tax_name = ?,";
				array_push($updateparams, $_REQUEST[$request_tax_name]);
			}
			$updatequery = trim($updatequery, ',') . " where id=? and productid=? and lineitem_id = ?";
			array_push($updateparams, $focus->id, $prod_id, $lineitem_id);
		}
		// jens 2006/08/19 - protect against empy update queries
		if (!preg_match('/set\s+where/i', $updatequery)) {
			$adb->pquery($updatequery, $updateparams);
		}
	}

	//we should update the netprice (subtotal), taxtype, group discount, S&H charge, S&H taxes and total
	//netprice, group discount, taxtype and total to entity table

	$updatequery = " update $focus->table_name set ";
	$updateparams = array();
	$subtotal = $_REQUEST['subtotal'];
	$updatequery .= " subtotal=?,";
	array_push($updateparams, $subtotal);

	$updatequery .= " taxtype=?,";
	array_push($updateparams, $_REQUEST['taxtype']);

	//for discount percentage or discount amount
	if ($_REQUEST['discount_type_final'] == 'percentage') {
		$updatequery .= " discount_percent=?,";
		array_push($updateparams, $_REQUEST['discount_percentage_final']);
	} elseif ($_REQUEST['discount_type_final'] == 'amount') {
		$discount_amount_final = $_REQUEST['discount_amount_final'];
		$updatequery .= " discount_amount=?,";
		array_push($updateparams, $discount_amount_final);
	}

	$total = $_REQUEST['total'];
	$updatequery .= " total=?,";
	array_push($updateparams, $total);

	//$id_array = Array('PurchaseOrder'=>'purchaseorderid','SalesOrder'=>'salesorderid','Quotes'=>'quoteid','Invoice'=>'invoiceid');
	//Added where condition to which entity we want to update these values
	$updatequery .= " where " . $focus->table_index . "=?";
	array_push($updateparams, $focus->id);
	$adb->pquery($updatequery, $updateparams);

	$log->debug("Exit from function SaveInventory($module).");
}

?>

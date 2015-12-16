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
 * OSSCosts Record Model Class
 */
class OSSCosts_Record_Model extends Inventory_Record_Model {
	function getConfig() {
        $db = PearDatabase::getInstance();
        $sql = "SELECT * FROM vtiger_osscosts_config";
        $result = $db->query($sql, true);
        $output = array();
        for($i = 0; $i < $db->num_rows($result); $i++){
            $output[$db->query_result($result, $i, 'param')] = $db->query_result($result, $i, 'value');
        }
		return $output;
	}
	function setConfig($param, $val) {
        $db = PearDatabase::getInstance();
		if($val == 'checked'){ $val = 1;
			copy("modules/OSSCosts/copy/new/DetailView.php", "modules/Vtiger/models/DetailView.php");
			if($param == 'show_widgets_opportunities'){
				//copy("layouts/vlayout/modules/Potentials/SummaryViewWidgets.tpl", "modules/OSSCosts/copy/old/$param.tpl");
				//copy("modules/OSSCosts/copy/new/$param.tpl", "layouts/vlayout/modules/Potentials/SummaryViewWidgets.tpl");
			}elseif($param == 'show_widgets_helpdesk'){
				//copy("layouts/vlayout/modules/HelpDesk/SummaryViewWidgets.tpl", "modules/OSSCosts/copy/old/$param.tpl");
				//copy("modules/OSSCosts/copy/new/$param.tpl", "layouts/vlayout/modules/HelpDesk/SummaryViewWidgets.tpl");
			}elseif($param == 'show_widgets_project'){
				//copy("layouts/vlayout/modules/Project/SummaryViewWidgets.tpl", "modules/OSSCosts/copy/old/$param.tpl");
				//copy("modules/OSSCosts/copy/new/$param.tpl", "layouts/vlayout/modules/Project/SummaryViewWidgets.tpl");
			}elseif($param == 'restrict_helpdesk'){
				//copy("modules/OSSCosts/copy/new/$param.php", "modules/HelpDesk/views/Popup.php");
			}elseif($param == 'restrict_opportunities'){
				//copy("modules/Potentials/views/Popup.php", "modules/OSSCosts/copy/old/$param.php");
				//copy("modules/OSSCosts/copy/new/$param.php", "modules/Potentials/views/Popup.php");
			}elseif($param == 'restrict_project'){
				//copy("modules/OSSCosts/copy/new/$param.php", "modules/Project/views/Popup.php");
			}
		}else{
			$result = $db->query("SELECT value FROM vtiger_osscosts_config WHERE value = '1'", true);
			if( $db->num_rows($result) == 0){
				//copy("modules/OSSCosts/copy/old/DetailView.php", "modules/Vtiger/models/DetailView.php");
			}
			if($param == 'show_widgets_opportunities'){
				//copy("modules/OSSCosts/copy/old/$param.tpl", "layouts/vlayout/modules/Potentials/SummaryViewWidgets.tpl");
			}elseif($param == 'show_widgets_helpdesk'){
				//copy("modules/OSSCosts/copy/old/$param.tpl", "layouts/vlayout/modules/HelpDesk/SummaryViewWidgets.tpl");
			}elseif($param == 'show_widgets_project'){
				//copy("modules/OSSCosts/copy/old/$param.tpl", "layouts/vlayout/modules/Project/SummaryViewWidgets.tpl");
			}elseif($param == 'restrict_helpdesk'){
				//unlink("modules/HelpDesk/views/Popup.php");
			}elseif($param == 'restrict_opportunities'){
				//copy("modules/OSSCosts/copy/old/$param.tpl", "modules/Potentials/views/Popup.php");
			}elseif($param == 'restrict_opportunities'){
				//unlink("modules/Project/views/Popup.php");
			}
			
			
		}
        $db->query("UPDATE vtiger_osscosts_config SET value = '$val' WHERE param = '$param'", true);
	}
	function getWidget($srecord,$smodule) {
        $db = PearDatabase::getInstance();
		$return = Array();
		$ModuleModel = Vtiger_Module_Model::getCleanInstance('OSSCosts');
		$field = $ModuleModel->modules_fields_ids[$smodule];
		$limit = $ModuleModel->widget_no_rows;
		$sql = "SELECT osscosts_no,total FROM vtiger_osscosts INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_osscosts.osscostsid WHERE vtiger_crmentity.deleted=0 AND $field = '$srecord' ORDER BY osscostsid DESC LIMIT $limit";
		$result = $db->query($sql, true);
		$return['rows'] = $adb->fetch_array($result);
		$sql = "SELECT COUNT(osscostsid) AS count,SUM(total) AS sum FROM vtiger_osscosts INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_osscosts.osscostsid WHERE vtiger_crmentity.deleted=0 AND $field = '$srecord'";
		$result = $db->query($sql, true);
		$return['summary'] = $adb->fetch_array($result);
		return $return;
	}
	function getHierarchy() {
		$focus = CRMEntity::getInstance($this->getModuleName());
		$hierarchy = $focus->getHierarchy($this->getId());
		$i=0;
		foreach($hierarchy['entries'] as $rowtId => $rowInfo) {
			preg_match('/<a href="+/', $rowInfo[0], $matches);
			if($matches != null) {
				preg_match('/[.\s]+/', $rowInfo[0], $dashes);
				preg_match("/<a(.*)>(.*)<\/a>/i",$rowInfo[0], $name);

				$recordModel = Vtiger_Record_Model::getCleanInstance('OSSCosts');
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
		$adb = PearDatabase::getInstance(); $log = vglobal('log');

		$log->debug("Entering into function OSSCosts_Record_Model getInventoryCurrencyInfo($module, $id).");

		$inv_table_array = Array('OSSCosts'=>'vtiger_osscosts');
		$inv_id_array = Array('OSSCosts'=>'osscostsid');

		$inventory_table = $inv_table_array[$module];
		$inventory_id = $inv_id_array[$module];
		$res = $adb->pquery("select currency_id, $inventory_table.conversion_rate as conv_rate, vtiger_currency_info.* from $inventory_table
							inner join vtiger_currency_info on $inventory_table.currency_id = vtiger_currency_info.id
							where $inventory_id=?", array($id));

		$currency_info = array();
		$currency_info['currency_id'] = $adb->query_result($res,0,'currency_id');
		$currency_info['conversion_rate'] = $adb->query_result($res,0,'conv_rate');
		$currency_info['currency_name'] = $adb->query_result($res,0,'currency_name');
		$currency_info['currency_code'] = $adb->query_result($res,0,'currency_code');
		$currency_info['currency_symbol'] = $adb->query_result($res,0,'currency_symbol');

		$log->debug("Exit from function OSSCosts_Record_Model getInventoryCurrencyInfo($module, $id).");

		return $currency_info;
	}
	function getInventoryTaxType($module, $id)	{
		$adb = PearDatabase::getInstance(); $log = vglobal('log');

		$log->debug("Entering into function getInventoryTaxType($module, $id).");

		$inv_table_array = Array('OSSCosts'=>'vtiger_osscosts');
		$inv_id_array = Array('OSSCosts'=>'osscostsid');

		$res = $adb->pquery("select taxtype from $inv_table_array[$module] where $inv_id_array[$module]=?", array($id));

		$taxtype = $adb->query_result($res,0,'taxtype');

		$log->debug("Exit from function getInventoryTaxType($module, $id).");

		return $taxtype;
	}
	function getProductTaxes() {
		$taxDetails = $this->get('taxDetails');
		if ($taxDetails) {
			return $taxDetails;
		}

		$record = $this->getId();
		if ($record) {
			$relatedProducts = $this->getAssociatedProducts($this->getModuleName(), $this->getEntity());
			$taxDetails = $relatedProducts[1]['final_details']['taxes'];
		} else {
			$taxDetails = getAllTaxes('available', '', $this->getEntity()->mode, $this->getId());
		}

		$this->set('taxDetails', $taxDetails);
		return $taxDetails;
	}
	function getProducts() {
		$numOfCurrencyDecimalPlaces = getCurrencyDecimalPlaces();
		$relatedProducts = $this->getAssociatedProducts($this->getModuleName(), $this->getEntity());
		$relatedProducts[1]['final_details']['grandTotal'] = number_format($this->get('hdnGrandTotal'), $numOfCurrencyDecimalPlaces,'.','');
		$relatedProducts[1]['final_details']['total_purchase'] = number_format($this->get('total_purchase'), $numOfCurrencyDecimalPlaces,'.','');
		$relatedProducts[1]['final_details']['total_margin'] = number_format($this->get('total_margin'), $numOfCurrencyDecimalPlaces,'.','');
		$relatedProducts[1]['final_details']['total_marginp'] = number_format($this->get('total_marginp'), $numOfCurrencyDecimalPlaces,'.','');

		//Updating Pre tax total
		$preTaxTotal = (float)$relatedProducts[1]['final_details']['hdnSubTotal']
						- (float)$relatedProducts[1]['final_details']['discountTotal_final'];

		$relatedProducts[1]['final_details']['preTaxTotal'] = number_format($preTaxTotal, $numOfCurrencyDecimalPlaces,'.','');
		
		//Updating Total After Discount
		$totalAfterDiscount = (float)$relatedProducts[1]['final_details']['hdnSubTotal']
								- (float)$relatedProducts[1]['final_details']['discountTotal_final'];
		
		$relatedProducts[1]['final_details']['totalAfterDiscount'] = number_format($totalAfterDiscount, $numOfCurrencyDecimalPlaces,'.','');
		return $relatedProducts;
	}
	function getAssociatedProducts($module,$focus,$seid='')	{
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		$log = vglobal('log');
		$theme = vglobal('theme');
		$log->debug("Entering OSSCosts_Record_Model getAssociatedProducts(".$module.",".get_class($focus).",".$seid."='') method ...");
		$output = '';

		$no_of_decimal_places = getCurrencyDecimalPlaces();
		$theme_path="themes/".$theme."/";
		$image_path=$theme_path."images/";
		$product_Detail = Array();

		if($module == 'OSSCosts') {
			$query="SELECT
						case when vtiger_products.productid != '' then vtiger_products.productname else vtiger_service.servicename end as productname,
						case when vtiger_products.productid != '' then vtiger_products.productcode else vtiger_service.service_no end as productcode,
						case when vtiger_products.productid != '' then vtiger_products.unit_price else vtiger_service.unit_price end as unit_price,
						case when vtiger_products.productid != '' then vtiger_products.qtyinstock else 'NA' end as qtyinstock,
						case when vtiger_products.productid != '' then 'Products' else 'Services' end as entitytype,
									vtiger_inventoryproductrel.listprice,
									vtiger_inventoryproductrel.description AS product_description,
									vtiger_inventoryproductrel.*,vtiger_crmentity.deleted,
									vtiger_products.usageunit,
									vtiger_service.service_usageunit
									FROM vtiger_inventoryproductrel
									LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_inventoryproductrel.productid
									LEFT JOIN vtiger_products
											ON vtiger_products.productid=vtiger_inventoryproductrel.productid
									LEFT JOIN vtiger_service
											ON vtiger_service.serviceid=vtiger_inventoryproductrel.productid
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
		$finalTaxTotal = '0.00';
		for($i=1;$i<=$num_rows;$i++)
		{
			$deleted = $adb->query_result($result,$i-1,'deleted');
			$hdnProductId = $adb->query_result($result,$i-1,'productid');
			$hdnProductcode = $adb->query_result($result,$i-1,'productcode');
			$productname=$adb->query_result($result,$i-1,'productname');
			$productdescription=$adb->query_result($result,$i-1,'product_description');
			$comment=$adb->query_result($result,$i-1,'comment');
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
			$tax=$adb->query_result($result,$i-1,'tax');
			
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

			if($module != 'PurchaseOrder' && $focus->object_name != 'Order') {
				$product_Detail[$i]['qtyInStock'.$i]=decimalFormat($qtyinstock);
			}
			$listprice = number_format($listprice, $no_of_decimal_places,'.','');
			$product_Detail[$i]['qty'.$i]=decimalFormat($qty);
			$product_Detail[$i]['listPrice'.$i]=$listprice;
			$product_Detail[$i]['unitPrice'.$i]=number_format($unitprice, $no_of_decimal_places,'.','');
			$product_Detail[$i]['usageUnit'.$i]=$usageunit;
			$product_Detail[$i]['productTotal'.$i]=$productTotal;
			$product_Detail[$i]['subproduct_ids'.$i]=$subprodid_str;
			$product_Detail[$i]['subprod_names'.$i]=$subprodname_str;
			$product_Detail[$i]['tax'.$i]=$tax;
			$discount_percent = decimalFormat($adb->query_result($result,$i-1,'discount_percent'));
			$discount_amount = $adb->query_result($result,$i-1,'discount_amount');
			$discount_amount = decimalFormat(number_format($discount_amount, $no_of_decimal_places,'.',''));
			$discountTotal = '0.00';
			//Based on the discount percent or amount we will show the discount details

			//To avoid NaN javascript error, here we assign 0 initially to' %of price' and 'Direct Price reduction'(for Each Product)
			$product_Detail[$i]['discount_percent'.$i] = 0;
			$product_Detail[$i]['discount_amount'.$i] = 0;

			if($discount_percent != 'NULL' && $discount_percent != '')
			{
				$product_Detail[$i]['discount_type'.$i] = "percentage";
				$product_Detail[$i]['discount_percent'.$i] = $discount_percent;
				$product_Detail[$i]['checked_discount_percent'.$i] = ' checked';
				$product_Detail[$i]['style_discount_percent'.$i] = ' style="visibility:visible"';
				$product_Detail[$i]['style_discount_amount'.$i] = ' style="visibility:hidden"';
				$discountTotal = $productTotal*$discount_percent/100;
			}
			elseif($discount_amount != 'NULL' && $discount_amount != '')
			{
				$product_Detail[$i]['discount_type'.$i] = "amount";
				$product_Detail[$i]['discount_amount'.$i] = $discount_amount;
				$product_Detail[$i]['checked_discount_amount'.$i] = ' checked';
				$product_Detail[$i]['style_discount_amount'.$i] = ' style="visibility:visible"';
				$product_Detail[$i]['style_discount_percent'.$i] = ' style="visibility:hidden"';
				$discountTotal = $discount_amount;
			}
			else
			{
				$product_Detail[$i]['checked_discount_zero'.$i] = ' checked';
			}
			$totalAfterDiscount = $productTotal-$discountTotal;
			$totalAfterDiscount = number_format($totalAfterDiscount, $no_of_decimal_places,'.','');
			$discountTotal = number_format($discountTotal, $no_of_decimal_places,'.','');
			$product_Detail[$i]['discountTotal'.$i] = $discountTotal;
			$product_Detail[$i]['totalAfterDiscount'.$i] = $totalAfterDiscount;

			$amount = '0.00';
			$tax_details = getTaxDetailsForProduct($hdnProductId,'all');
			//First we should get all available taxes and then retrieve the corresponding tax values
			$allTaxes = getAllTaxes('available','','edit',$focus->id);
			$taxtype = $this->getInventoryTaxType($module,$focus->id);
			for($tax_count=0;$tax_count<count($tax_details);$tax_count++)
			{
				$tax_name = $tax_details[$tax_count]['taxname'];
				$tax_label = $tax_details[$tax_count]['taxlabel'];
				$tax_value = '0.00';

				//condition to avoid this function call when create new PO/Invoice from Product module
				if($focus->id != '')
				{
					if($taxtype == 'individual')//if individual then show the entered tax percentage
						$tax_value = getInventoryProductTaxValue($focus->id, $hdnProductId, $tax_name);
					else//if group tax then we have to show the default value when change to individual tax
						$tax_value = $tax_details[$tax_count]['percentage'];
				}
				else//if the above function not called then assign the default associated value of the product
					$tax_value = $tax_details[$tax_count]['percentage'];

				$product_Detail[$i]['taxes'][$tax_count]['taxname'] = $tax_name;
				$product_Detail[$i]['taxes'][$tax_count]['taxlabel'] = $tax_label;
				$product_Detail[$i]['taxes'][$tax_count]['percentage'] = $tax_value;
				$amount = $totalAfterDiscount*$tax_value/100;
				$amount = number_format($amount, $no_of_decimal_places,'.','');
				$product_Detail[$i]['taxes'][$tax_count]['amount'] = $amount;
				if($tax == $tax_name){
					$finalTaxTotal += $amount;
					$product_Detail[$i]['taxTotal'.$i] = $amount;
				}
			}
			if($taxtype == 'group'){
				foreach ($allTaxes as $key => $value){
					if($tax == $value['taxname']){
						$amount = $totalAfterDiscount*$value['percentage']/100;
						$amount = number_format($amount, $no_of_decimal_places,'.','');
						$product_Detail[$i]['taxes'][$tax]['amount'] = $amount;
						$finalTaxTotal += $amount;
						$product_Detail[$i]['taxTotal'.$i] = $amount;
					}
				}		
			}
			//Calculate netprice
			$netPrice = $totalAfterDiscount+number_format($product_Detail[$i]['taxTotal'.$i], $no_of_decimal_places,'.','');
			//if condition is added to call this function when we create PO/Invoice from Product module
			
			$product_Detail[$i]['netPrice'.$i] = $netPrice;
		}
		//set the taxtype
		$product_Detail[1]['final_details']['taxtype'] = $taxtype;

		//Get the Final Discount, S&H charge, Tax for S&H  values
		//To set the Final Discount details
		$finalDiscount = '0.00';
		$product_Detail[1]['final_details']['discount_type_final'] = 'zero';

		$subTotal = ($focus->column_fields['hdnSubTotal'] != '')?$focus->column_fields['hdnSubTotal']:'0.00';
		$subTotal = number_format($subTotal, $no_of_decimal_places,'.','');

		$product_Detail[1]['final_details']['hdnSubTotal'] = $subTotal;
		$discountPercent = ($focus->column_fields['hdnDiscountPercent'] != '')?$focus->column_fields['hdnDiscountPercent']:'0.00';
		$discountAmount = ($focus->column_fields['hdnDiscountAmount'] != '')?$focus->column_fields['hdnDiscountAmount']:'0.00';
		if($discountPercent != '0'){
			$discountAmount = ($product_Detail[1]['final_details']['hdnSubTotal'] * $discountPercent / 100);
		}

		//To avoid NaN javascript error, here we assign 0 initially to' %of price' and 'Direct Price reduction'(For Final Discount)
		$discount_amount_final = '0.00';
		$discount_amount_final = number_format($discount_amount_final, $no_of_decimal_places,'.','');
		$product_Detail[1]['final_details']['discount_percentage_final'] = 0;
		$product_Detail[1]['final_details']['discount_amount_final'] = $discount_amount_final;

		if($focus->column_fields['hdnDiscountPercent'] != '0')
		{
			$finalDiscount = ($subTotal*$discountPercent/100);
			$product_Detail[1]['final_details']['discount_type_final'] = 'percentage';
			$product_Detail[1]['final_details']['discount_percentage_final'] = $discountPercent;
			$product_Detail[1]['final_details']['checked_discount_percentage_final'] = ' checked';
			$product_Detail[1]['final_details']['style_discount_percentage_final'] = ' style="visibility:visible"';
			$product_Detail[1]['final_details']['style_discount_amount_final'] = ' style="visibility:hidden"';
		}
		elseif($focus->column_fields['hdnDiscountAmount'] != '0' && $focus->column_fields['hdnDiscountAmount'] != '')
		{
			$finalDiscount = $focus->column_fields['hdnDiscountAmount'];
			$product_Detail[1]['final_details']['discount_type_final'] = 'amount';
			$product_Detail[1]['final_details']['discount_amount_final'] = $discountAmount;
			$product_Detail[1]['final_details']['checked_discount_amount_final'] = ' checked';
			$product_Detail[1]['final_details']['style_discount_amount_final'] = ' style="visibility:visible"';
			$product_Detail[1]['final_details']['style_discount_percentage_final'] = ' style="visibility:hidden"';
		}
		$finalDiscount = number_format($finalDiscount, $no_of_decimal_places,'.','');
		$product_Detail[1]['final_details']['discountTotal_final'] = $finalDiscount;

		//To set the Final Tax values
		//we will get all taxes. if individual then show the product related taxes only else show all taxes
		//suppose user want to change individual to group or vice versa in edit time the we have to show all taxes. so that here we will store all the taxes and based on need we will show the corresponding taxes

		for($tax_count=0;$tax_count<count($allTaxes);$tax_count++)
		{
			$tax_name = $allTaxes[$tax_count]['taxname'];
			$tax_label = $allTaxes[$tax_count]['taxlabel'];

			//if taxtype is individual and want to change to group during edit time then we have to show the all available taxes and their default values
			//Also taxtype is group and want to change to individual during edit time then we have to provide the asspciated taxes and their default tax values for individual products
			if($taxtype == 'group')
				$tax_percent = $adb->query_result($result,0,$tax_name);
			else
				$tax_percent = $allTaxes[$tax_count]['percentage'];//$adb->query_result($result,0,$tax_name);

			if($tax_percent == '' || $tax_percent == 'NULL')
				$tax_percent = '0.00';
			$taxamount = ($subTotal-$finalDiscount)*$tax_percent/100;
			$taxamount = number_format($taxamount, $no_of_decimal_places,'.','');
			$product_Detail[1]['final_details']['taxes'][$tax_count]['taxname'] = $tax_name;
			$product_Detail[1]['final_details']['taxes'][$tax_count]['taxlabel'] = $tax_label;
			$product_Detail[1]['final_details']['taxes'][$tax_count]['percentage'] = $tax_percent;
			$product_Detail[1]['final_details']['taxes'][$tax_count]['amount'] = $taxamount;
		}
		$product_Detail[1]['final_details']['tax_totalamount'] = $finalTaxTotal;
		$product_Detail[1]['final_details']['tax'] = $tax;
		//To set the grand total
		$grandTotal = ($focus->column_fields['hdnGrandTotal'] != '')?$focus->column_fields['hdnGrandTotal']:'0.00';
		$grandTotal = number_format($grandTotal, $no_of_decimal_places,'.','');
		$product_Detail[1]['final_details']['grandTotal'] = $grandTotal;

		$log->debug("Exiting OSSCosts_Record_Model getAssociatedProducts method ...");

		return $product_Detail;

	}
}

<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Quotes_Detail_View extends Inventory_Detail_View {
	function showLineItemDetails(Vtiger_Request $request) {
		$record = $request->get('record');
		$moduleName = $request->getModule();

		$recordModel = Inventory_Record_Model::getInstanceById($record);
		$relatedProducts = $recordModel->getProducts();

		//##Final details convertion started
		$finalDetails = $relatedProducts[1]['final_details'];

		//Final tax details convertion started
		$taxtype = $finalDetails['taxtype'];
		if ($taxtype == 'group') {
			$taxDetails = $finalDetails['taxes'];
			$taxCount = count($taxDetails);
			for($i=0; $i<$taxCount; $i++) {
				$taxDetails[$i]['amount'] = Vtiger_Currency_UIType::transformDisplayValue($taxDetails[$i]['amount'], null, true);
			}
			$finalDetails['taxes'] = $taxDetails;
		}
		//Final tax details convertion ended

		$currencyFieldsList = array('grandTotal', 'hdnSubTotal', 'preTaxTotal', 'tax_totalamount',
									'discountTotal_final', 'discount_amount_final',  'totalAfterDiscount');
		foreach ($currencyFieldsList as $fieldName) {
			$finalDetails[$fieldName] = Vtiger_Currency_UIType::transformDisplayValue($finalDetails[$fieldName], null, true);
		}

		$relatedProducts[1]['final_details'] = $finalDetails;
		//##Final details convertion ended

		//##Product details convertion started
		$productsCount = count($relatedProducts);
		for ($i=1; $i<=$productsCount; $i++) {
			$product = $relatedProducts[$i];

			//Product tax details convertion started
			if ($taxtype == 'individual') {
				$taxDetails = $product['taxes'];
				$taxCount = count($taxDetails);
				for($j=0; $j<$taxCount; $j++) {
					$taxDetails[$j]['amount'] = Vtiger_Currency_UIType::transformDisplayValue($taxDetails[$j]['amount'], null, true);
				}
				$product['taxes'] = $taxDetails;
			}
			//Product tax details convertion ended

			$currencyFieldsList = array('taxTotal', 'netPrice', 'listPrice', 'unitPrice', 'productTotal',
										'discountTotal', 'discount_amount', 'totalAfterDiscount');
			foreach ($currencyFieldsList as $fieldName) {
				$product[$fieldName.$i] = Vtiger_Currency_UIType::transformDisplayValue($product[$fieldName.$i], null, true);
			}

			$relatedProducts[$i] = $product;
		}
		//##Product details convertion ended

		$viewer = $this->getViewer($request);
		$viewer->assign('RELATED_PRODUCTS', $relatedProducts);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('MODULE_NAME',$moduleName);

		$viewer->view('LineItemsDetail.tpl', 'Quotes');
	}
}

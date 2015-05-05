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

class Calculations_Detail_View extends Inventory_Detail_View {
	function showDetailViewByMode($request) {
		$requestMode = $request->get('requestMode');
		if ($requestMode == 'full') {
			return $this->showModuleDetailView($request);
		}
		return $this->showModuleBasicView($request);
	}

	function showModuleBasicView($request) {
		Vtiger_Detail_View::showModuleBasicView($request);
	}

	/**
	 * Function returns Inventory Line Items
	 * @param Vtiger_Request $request
	 */
	function showLineItemDetails(Vtiger_Request $request) {
		$record = $request->get('record');
		$moduleName = $request->getModule();

		$recordModel = Inventory_Record_Model::getInstanceById($record);
		$relatedProducts = $recordModel->getProducts();

		//##Final details convertion started
		$finalDetails = $relatedProducts[1]['final_details'];

		//Final shipping tax details convertion ended

		$currencyFieldsList = array('grandTotal');
		foreach ($currencyFieldsList as $fieldName) {
			$finalDetails[$fieldName] = Vtiger_Currency_UIType::transformDisplayValue($finalDetails[$fieldName], null, true);
		}
		$relatedProducts[1]['final_details'] = $finalDetails;
		//##Final details convertion ended

		//##Product details convertion started
		$productsCount = count($relatedProducts);
		for ($i=1; $i<=$productsCount; $i++) {
			$product = $relatedProducts[$i];

			$currencyFieldsList = array('listPrice', 'unitPrice', 'productTotal');
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
		$viewer->view('LineItemsDetail.tpl', $moduleName);
	}
}

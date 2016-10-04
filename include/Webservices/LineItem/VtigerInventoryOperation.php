<?php
/* +*******************************************************************************
 *  The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 * ******************************************************************************* */
require_once 'include/Webservices/VtigerModuleOperation.php';
require_once 'include/Webservices/Utils.php';

/**
 * Description of VtigerInventoryOperation
 */
class VtigerInventoryOperation extends VtigerModuleOperation
{

	public function create($elementType, $element)
	{
		$element = $this->sanitizeInventoryForInsert($element);
		$lineItems = $element['LineItems'];
		if (!empty($lineItems)) {
			$element = parent::create($elementType, $element);
			$handler = vtws_getModuleHandlerFromName('LineItem', $this->user);
			$handler->setLineItems('LineItem', $lineItems, $element);
			$parent = $handler->getParentById($element['id']);
			$handler->updateParent($lineItems, $parent);
			$updatedParent = $handler->getParentById($element['id']);
			//since subtotal and grand total is updated in the update parent api 
			$parent['hdnSubTotal'] = $updatedParent['hdnSubTotal'];
			$parent['hdnGrandTotal'] = $updatedParent['hdnGrandTotal'];
			$parent['pre_tax_total'] = $updatedParent['pre_tax_total'];
			$components = vtws_getIdComponents($element['id']);
			$parentId = $components[1];
			$parent['LineItems'] = $handler->getAllLineItemForParent($parentId);
		} else {
			throw new WebServiceException(WebServiceErrorCode::$MANDFIELDSMISSING, "Mandatory Fields Missing..");
		}
		return array_merge($element, $parent);
	}

	public function update($element)
	{
		$element = $this->sanitizeInventoryForInsert($element);
		$lineItemList = $element['LineItems'];
		$handler = vtws_getModuleHandlerFromName('LineItem', $this->user);
		if (!empty($lineItemList)) {
			$updatedElement = parent::update($element);
			$handler->setLineItems('LineItem', $lineItemList, $updatedElement);
			$parent = $handler->getParentById($element['id']);
			$handler->updateParent($lineItemList, $parent);
			$updatedParent = $handler->getParentById($element['id']);
			//since subtotal and grand total is updated in the update parent api 
			$parent['hdnSubTotal'] = $updatedParent['hdnSubTotal'];
			$parent['hdnGrandTotal'] = $updatedParent['hdnGrandTotal'];
			$parent['pre_tax_total'] = $updatedParent['pre_tax_total'];
			$updatedElement = array_merge($updatedElement, $parent);
		} else {
			$updatedElement = $this->revise($element);
		}
		return $updatedElement;
	}

	public function revise($element)
	{
		$element = $this->sanitizeInventoryForInsert($element);
		$handler = vtws_getModuleHandlerFromName('LineItem', $this->user);
		$components = vtws_getIdComponents($element['id']);
		$parentId = $components[1];

		if (!empty($element['LineItems'])) {
			$lineItemList = $element['LineItems'];
			unset($element['LineItems']);

			$updatedElement = parent::revise($element);
			$handler->setLineItems('LineItem', $lineItemList, $updatedElement);
			$parent = $handler->getParentById($element['id']);
			$handler->updateParent($lineItemList, $parent);
			$updatedParent = $handler->getParentById($element['id']);
			//since subtotal and grand total is updated in the update parent api
			$parent['hdnSubTotal'] = $updatedParent['hdnSubTotal'];
			$parent['hdnGrandTotal'] = $updatedParent['hdnGrandTotal'];
			$parent['pre_tax_total'] = $updatedParent['pre_tax_total'];
			$parent['LineItems'] = $handler->getAllLineItemForParent($parentId);
		} else {
			$prevAction = AppRequest::get('action');
			// This is added as we are passing data in user format, so in the crmentity insertIntoEntity API
			// should convert to database format, we have added a check based on the action name there. But 
			// while saving Invoice and Purchase Order we are also depending on the same action file names to
			// not to update stock if its an ajax save. In this case also we do not want line items to change.
			AppRequest::set('action', 'FROM_WS');

			$parent = parent::revise($element);
			AppRequest::set('action', $prevAction);
		}
		return array_merge($element, $parent);
	}

	public function retrieve($id)
	{
		$element = parent::retrieve($id);
		$skipLineItemFields = getLineItemFields();
		foreach ($skipLineItemFields as $key => $field) {
			if (array_key_exists($field, $element)) {
				unset($element[$field]);
			}
		}
		$handler = vtws_getModuleHandlerFromName('LineItem', $this->user);
		$idComponents = vtws_getIdComponents($id);
		$lineItems = $handler->getAllLineItemForParent($idComponents[1]);
		$element['LineItems'] = $lineItems;
		return $element;
	}

	public function delete($id)
	{
		$components = vtws_getIdComponents($id);
		$parentId = $components[1];
		$handler = vtws_getModuleHandlerFromName('LineItem', $this->user);
		$handler->cleanLineItemList($id);
		$result = parent::delete($id);
		return $result;
	}

	/**
	 * function to display discounts,taxes and s
	 * @param type $element
	 * @return type
	 */
	protected function sanitizeInventoryForInsert($element)
	{
		$meta = $this->getMeta();
		if (!empty($element['hdnTaxType'])) {
			AppRequest::set('taxtype', $element['hdnTaxType']);
		}
		if (!empty($element['hdnSubTotal'])) {
			AppRequest::set('subtotal', $element['hdnSubTotal']);
		}

		if (($element['hdnDiscountAmount'])) {
			AppRequest::set('discount_type_final', 'amount');
			AppRequest::set('discount_amount_final', $element['hdnDiscountAmount']);
		} elseif (($element['hdnDiscountPercent'])) {
			AppRequest::set('discount_type_final', 'percentage');
			AppRequest::set('discount_percentage_final', $element['hdnDiscountPercent']);
		} else {
			AppRequest::set('discount_type_final', '');
			AppRequest::set('discount_percentage_final', '');
		}
		if (!empty($element['hdnGrandTotal'])) {
			AppRequest::set('total', $element['hdnGrandTotal']);
		}

		return $element;
	}

	public function describe($elementType)
	{
		$describe = parent::describe($elementType);
		$tandc = \vtlib\Functions::getInventoryTermsAndCondition();
		foreach ($describe['fields'] as $key => $list) {
			if ($list["name"] == 'terms_conditions') {
				$describe['fields'][$key]['default'] = $tandc;
			}
		}
		return $describe;
	}
}

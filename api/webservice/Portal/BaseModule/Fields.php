<?php

namespace Api\Portal\BaseModule;

/**
 * Get fields class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Fields extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];

	/**
	 * Get method.
	 *
	 * @return array
	 */
	public function get()
	{
		$moduleName = $this->controller->request->get('module');
		$module = \Vtiger_Module_Model::getInstance($moduleName);
		$fields = $blocks = [];
		foreach ($module->getFields() as &$field) {
			$block = $field->get('block');
			if (!isset($blocks[$block->id])) {
				$blockProperties = get_object_vars($block);
				$blocks[$block->id] = array_filter($blockProperties, function ($v) {
					return !\is_object($v);
				});
				$blocks[$block->id]['name'] = \App\Language::translate($block->label, $moduleName);
			}
			$fieldInfo = $field->getFieldInfo();
			$fieldInfo['id'] = $field->getId();
			$fieldInfo['isEditable'] = $field->isEditable();
			$fieldInfo['isViewable'] = $field->isViewable();
			$fieldInfo['isEditableReadOnly'] = $field->isEditableReadOnly();
			$fieldInfo['sequence'] = $field->get('sequence');
			$fieldInfo['fieldparams'] = $field->getFieldParams();
			$fieldInfo['blockId'] = $block->id;
			if (isset($fieldInfo['picklistvalues']) && $field->isEmptyPicklistOptionAllowed()) {
				$fieldInfo['isEmptyPicklistOptionAllowed'] = $field->isEmptyPicklistOptionAllowed();
			}
			if ($field->isReferenceField()) {
				$fieldInfo['referenceList'] = $field->getReferenceList();
			}
			if ($field->isTreeField()) {
				$fieldInfo['treeValues'] = \App\Fields\Tree::getTreeValues((int) $field->getFieldParams(), $moduleName);
			}
			if ($field->getFieldDataType() === 'country') {
				$countries = $field->getPicklistValues();
				array_walk($countries, function (&$item, $key) {
					$item = \App\Language::translateSingleMod($key, 'Other.Country');
				});
				$fieldInfo['picklistvalues'] = $countries;
				$fieldInfo['isEmptyPicklistOptionAllowed'] = $field->isEmptyPicklistOptionAllowed();
			}
			$fields[$field->getId()] = $fieldInfo;
		}
		$inventoryFields = [];
		if ($module->isInventory()) {
			$inventoryInstance = \Vtiger_Inventory_Model::getInstance($moduleName);
			$fieldsInInventory = $inventoryInstance->getFieldsByBlocks();
			if (isset($fieldsInInventory[1])) {
				foreach ($fieldsInInventory[1] as $fieldName => $fieldModel) {
					$inventoryFields[1][$fieldName] = [
						'label' => \App\Language::translate($fieldModel->get('label'), $moduleName),
						'isVisibleInDetail' => $fieldModel->isVisibleInDetail(),
						'type' => $fieldModel->getType(),
						'columnname' => $fieldModel->getColumnName(),
						'isSummary' => $fieldModel->isSummary()
					];
				}
			}
		}
		return [
			'fields' => $fields,
			'blocks' => $blocks,
			'inventory' => $inventoryFields
		];
	}
}

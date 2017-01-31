<?php
namespace Api\Portal\BaseModule;

/**
 * Get fields class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Fields extends \Api\Core\BaseAction
{

	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];

	/**
	 * Get method
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
				$blocks[$block->id] = array_filter($blockProperties, function($v, $k) {
					return !is_object($v);
				}, ARRAY_FILTER_USE_BOTH);
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
			if ($field->isReferenceField()) {
				$fieldInfo['referenceList'] = $field->getReferenceList();
			}
			$fields[$field->getId()] = $fieldInfo;
		}
		return ['fields' => $fields, 'blocks' => $blocks];
	}
}

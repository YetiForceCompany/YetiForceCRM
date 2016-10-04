<?php

/**
 * Get fields class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class API_Base_GetFields extends BaseAction
{

	protected $requestMethod = ['get'];

	public function get()
	{
		$moduleName = $this->api->getModuleName();
		$user = new Users();
		$currentUser = $user->retrieveCurrentUserInfoFromFile(Users::getActiveAdminId());
		vglobal('current_user', $currentUser);
		$listQuery = '';

		$module = Vtiger_Module_Model::getInstance($moduleName);

		$fields = $blocks = [];
		foreach ($module->getFields() as &$field) {
			$block = $field->get('block');
			if (!isset($blocks[$block->id])) {
				$blockProperties = get_object_vars($block);
				$blocks[$block->id] = array_filter($blockProperties, function($v, $k) {
					return !is_object($v);
				}, ARRAY_FILTER_USE_BOTH);
			}
			$fieldInfo = $field->getFieldInfo();
			$fieldInfo['id'] = $field->getId();
			$fieldInfo['isEditable'] = $field->isEditable();
			$fieldInfo['isViewable'] = $field->isViewable();
			$fieldInfo['sequence'] = $field->get('sequence');
			$fieldInfo['blockId'] = $block->id;
			$fields[$field->getId()] = $fieldInfo;
		}

		return ['fields' => $fields, 'blocks' => $blocks];
	}
}

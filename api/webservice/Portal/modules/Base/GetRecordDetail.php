<?php

/**
 * Get record detail class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class API_Base_GetRecordDetail extends BaseAction
{

	protected $requestMethod = ['get'];

	public function get($record)
	{
		$moduleName = $this->api->getModuleName();
		$user = new Users();
		$currentUser = $user->retrieveCurrentUserInfoFromFile(Users::getActiveAdminId());
		vglobal('current_user', $currentUser);
		$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
		$rawData = $recordModel->getData();
		$moduleModel = $recordModel->getModule();

		$fields = [];
		$moduleBlockFields = Vtiger_Field_Model::getAllForModule($moduleModel);
		foreach ($moduleBlockFields as $moduleFields) {
			foreach ($moduleFields as $moduleField) {
				$block = $moduleField->get('block');
				if (empty($block)) {
					continue;
				}
				$blockLabel = vtranslate($block->label, $moduleName);
				$fieldLabel = vtranslate($moduleField->get('label'), $moduleName);
				$fields[$blockLabel][$fieldLabel] = $recordModel->getDisplayValue($moduleField->getName(), $record, true);
			}
		}

		return ['rawData' => $rawData, 'data' => $fields];
	}
}

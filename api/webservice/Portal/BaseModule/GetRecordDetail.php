<?php
namespace Api\Portal\BaseModule;

/**
 * Get record detail class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class API_Base_GetRecordDetail extends BaseAction
{

	protected $requestMethod = ['GET'];

	public function get()
	{
		$moduleName = $this->controller->requeste->get('module');
		$record = $this->controller->requeste->get('record');
		$user = new Users();
		$currentUser = $user->retrieveCurrentUserInfoFromFile(Users::getActiveAdminId());
		vglobal('current_user', $currentUser);
		App\User::setCurrentUserId(Users::getActiveAdminId());

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
				$blockLabel = \App\Language::translate($block->label, $moduleName);
				$fieldLabel = \App\Language::translate($moduleField->get('label'), $moduleName);
				$fields[$blockLabel][$fieldLabel] = $recordModel->getDisplayValue($moduleField->getName(), $record, true);
			}
		}
		return ['rawData' => $rawData, 'data' => $fields];
	}
}

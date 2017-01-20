<?php

/**
 * Get record list class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class API_Base_GetRecordsList extends BaseAction
{

	protected $requestMethod = ['get'];

	public function get()
	{
		$moduleName = $this->api->getModuleName();
		$user = new Users();
		$currentUser = $user->retrieveCurrentUserInfoFromFile(Users::getActiveAdminId());
		vglobal('current_user', $currentUser);
		App\User::setCurrentUserId(Users::getActiveAdminId());
		$queryGenerator = new App\QueryGenerator($moduleName);
		$queryGenerator->initForDefaultCustomView();
		$records = [];
		$fieldsModel = $queryGenerator->getListViewFields();
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$record = [];
			foreach ($fieldsModel as $fieldName => $fieldModel) {
				if (isset($row[$fieldName])) {
					$record[$fieldName] = $fieldModel->getDisplayValue($row[$fieldName], $row['id'], false, true);
				}
			}
			$records[$row['id']] = $record;
		}
		$headers = [];
		foreach ($fieldsModel as $fieldName => $fieldModel) {
			$headers[$fieldName] = \App\Language::translate($fieldModel->getFieldLabel(), $moduleName);
		}
		return ['headers' => $headers, 'records' => $records, 'count' => 456];
	}
}

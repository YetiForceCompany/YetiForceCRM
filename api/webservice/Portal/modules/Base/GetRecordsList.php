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
		$listQuery = '';

		$module = Vtiger_Module_Model::getInstance($moduleName);
		$fields = $module->getFields();
		$queryGenerator = new QueryGenerator($moduleName, $currentUser);
		$queryGenerator->initForDefaultCustomView();
		$queryFields = $queryGenerator->getFields();
		$listQuery = $queryGenerator->getQuery();
		$db = PearDatabase::getInstance();

		$listResult = $db->query($listQuery);
		$records = [];
		$entity = CRMEntity::getInstance($moduleName);

		$columns = [];
		foreach ($queryFields as &$column) {
			if (isset($fields[$column])) {
				$columns[$fields[$column]->get('column')] = $fields[$column];
			}
		}

		while ($row = $db->getRow($listResult)) {
			$id = $row[$entity->table_index];
			$record = [];
			foreach ($columns as $column => $field) {
				if (isset($row[$column])) {
					$record[$field->getName()] = $field->getDisplayValue($row[$column], $id, false, true);
				}
			}
			$records[$id] = $record;
		}
		$headers = [];
		foreach ($columns as &$column) {
			$headers[$column->getName()] = vtranslate($column->getFieldLabel(), $moduleName);
		}
		return ['headers' => $headers, 'records' => $records, 'count' => 456];
	}
}

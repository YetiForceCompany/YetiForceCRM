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
		$queryGenerator = new QueryGenerator($moduleName, $currentUser);
		$queryGenerator->initForDefaultCustomView();
		$listQuery = $queryGenerator->getQuery();
		$db = PearDatabase::getInstance();
		$listResult = $db->query($listQuery);
		$records = [];
		$entity = CRMEntity::getInstance($moduleName);
		
		while ($row = $db->fetch_array($listResult)) {
			$records[$row[$entity->table_index]] = $row;
		}
		//$listQuery = getListQuery('OSSTimeControl', '');

		return ['headers' => $queryGenerator->getFields(), 'records' => $records,'count'=> 456 ];
	}
}

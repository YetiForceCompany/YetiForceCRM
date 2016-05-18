<?php

/**
 * Class to get list of storages
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class API_IStorages_GetIStorages extends BaseAction
{

	protected $requestMethod = ['GET'];

	public function get()
	{
		$records = [];
		$db = PearDatabase::getInstance();
		$query = 'SELECT u_yf_istorages.* FROM u_yf_istorages
			INNER JOIN vtiger_crmentity ON  u_yf_istorages.istorageid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = ?
			AND u_yf_istorages.pos LIKE ?
			AND u_yf_istorages.storage_status = ?';
		$results = $db->pquery($query, [0, '%' . $this->api->app['id'] . '%', 'PLL_ACTIVE']);
		while ($storage = $db->getRow($results)) {
			$poses = explode(',', $storage['pos']);
			if (in_array($this->api->app['id'], $poses)) {
				unset($storage['pos']);
				$records[] = $storage;
			}
		}
		return $records;
	}
}

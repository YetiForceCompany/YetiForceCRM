<?php
require_once 'api/webservice/Core/APISessionPOS.php';

/**
 *
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class API_IStorages_GetIStorages extends BaseAction
{

	protected $requestMethod = ['GET'];

	public function get()
	{
		if (APISessionPOS::checkSession($this->api->headers['Sessionid'])) {
			$db = PearDatabase::getInstance();
			$results = $db->pquery('SELECT * FROM u_yf_istorages WHERE pos LIKE ? AND storage_status = ?', ['%' . $this->api->app['id'] . '%', 'PLL_ACTIVE']);
			while ($storage = $db->getRow($results)) {
				$records[] = $storage;
			}
			return $records;
		}
	}
}

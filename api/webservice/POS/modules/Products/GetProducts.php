<?php
require_once 'api/webservice/Core/APISessionPOS.php';

/**
 * Get modules list action class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class API_Products_GetProducts extends BaseAction
{

	protected $requestMethod = ['GET'];

	private function getInfo($recordId)
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$image = $recordModel->getImageDetails();
		$data = $recordModel->getData();
		$imagesUrl = '';
		foreach ($image as $img) {
			$imagesUrl[] = 'api/webservice/Products/GetImage/' . $img['id'];
		}
		$data['imageUrl'] = $imagesUrl;
		$records[$recordModel->getId()] = $data;
		return $records;
	}

	public function get($recordId = false)
	{
		if (APISessionPOS::checkSession($this->api->headers['Sessionid'])) {
			$db = PearDatabase::getInstance();
			if ($recordId) {
				$records = $this->getInfo($recordId);
			} else {
				$results = $db->pquery('SELECT productid FROM vtiger_products WHERE pos LIKE ?', ['%' . $this->api->app['id'] . '%']);
				while ($productId = $db->getRow($results)) {
					$records[] = $this->getInfo($productId['productid']);
				}
			}
			return $records;
		}
	}
}

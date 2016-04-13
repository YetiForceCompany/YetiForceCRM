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

	public function get()
	{
		if(APISessionPOS::checkSession($this->api->headers['Sessionid'])){
			$db = PearDatabase::getInstance();
			$results = $db->pquery('SELECT productid FROM vtiger_products WHERE pos LIKE ?', ['%'.$this->api->app['server_id'].'%']);
			while($productId = $db->getSingleValue($results)){
				$recordModel = Vtiger_Record_Model::getInstanceById($productId);
				$image = $recordModel->getImageDetails();
				$data = $recordModel->getData();
				$data['imageDetail'] = $image;
				$records[$recordModel->getId()] = $data;
			}
			return $records;
		}
	}
}

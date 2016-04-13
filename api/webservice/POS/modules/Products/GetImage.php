<?php

require_once 'api/webservice/Core/APISessionPOS.php';
/**
 * Get modules list action class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class API_Products_GetImage extends BaseAction
{

	protected $requestMethod = ['GET'];

	public function get($recordId)
	{
		if(APISessionPOS::checkSession($this->api->headers['Sessionid'])){
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, 'Products');
			return $recordModel->getData();
		}
	}
}

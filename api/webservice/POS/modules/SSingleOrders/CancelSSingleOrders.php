<?php

/**
 * Class to cancel order
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class API_SSingleOrders_CancelSSingleOrders extends BaseAction
{

	protected $requestMethod = ['POST'];

	public function post($recordId)
	{
		$response = ['success' => false];
		vglobal('current_user', Users_Privileges_Model::getInstanceById($this->user['user_id']));
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		if (strpos($recordModel->get('pos'), (string)$this->api->app['id']) !== false) {
			if ($recordModel->getModuleName() == 'SSingleOrders') {
				if(Users_Privileges_Model::isPermitted($recordModel->getModuleName(), 'EditView', $recordId)){
					$recordModel->set('ssingleorders_status', 'PLL_CANCELLED');
					$recordModel->set('mode', 'edit');
					$recordModel->save();
					$db = PearDatabase::getInstance();
					$result = $db->pquery('SELECT igdnid FROM u_yf_igdn WHERE ssingleordersid = ?', [$recordId]);
					while($igdnId = $db->getSingleValue($result)){
						$recordIgdnModel = Vtiger_Record_Model::getInstanceById($igdnId);
						$recordIgdnModel->set('igdn_status', 'PLL_CANCELLED');
						$recordIgdnModel->set('mode', 'edit');
						$recordIgdnModel->save();
						if($recordIgdnModel->getid()){
							$response = ['success' => true];
						}
					}
				} else {
					throw new APIException('ERR_NO_PERMISSION_TO_EDIT', 500);
				}
			} else {
				throw new APIException('ERR_RECORD_FROM_OTHER_MODULE', 500);
			}
		} else {
			throw new APIException('ERR_NO_PERMISSION_TO_RECORD', 500);
		}
		return $response;
	}
}

<?php
require_once 'api/webservice/Core/APISessionPOS.php';

/**
 * Action for adding orders
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class API_SSingleOrders_SetSSingleOrders extends BaseAction
{

	protected $requestMethod = ['POST'];
	private $inventoryMapping = [
		'name' => 'id_product',
		'price' => 'price',
		'qty' => 'qty',
	];

	private function hasPermissionToStorage($storageId)
	{
		if (empty($storageId)) {
			return false;
		} else {
			$storageModel = Vtiger_Record_Model::getInstanceById($storageId, 'IStorages');
			return in_array($this->api->app['id'], explode(',', $storageModel->get('pos')));
		}
	}

	public function post($offer)
	{
		if ($session = APISessionPOS::checkSession($this->api->headers['Sessionid'])) {
			$offer = json_decode($offer, true);
			if ($this->hasPermissionToStorage($offer['storage'])) {
				$moduleName = 'SSingleOrders';
				$userId = $session['user_id'];
				vglobal('current_user', Users_Privileges_Model::getInstanceById($userId));
				$recordModel = Vtiger_Record_Model::getCleanInstance('SSingleOrders');
				$recordModel->set('subject', $offer['tableNo'] . '/' . $offer['seat']);
				$recordModel->set('ssingleorders_status', $offer['status']);
				$recordModel->set('date_start', $offer['date']);
				$recordModel->set('pos', $this->api->app['id']);
				$recordModel->set('table', $offer['tableNo']);
				$recordModel->set('istoragesid', $offer['storage']);
				$recordModel->set('seat', $offer['seat']);
				$recordModel->set('sum_gross', $offer['brutto']);
				$recordModel->set('description', $offer['description']);
				$recordModel->set('mode', '');
				$countInventoryData = 0;
				$defaultCurrency = Vtiger_Functions::getDefaultCurrencyInfo()['id'];
				$inventory = Vtiger_InventoryField_Model::getInstance($moduleName);
				$fields = $inventory->getColumns();
				foreach ($offer['items'] as $rowInInventory) {
					$countInventoryData++;
					foreach ($fields as $columnName) {
						if ($columnName == 'total' || $columnName == 'gross' || $columnName == 'net') {
							$_REQUEST[$columnName . $countInventoryData] = $rowInInventory['qty'] * $rowInInventory['price'];
						} else {
							if(key_exists($columnName, $this->inventoryMapping)){
								$_REQUEST[$columnName . $countInventoryData] = $rowInInventory[$this->inventoryMapping[$columnName]];
							}
						}
					}
					$_REQUEST['seq' . $countInventoryData] = $countInventoryData;
					$_REQUEST['currency' . $countInventoryData] = $defaultCurrency;
				}
				$_REQUEST['inventoryItemsNo'] = $countInventoryData;
				$recordModel->save();
			} else {
				throw new APIException('LBL_NO_PERMISSION_TO_STORAGE', 405);
			}
			return ['LBL_OK'];
		} else {
			throw new APIException('LBL_NO_SESSION', 401);
		}
	}
}

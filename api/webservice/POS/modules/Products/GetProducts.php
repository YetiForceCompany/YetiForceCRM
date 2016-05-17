<?php

/**
 * Get modules list action class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class API_Products_GetProducts extends BaseAction
{

	protected $requestMethod = ['GET'];

	private function getTemplate()
	{
		$moduleId = Vtiger_Functions::getModuleId('Products');
		$db = PearDatabase::getInstance();
		$query = 'SELECT templateid FROM vtiger_trees_templates WHERE module = ?';
		return $db->getSingleValue($db->pquery($query, [$moduleId]));
	}

	private function getCategoryName($categoryId)
	{
		static $categoryCache = [];
		if (!empty($categoryCache[$categoryId])) {
			return $categoryCache[$categoryId];
		}
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT name FROM vtiger_trees_templates_data WHERE templateid = ? AND tree = ?', [$this->getTemplate(), $categoryId]);
		$categoryName = $db->getSingleValue($result);
		$categoryCache[$categoryId] = $categoryName;
		return $categoryName;
	}

	private function getInfo($recordId)
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$image = $recordModel->getImageDetails();
		$data = $recordModel->getData();
		$imagesUrl = '';
		foreach ($image as $img) {
			$imagesUrl[] = '/Products/GetImage/' . $img['id'];
		}
		$data['categoryName'] = $this->getCategoryName($data['pscategory']);
		$data['imageUrl'] = $imagesUrl;
		return $data;
	}

	public function get($recordId = false)
	{
		$db = PearDatabase::getInstance();
		if ($recordId) {
			$records = $this->getInfo($recordId);
		} else {
			$query = 'SELECT vtiger_products.productid, vtiger_products.pos FROM vtiger_products
					INNER JOIN vtiger_crmentity ON  vtiger_products.productid = vtiger_crmentity.crmid
					WHERE vtiger_crmentity.deleted = ?
					AND vtiger_products.pos LIKE ?
					AND vtiger_products.discontinued = ?';
			$results = $db->pquery($query, [0, '%' . $this->api->app['id'] . '%', 1]);
			while ($products = $db->getRow($results)) {
				$poses = explode(',', $products['pos']);
				if (in_array($this->api->app['id'], $poses)) {
					$records[$products['productid']] = $this->getInfo($products['productid']);
				}
			}
		}
		return $records;
	}
}

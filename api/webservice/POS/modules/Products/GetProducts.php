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

	private function getCategoryName($categoryId)
	{
		static $categoryCache = [];
		if (!empty($categoryCache[$categoryId])) {
			return $categoryCache[$categoryId];
		}
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT name FROM vtiger_trees_templates_data WHERE templateid = ? AND tree = ?', [2, $categoryId]);
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
			$results = $db->pquery('SELECT productid FROM vtiger_products WHERE pos LIKE ? AND discontinued = ?', ['%' . $this->api->app['id'] . '%', 1]);
			while ($productId = $db->getRow($results)) {
				$records[$productId['productid']] = $this->getInfo($productId['productid']);
			}
		}
		return $records;
	}
}

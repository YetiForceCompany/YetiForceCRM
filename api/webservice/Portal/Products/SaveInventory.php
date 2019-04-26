<?php
/**
 * The file contains a the SaveInventory class.
 *
 * @package   Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Portal\Products;

/**
 * Saving data to the inventory module.
 */
class SaveInventory extends \Api\Core\BaseAction
{
	/**
	 * {@inheritdoc}
	 */
	public $allowedMethod = ['PUT', 'POST'];

	/**
	 * Create inventory record.
	 *
	 * @return array
	 */
	public function put(): array
	{
		return $this->post();
	}

	/**
	 * Create inventory record.
	 *
	 * @return array
	 */
	public function post(): array
	{
		$sourceModuleName = $this->controller->request->getByType('sourceModule');
		$inventory = $this->controller->request->getMultiDimensionArray('inventory', [
			[
				'id' => 'Integer',
				'amount' => 'Double'
			]
		]);
		$inventoryData = $this->getInventoryData($inventory);
		$recordModel = \Vtiger_Record_Model::getCleanInstance($sourceModuleName);
		$recordModel->set('subject', $sourceModuleName . '/' . date('Y-m-d'));
		$recordModel->initInventoryData($inventoryData, false);
		$recordModel->save();
		return [
			'id' => $recordModel->getId(),
			'subject' => $recordModel->get('subject')
		];
	}

	/**
	 * Get inventory data.
	 *
	 * @param array $inventory
	 *
	 * @return array
	 */
	private function getInventoryData(array $inventory): array
	{
		$globalTaxes = \Vtiger_Inventory_Model::getGlobalTaxes();
		$productsId = [];
		foreach ($inventory as $item) {
			$productsId[] = $item['id'];
		}
		$dataReader = (new \App\Db\Query())
			->from('vtiger_products')
			->innerJoin('vtiger_crmentity', 'vtiger_products.productid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => 0])
			->andWhere(['vtiger_products.productid' => $productsId])
			->createCommand()
			->query();
		$inventoryData = [];
		while ($row = $dataReader->read()) {
			$taxmode = $row['taxes'];
			$inventoryData[] = [
				'discountmode' => 0,
				'taxmode' => $taxmode,
				'currency' => $row['currency_id'],
				'currencyparam' => '',
				'name' => $row['productid'],
				'unit' => $row['usageunit'],
				'subunit' => $row['subunit'],
				'qty' => $inventory[$row['productid']]['amount'],
				'price' => $row['unit_price'],
				'discount' => 0,
				'discountparam' => '',
				'purchase' => 0,
				'tax' => $globalTaxes[$taxmode]['value'],
				'taxparam' => '',
				'comment1' => $row['description'],
			];
		}
		$dataReader->close();
		return $inventoryData;
	}
}

<?php

/**
 * Synchronize inventory stock file.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizer;

/**
 * Synchronize inventory stock class.
 */
class InventoryStock extends Base
{
	/**
	 * Storage id.
	 *
	 * @var int
	 */
	public $storageId;
	/**
	 * Product id.
	 *
	 * @var int
	 */
	public $product;

	/** {@inheritdoc} */
	public function process()
	{
		$products = [];
		if ('Products' === $this->config->get('storage_quantity_location')) {
			$products = $this->getStockFromProducts();
		} elseif ('IStorages' === $this->config->get('storage_quantity_location') && ((int) $this->config->get('storage_id') === $this->storageId)) {
			$products = $this->getStockFromStorage();
		}
		foreach ($products as $product) {
			try {
				$data = \App\Json::decode($this->connector->request('GET', $this->config->get('store_code') . '/V1/stockItems/' . $product['ean']));
				$data['qty'] = $product['qtyinstock'];
				$this->connector->request('PUT', "{$this->config->get('store_code')}/V1/products/{$product['ean']}/stockItems/{$data['item_id']}", ['stockItem' => $data]);
			} catch (\Throwable $ex) {
				$this->log('Update stock', $ex);
				\App\Log::error('Error during update stock: ' . PHP_EOL . $ex->__toString() . PHP_EOL, 'Integrations/Magento');
			}
		}
	}

	/**
	 * Get stock from products.
	 *
	 * @return array
	 */
	public function getStockFromProducts()
	{
		$queryGenerator = new \App\QueryGenerator('Products');
		$queryGenerator->setStateCondition('All');
		$queryGenerator->setFields(['id', 'qtyinstock', 'ean'])->permissions = false;
		$queryGenerator->addCondition('id', $this->product, 'e');
		return $queryGenerator->createQuery()->all();
	}

	/**
	 * Get stock from storage.
	 *
	 * @return array
	 */
	public function getStockFromStorage()
	{
		$referenceInfo = \Vtiger_Relation_Model::getReferenceTableInfo('Products', 'IStorages');
		return (new \App\Db\Query())->select([
			'id' => $referenceInfo['table'] . '.' . $referenceInfo['rel'],
			'qtyinstock' => $referenceInfo['table'] . '.qtyinstock',
			'ean' => 'vtiger_products.ean', ])
			->from($referenceInfo['table'])
			->innerJoin('vtiger_products', "{$referenceInfo['table']}.{$referenceInfo['rel']} = vtiger_products.productid")
			->where([$referenceInfo['base'] => $this->storageId, $referenceInfo['rel'] => $this->product])
			->all();
	}
}

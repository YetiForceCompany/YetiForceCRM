<?php
/**
 * The file contains: Get record detail class.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Portal\Products;

/**
 * Get record detail class.
 */
class Record extends \Api\Portal\BaseModule\Record
{
	/**
	 * {@inheritdoc}
	 */
	public function get(): array
	{
		$response = parent::get();
		$availableTaxes = [];
		if (\Api\Portal\Privilege::USER_PERMISSIONS === $this->getPermissionType()) {
			$availableTaxes = 'LBL_GROUP';
			$regionalTaxes = '';
			$unitPrice = (new \Vtiger_MultiCurrency_UIType())->getValueForCurrency($response['rawData']['unit_price'] ?? [], \App\Fields\Currency::getDefault()['id']);
		} else {
			$parentRecordModel = \Vtiger_Record_Model::getInstanceById($this->getParentCrmId(), 'Accounts');
			$availableTaxes = $parentRecordModel->get('accounts_available_taxes');
			$regionalTaxes = $parentRecordModel->get('taxes');
			$unitPrice = \Api\Portal\Record::getPriceFromPricebook($this->getParentCrmId(), $this->controller->request->getInteger('record'));
			if (null === $unitPrice) {
				$unitPrice = (new \Vtiger_MultiCurrency_UIType())->getValueForCurrency($response['rawData']['unit_price'] ?? [], \App\Fields\Currency::getDefault()['id']);
			}
		}
		$taxParam = \Api\Portal\Record::getTaxParam($availableTaxes, $response['rawData']['taxes'], $regionalTaxes);
		$taxConfig = \Vtiger_Inventory_Model::getTaxesConfig();
		$response['ext']['unit_gross'] = $unitPrice + (new \Vtiger_Tax_InventoryField())->getTaxValue($taxParam, $unitPrice, (int) $taxConfig['aggregation']);
		$response['ext']['unit_price'] = $unitPrice;
		$response['productBundles'] = $this->getProductBundles();
		return $response;
	}

	private function getProductBundles(): array
	{
		$products = [];
		$productRelationModel = \Vtiger_Relation_Model::getInstance($this->recordModel->getModule(), $this->recordModel->getModule());
		$productRelationModel->set('parentRecord', $this->recordModel);
		$queryGenerator = $productRelationModel->getQuery();
		$queryGenerator->setField('ean');

		$parentRecordModel = \Vtiger_Record_Model::getInstanceById($this->getParentCrmId(), 'Accounts');
		$pricebookId = $parentRecordModel->get('pricebook_id');

		$storage = $this->getUserStorageId();
		if ($storage) {
			$queryGenerator->setCustomColumn('u_#__istorages_products.qtyinstock as storage_qtyinstock');
			$queryGenerator->addJoin([
				'LEFT JOIN',
				'u_#__istorages_products',
				"u_#__istorages_products.crmid={$storage} AND u_#__istorages_products.relcrmid = vtiger_products.productid"]
			);
		}
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		foreach ($dataReader as $row) {
			$row['qtyinstock'] = (int) ($row['storage_qtyinstock'] ?? 0);
			$products[$row['id']] = $row;
		}
		$dataReader->close();
		return $products;
	}
}

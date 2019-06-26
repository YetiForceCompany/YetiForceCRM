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
	 * Is user permissions.
	 *
	 * @var bool
	 */
	private $isUserPermissions;

	/**
	 * Construct.
	 */
	public function __construct()
	{
		$this->isUserPermissions = \Api\Portal\Privilege::USER_PERMISSIONS === (int) \App\User::getCurrentUserModel()->get('permission_type');
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(): array
	{
		$response = parent::get();
		$availableTaxes = [];
		if ($this->isUserPermissions) {
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

	/**
	 * Get product bundles.
	 *
	 * @return array
	 */
	private function getProductBundles(): array
	{
		$products = [];
		$productRelationModel = \Vtiger_Relation_Model::getInstance($this->recordModel->getModule(), $this->recordModel->getModule());
		$productRelationModel->set('parentRecord', $this->recordModel);
		$queryGenerator = $productRelationModel->getQuery();
		$queryGenerator->setField('ean');
		$queryGenerator->setField('taxes');
		if ($this->isUserPermissions) {
			$availableTaxes = 'LBL_GROUP';
			$regionalTaxes = '';
		} else {
			$parentRecordModel = \Vtiger_Record_Model::getInstanceById($this->getParentCrmId(), 'Accounts');
			$availableTaxes = $parentRecordModel->get('accounts_available_taxes');
			$regionalTaxes = $parentRecordModel->get('taxes');
			$pricebookId = $parentRecordModel->get('pricebook_id');
			if (!empty($pricebookId)) {
				$queryGenerator->setCustomColumn('vtiger_pricebookproductrel.listprice');
				$queryGenerator->addJoin([
					'LEFT JOIN',
					'vtiger_pricebookproductrel',
					"vtiger_pricebookproductrel.pricebookid={$pricebookId} AND vtiger_pricebookproductrel.productid = vtiger_products.productid"]
				);
			}
		}
		$storage = $this->getUserStorageId();
		if ($storage) {
			$queryGenerator->setCustomColumn('u_#__istorages_products.qtyinstock as storage_qtyinstock');
			$queryGenerator->addJoin([
				'LEFT JOIN',
				'u_#__istorages_products',
				"u_#__istorages_products.crmid={$storage} AND u_#__istorages_products.relcrmid = vtiger_products.productid"]
			);
		}
		$fieldsModel = $queryGenerator->getListViewFields();
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		$taxConfigAggregation = (int) \Vtiger_Inventory_Model::getTaxesConfig()['aggregation'];
		foreach ($dataReader as $row) {
			$row['qtyinstock'] = (int) ($row['storage_qtyinstock'] ?? 0);
			if (!$this->isUserPermissions && !empty($row['listprice'])) {
				$unitPrice = $row['unit_price'] = $row['listprice'];
			} else {
				$unitPrice = $row['unit_price'] = (new \Vtiger_MultiCurrency_UIType())->getValueForCurrency($row['unit_price'], \App\Fields\Currency::getDefault()['id']);
			}
			$taxParam = \Api\Portal\Record::getTaxParam($availableTaxes, $row['taxes'], $regionalTaxes);
			$row['unit_gross'] = $unitPrice + (new \Vtiger_Tax_InventoryField())->getTaxValue($taxParam, $unitPrice, $taxConfigAggregation);
			$products[$row['id']]['rawData'] = $row;
			$products[$row['id']]['data'] = $this->getRecordFromRow($row, $fieldsModel);
		}
		$dataReader->close();
		return $products;
	}

	/**
	 * Get record from row.
	 *
	 * @param array                 $row
	 * @param \Vtiger_Field_Model[] $fieldsModel
	 *
	 * @return array
	 */
	private function getRecordFromRow(array $row, array $fieldsModel): array
	{
		$record = ['recordLabel' => \App\Record::getLabel($row['id'])];
		$recordModel = \Vtiger_Record_Model::getCleanInstance($this->controller->request->getModule());
		foreach ($fieldsModel as $fieldName => $fieldModel) {
			if (isset($row[$fieldName])) {
				$recordModel->set($fieldName, $row[$fieldName]);
				$record[$fieldName] = $recordModel->getDisplayValue($fieldName, $row['id'], true);
			}
		}
		$record['unit_price'] = \CurrencyField::convertToUserFormatSymbol($row['unit_price']);
		$record['unit_gross'] = \CurrencyField::convertToUserFormatSymbol($row['unit_gross']);
		$record['id'] = $row['id'];
		return $record;
	}
}

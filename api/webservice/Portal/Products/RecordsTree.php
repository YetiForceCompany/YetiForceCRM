<?php
/**
 * The file contains: Description class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Portal\Products;

/**
 * Class Description.
 */
class RecordsTree extends \Api\Portal\BaseModule\RecordsList
{
	/**
	 * Permission type.
	 *
	 * @var int
	 */
	private $permissionType;

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
		$this->permissionType = (int) \App\User::getCurrentUserModel()->get('permission_type');
		$this->isUserPermissions = \Api\Portal\Privilege::USER_PERMISSIONS === $this->permissionType;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQuery()
	{
		if ($this->isUserPermissions) {
			$queryGenerator = parent::getQuery();
		} else {
			$pricebookId = \Vtiger_Record_Model::getInstanceById($this->getParentCrmId(), 'Accounts')->get('pricebook_id');
			if (empty($pricebookId)) {
				$queryGenerator = parent::getQuery();
			} else {
				$queryGenerator = parent::getQuery();
				$queryGenerator->setCustomColumn('vtiger_pricebookproductrel.listprice');
				$queryGenerator->addJoin([
					'LEFT JOIN',
					'vtiger_pricebookproductrel',
					"vtiger_pricebookproductrel.pricebookid={$pricebookId} AND vtiger_pricebookproductrel.productid = vtiger_products.productid"]
				);
			}
		}
		$queryGenerator->setCustomColumn('u_yf_istorages_products.qtyinstock as storage_qtyinstock');
		$queryGenerator->addJoin([
			'LEFT JOIN',
			'u_yf_istorages_products',
			"u_yf_istorages_products.crmid={$this->getUserStorageId()} AND u_yf_istorages_products.relcrmid = vtiger_products.productid"]
		);
		$queryGenerator->setCustomColumn('a_yf_taxes_global.value as tax_value');
		$queryGenerator->addJoin([
			'LEFT JOIN',
			'a_yf_taxes_global',
			'a_yf_taxes_global.id = vtiger_products.taxes']
		);
		return $queryGenerator;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function isRawData(): bool
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function createRecordFromRow(array $row, array $fieldsModel): array
	{
		$listprice = $row['listprice'] ?? null;
		$record = parent::createRecordFromRow($row, $fieldsModel);
		if (!$this->isUserPermissions && !empty($listprice)) {
			$record['unit_price'] = \CurrencyField::convertToUserFormatSymbol($listprice);
		}
		$record['unit_gross'] = \CurrencyField::convertToUserFormatSymbol($row['unit_price'] + ($row['unit_price'] * ($row['tax_value'] ?? 0)) / 100.00);
		return $record;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function createRawDataFromRow(array $row): array
	{
		$row = parent::createRawDataFromRow($row, $fieldsModel);
		if (!$this->isUserPermissions && !empty($row['listprice'])) {
			$row['unit_price'] = $row['listprice'];
		}
		$row['qtyinstock'] = $row['storage_qtyinstock'] ?? 0;
		$row['unit_gross'] = $row['unit_price'] + ($row['unit_price'] * ($row['tax_value'] ?? 0) / 100.0);
		return $row;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function createHeader(array $fieldsModel): array
	{
		$headers = parent::createHeader($fieldsModel);
		$headers['unit_gross'] = \App\Language::translate('LBL_GRAND_TOTAL');
		return $headers;
	}
}

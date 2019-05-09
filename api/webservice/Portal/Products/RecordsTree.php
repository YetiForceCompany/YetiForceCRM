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
	 * Construct.
	 */
	public function __construct()
	{
		$this->permissionType = (int) \App\User::getCurrentUserModel()->get('permission_type');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQuery()
	{
		if (\Api\Portal\Privilege::USER_PERMISSIONS === $this->permissionType) {
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
		$row = parent::createRecordFromRow($row, $fieldsModel);
		if (\Api\Portal\Privilege::USER_PERMISSIONS !== $this->permissionType && !empty($listprice)) {
			$row['unit_price'] = \CurrencyField::convertToUserFormat($listprice, null, true);
		}
		return $row;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function createRawDataFromRow(array $row): array
	{
		$row = parent::createRawDataFromRow($row, $fieldsModel);
		if (\Api\Portal\Privilege::USER_PERMISSIONS !== $this->permissionType && !empty($row['listprice'])) {
			$row['unit_price'] = $row['listprice'];
		}
		return $row;
	}
}

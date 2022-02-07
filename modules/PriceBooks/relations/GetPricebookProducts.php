<?php
/**
 * Main file that includes basic operations on relations.
 *
 * @package   Relation
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * PriceBooks_GetPricebookProducts_Relation class.
 */
class PriceBooks_GetPricebookProducts_Relation extends \App\Relation\RelationAbstraction
{
	/**
	 * @var string Name of the table that stores relations.
	 */
	public const TABLE_NAME = 'vtiger_pricebookproductrel';

	/** {@inheritdoc} */
	public function getRelationType(): int
	{
		return Vtiger_Relation_Model::RELATION_M2M;
	}

	/** {@inheritdoc} */
	public function getQuery()
	{
		$tableName = self::TABLE_NAME;
		$queryGenerator = $this->relationModel->getQueryGenerator();
		$queryGenerator->setCustomColumn("{$tableName}.listprice")
			->addJoin(['INNER JOIN', $tableName, $queryGenerator->getColumnName('id') . " = {$tableName}.productid"])
			->addJoin(['INNER JOIN', 'vtiger_pricebook', "vtiger_pricebook.pricebookid = {$tableName}.pricebookid"])
			->addNativeCondition(['vtiger_pricebook.pricebookid' => $this->relationModel->get('parentRecord')->getId()]);
	}

	/** {@inheritdoc} */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		return (bool) App\Db::getInstance()->createCommand()
			->delete(self::TABLE_NAME, ['pricebookid' => $sourceRecordId, 'productid' => $destinationRecordId])
			->execute();
	}

	/** {@inheritdoc} */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		$result = false;
		$data = ['pricebookid' => $sourceRecordId, 'productid' => $destinationRecordId];
		if (!(new App\Db\Query())->from(self::TABLE_NAME)->where($data)->exists()) {
			$data['listprice'] = 0;
			$data['usedcurrency'] = \Vtiger_Record_Model::getInstanceById($sourceRecordId)->get('currency_id');
			$result = (bool) \App\Db::getInstance()->createCommand()->insert(self::TABLE_NAME, $data)->execute();
		}
		return $result;
	}

	/** {@inheritdoc} */
	public function transfer(int $relatedRecordId, int $fromRecordId, int $toRecordId): bool
	{
		return (bool) \App\Db::getInstance()->createCommand()->update(self::TABLE_NAME,
		['pricebookid' => $toRecordId], ['pricebookid' => $fromRecordId, 'productid' => $relatedRecordId])->execute();
	}
}

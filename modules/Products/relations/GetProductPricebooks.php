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
 * Products_GetProductPricebooks_Relation class.
 */
class Products_GetProductPricebooks_Relation extends \App\Relation\RelationAbstraction
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
		$queryGenerator = $this->relationModel->getQueryGenerator()
			->setCustomColumn("{$tableName}.listprice")
			->addJoin(['INNER JOIN', $tableName, "vtiger_pricebook.pricebookid = {$tableName}.pricebookid"])
			->addNativeCondition(["{$tableName}.productid" => $this->relationModel->get('parentRecord')->getId()]);
		$queryByProduct = new \App\QueryGenerator($this->relationModel->getParentModuleModel()->getName());
		if (($fieldModel = $queryByProduct->getModuleField('unit_price')) && $fieldModel->isActiveField()) {
			$queryByProduct->setFields(['id']);
			$queryByProduct->permissions = false;
			$queryGenerator->addJoin(['INNER JOIN', $fieldModel->getTableName(), $queryByProduct->getColumnName('id') . " = {$tableName}.productid"])
				->setCustomColumn($queryByProduct->getColumnName($fieldModel->getName()))
				->addNativeCondition([$queryByProduct->getColumnName('id') => $queryByProduct->createQuery()]);
		}
	}

	/** {@inheritdoc} */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		return (bool) App\Db::getInstance()->createCommand()
			->delete(self::TABLE_NAME, ['pricebookid' => $destinationRecordId, 'productid' => $sourceRecordId])
			->execute();
	}

	/** {@inheritdoc} */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		$result = false;
		$data = ['pricebookid' => $destinationRecordId, 'productid' => $sourceRecordId];
		if (!(new App\Db\Query())->from(self::TABLE_NAME)->where($data)->exists()) {
			$data['listprice'] = 0;
			$data['usedcurrency'] = \Vtiger_Record_Model::getInstanceById($destinationRecordId)->get('currency_id');
			$result = (bool) \App\Db::getInstance()->createCommand()->insert(self::TABLE_NAME, $data)->execute();
		}
		return $result;
	}

	/** {@inheritdoc} */
	public function transfer(int $relatedRecordId, int $fromRecordId, int $toRecordId): bool
	{
		return (bool) \App\Db::getInstance()->createCommand()->update(self::TABLE_NAME,
		['productid' => $toRecordId], ['productid' => $fromRecordId, 'pricebookid' => $relatedRecordId])->execute();
	}
}

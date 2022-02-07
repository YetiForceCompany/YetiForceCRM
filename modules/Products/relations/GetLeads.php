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
 * Products_GetLeads_Relation class.
 */
class Products_GetLeads_Relation extends \App\Relation\RelationAbstraction
{
	/**
	 * @var string Name of the table that stores relations.
	 */
	public const TABLE_NAME = 'vtiger_seproductsrel';

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
		$queryGenerator->addJoin(['INNER JOIN', $tableName, "{$tableName}.crmid = " . $queryGenerator->getColumnName('id')])
			->addJoin(['INNER JOIN', 'vtiger_products', "{$tableName}.productid = vtiger_products.productid"])
			->addNativeCondition(['vtiger_products.productid' => $this->relationModel->get('parentRecord')->getId()]);
	}

	/** {@inheritdoc} */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		return (bool) App\Db::getInstance()->createCommand()
			->delete(self::TABLE_NAME, ['productid' => $sourceRecordId, 'crmid' => $destinationRecordId])
			->execute();
	}

	/** {@inheritdoc} */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		$result = false;
		$data = ['crmid' => $destinationRecordId, 'productid' => $sourceRecordId];
		if (!(new App\Db\Query())->from(self::TABLE_NAME)->where($data)->exists()) {
			$data['setype'] = $this->relationModel->getRelationModuleName();
			$data['rel_created_user'] = App\User::getCurrentUserRealId();
			$data['rel_created_time'] = date('Y-m-d H:i:s');
			$result = App\Db::getInstance()->createCommand()->insert(self::TABLE_NAME, $data)->execute();
		}
		return (bool) $result;
	}

	/** {@inheritdoc} */
	public function transfer(int $relatedRecordId, int $fromRecordId, int $toRecordId): bool
	{
		return (bool) \App\Db::getInstance()->createCommand()->update(self::TABLE_NAME,
		['productid' => $toRecordId], ['crmid' => $relatedRecordId, 'productid' => $fromRecordId])->execute();
	}
}

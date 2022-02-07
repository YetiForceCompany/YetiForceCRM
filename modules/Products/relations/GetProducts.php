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
 * Products_GetProducts_Relation class.
 */
class Products_GetProducts_Relation extends \App\Relation\RelationAbstraction
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
		$relModuleName = $this->relationModel->getRelationModuleName();
		$sourceModuleName = $this->relationModel->getParentModuleModel()->getName();

		if ($relModuleName === $sourceModuleName) {
			$queryGenerator->addJoin([
				'INNER JOIN',
				$tableName,
				"{$tableName}.crmid = " . $queryGenerator->getColumnName('id') . " AND {$tableName}.setype=:module",
				[':module' => $sourceModuleName],
			])->addNativeCondition(["{$tableName}.productid" => $this->relationModel->get('parentRecord')->getId()]);
		} else {
			$moduleModel = $this->relationModel->getParentModuleModel();
			$baseTableName = $moduleModel->get('basetable');
			$columnFullName = "{$baseTableName}.{$moduleModel->get('basetableid')}";
			$queryGenerator->addJoin(['INNER JOIN',	$tableName,	"{$tableName}.productid = " . $queryGenerator->getColumnName('id')])
				->addJoin(['INNER JOIN', $baseTableName, "{$columnFullName} = {$tableName}.crmid"])
				->addNativeCondition([$columnFullName => $this->relationModel->get('parentRecord')->getId()]);
		}
	}

	/** {@inheritdoc} */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		if ($this->relationModel->getRelationModuleName() === $this->relationModel->getParentModuleModel()->getName()) {
			$temp = $sourceRecordId;
			$sourceRecordId = $destinationRecordId;
			$destinationRecordId = $temp;
		}
		return (bool) App\Db::getInstance()->createCommand()
			->delete(self::TABLE_NAME, ['productid' => $destinationRecordId, 'crmid' => $sourceRecordId])
			->execute();
	}

	/** {@inheritdoc} */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		$result = false;
		$data = $where = ['crmid' => $sourceRecordId, 'productid' => $destinationRecordId];
		$sourceModuleName = $this->relationModel->getParentModuleModel()->getName();
		if ($this->relationModel->getRelationModuleName() === $sourceModuleName) {
			$data = ['crmid' => $destinationRecordId, 'productid' => $sourceRecordId];
			$where = ['or', $data, ['productid' => $destinationRecordId, 'setype' => $sourceModuleName]];
		}
		if (!(new App\Db\Query())->from(self::TABLE_NAME)->where($where)->exists()) {
			$data['setype'] = $sourceModuleName;
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
		['crmid' => $toRecordId], ['crmid' => $fromRecordId, 'productid' => $relatedRecordId])->execute();
	}
}

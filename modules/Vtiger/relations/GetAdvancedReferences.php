<?php
/**
 * Get advanced references file that includes basic operations on relations.
 *
 * @package   Relation
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Vtiger_GetAdvancedReferences_Relation class.
 */
class Vtiger_GetAdvancedReferences_Relation extends \App\Relation\RelationAbstraction
{
	/** {@inheritdoc} */
	public function getRelationType(): int
	{
		return Vtiger_Relation_Model::RELATION_AR;
	}

	/** {@inheritdoc} */
	public function getQuery()
	{
		$fieldModel = $this->relationModel->getRelationField();
		$queryGenerator = $this->relationModel->getQueryGenerator();
		$entityModel = $queryGenerator->getEntityModel();
		$parentTableName = $entityModel->table_name;
		$parentTableId = $entityModel->table_index;
		$queryGenerator->setDistinct($parentTableName . '.' . $parentTableId);
		$relationModuleModel = Vtiger_Inventory_Model::getInstance($this->relationModel->getRelationModuleModel()->getName());
		$inventoryTable = $relationModuleModel->getDataTableName();
		$subQuery = (new \App\Db\Query())->select('crmid')->from($inventoryTable)
			->where(["{$inventoryTable}.{$fieldModel->getColumnName()}" => $this->relationModel->get('parentRecord')->getId()]);
		$queryGenerator->addNativeCondition(["{$parentTableName}.{$parentTableId}" => $subQuery]);
	}

	/** {@inheritdoc} */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		return false;
	}

	/** {@inheritdoc} */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		return false;
	}

	/** {@inheritdoc} */
	public function transfer(int $relatedRecordId, int $fromRecordId, int $toRecordId): bool
	{
		return false;
	}
}

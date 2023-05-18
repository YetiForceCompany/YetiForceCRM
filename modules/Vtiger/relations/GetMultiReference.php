<?php
/**
 * Main file that includes basic operations on relations.
 *
 * @package   Relation
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Vtiger_GetMultiReference_Relation class.
 */
class Vtiger_GetMultiReference_Relation extends \App\Relation\RelationAbstraction
{
	/** {@inheritdoc} */
	public function getRelationType(): int
	{
		return Vtiger_Relation_Model::RELATION_MR;
	}

	/** {@inheritdoc} */
	public function getQuery()
	{
		$relationField = $this->relationModel->getRelationField();
		$queryGenerator = $this->relationModel->getQueryGenerator();
		$queryGenerator->addCondition($relationField->getName(), $this->relationModel->getParentRecord()->getId(), 'c');
	}

	/** {@inheritdoc} */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		return false;
	}

	/** {@inheritdoc} */
	public function transfer(int $relatedRecordId, int $fromRecordId, int $toRecordId): bool
	{
		return false;
	}

	/** {@inheritdoc} */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		return false;
	}
}

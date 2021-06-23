<?php
/**
 * Main file that includes basic operations on relations.
 *
 * @package   Relation
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
use App\Relation\RelationInterface;

/**
 * ModTracker_GetRelatedRecord_Relation class.
 */
class ModComments_GetRelatedRecord_Relation implements RelationInterface
{
	/** {@inheritdoc} */
	public function getRelationType(): int
	{
		return Vtiger_Relation_Model::RELATION_O2M;
	}

	/** {@inheritdoc} */
	public function getQuery()
	{
		$queryGenerator = $this->relationModel->getQueryGenerator();
		$queryGenerator->addCondition('related_to', $this->relationModel->get('parentRecord')->getId(), 'eid');
	}

	/** {@inheritdoc} */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		return false;
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
}

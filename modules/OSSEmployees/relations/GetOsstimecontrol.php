<?php
/**
 * Main file that includes basic operations on relations.
 *
 * @package   Relation
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use App\Relation\RelationInterface;

/**
 * OSSEmployees_GetOsstimecontrol_Relation class.
 */
class OSSEmployees_GetOsstimecontrol_Relation implements RelationInterface
{
	/** {@inheritdoc} */
	public function getRelationType(): int
	{
		return Vtiger_Relation_Model::RELATION_M2M;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQuery()
	{
		$this->relationModel->getQueryGenerator()
			->addNativeCondition(['vtiger_crmentity.smownerid' => $this->relationModel->get('parentRecord')->get('assigned_user_id')]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function transfer(int $relatedRecordId, int $fromRecordId, int $toRecordId): bool
	{
		return false;
	}
}

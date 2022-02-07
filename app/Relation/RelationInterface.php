<?php
/**
 * Relation interface class file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Relation;

/**
 * Relation interface class.
 */
interface RelationInterface
{
	/**
	 * Get relation type.
	 *
	 * @return int
	 */
	public function getRelationType(): int;

	/**
	 * Function adds relation conditions to query object.
	 */
	public function getQuery();

	/**
	 * Delete relation.
	 *
	 * @param int $sourceRecordId      Specifies parent record ID from where we remove relation
	 * @param int $destinationRecordId Specifies record ID from related module. This record will disappear from the list of parent module's related records
	 *
	 * @return bool
	 */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool;

	/**
	 * Create relation.
	 *
	 * @param int $sourceRecordId      Specifies parent record ID where we add relation
	 * @param int $destinationRecordId Specifies record ID from related module. This record will appear on the list of parent module's related records
	 *
	 * @return bool
	 */
	public function create(int $sourceRecordId, int $destinationRecordId): bool;

	/**
	 * Function moves related records from source to target.
	 *
	 * @param int $relatedRecordId
	 * @param int $fromRecordId
	 * @param int $toRecordId
	 *
	 * @return bool
	 */
	public function transfer(int $relatedRecordId, int $fromRecordId, int $toRecordId): bool;
}

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
 * Documents_GetRelatedRecord_Relation class.
 */
class Documents_GetRelatedRecord_Relation extends \App\Relation\RelationAbstraction
{
	/**
	 * @var string Name of the table that stores relations.
	 */
	public const TABLE_NAME = 'vtiger_senotesrel';

	/** {@inheritdoc} */
	public function getRelationType(): int
	{
		return Vtiger_Relation_Model::RELATION_M2M;
	}

	/** {@inheritdoc} */
	public function getQuery()
	{
		$queryGenerator = $this->relationModel->getQueryGenerator();
		$tableName = self::TABLE_NAME;
		$queryGenerator->addJoin(['INNER JOIN', $tableName, "{$tableName}.crmid = vtiger_crmentity.crmid"]);
		$queryGenerator->addNativeCondition(["{$tableName}.notesid" => $this->relationModel->get('parentRecord')->getId()]);
	}

	/**
	 * Load advanced conditions relationship by custom column.
	 *
	 * @param App\QueryGenerator $queryGenerator QueryGenerator for the list of records to be tapered based on the relationship
	 * @param array              $searchParam    Related record for which we are filtering the list of records
	 *
	 * @return void
	 */
	public function loadAdvancedConditionsByColumns(App\QueryGenerator $queryGenerator, array $searchParam): void
	{
		$tableName = static::TABLE_NAME;
		$queryGenerator->addJoin(['INNER JOIN', $tableName, "{$tableName}.notesid = vtiger_crmentity.crmid"])
			->addNativeCondition(["{$tableName}.crmid" => $searchParam['value']]);
	}

	/**
	 * Get configuration advanced conditions relationship by custom column..
	 *
	 * @return array
	 */
	public function getConfigAdvancedConditionsByColumns(): array
	{
		$modules = (new \App\Db\Query())->select(['vtiger_tab.name'])
			->from('vtiger_relatedlists')
			->innerJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_relatedlists.tabid')
			->where(['and', ['vtiger_tab.presence' => 0], ['vtiger_relatedlists.name' => 'getAttachments']])
			->column();
		foreach ($modules as $key => $moduleName) {
			if (!\App\Privilege::isPermitted($moduleName)) {
				unset($modules[$key]);
			}
		}
		return ['relatedModules' => $modules];
	}

	/** {@inheritdoc} */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		return (bool) App\Db::getInstance()->createCommand()->delete(self::TABLE_NAME, [
			'notesid' => $sourceRecordId,
			'crmid' => $destinationRecordId,
		])->execute();
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

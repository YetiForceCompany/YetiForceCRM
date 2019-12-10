<?php
/**
 * Includes RelatedMembers relation.
 *
 * @package   Relation
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
/**
 * Class GetRelatedMembers.
 */
class Vtiger_GetRelatedMembers_Relation extends Vtiger_GetRelatedContacts_Relation
{
	/**
	 * {@inheritdoc}
	 */
	public const TABLE_NAME = 'u_yf_relations_members_entity';

	/**
	 * {@inheritdoc}
	 */
	public function getQuery()
	{
		$record = $this->relationModel->get('parentRecord')->getId();
		$tableName = static::TABLE_NAME;
		$this->relationModel->getQueryGenerator()
			->addJoin(['INNER JOIN', $tableName, "({$tableName}.relcrmid = vtiger_crmentity.crmid OR {$tableName}.crmid = vtiger_crmentity.crmid)"])
			->addNativeCondition(['or', ["{$tableName}.crmid" => $record], ["{$tableName}.relcrmid" => $record]])
			->setCustomColumn(["{$tableName}.status_rel"])
			->setCustomColumn(["{$tableName}.rating_rel"])
			->setCustomColumn(["{$tableName}.comment_rel"])
			->setDistinct('id');
		return $this->relationModel->getQueryGenerator();
	}
}

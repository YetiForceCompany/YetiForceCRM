<?php
/**
 * Includes RelatedMembers relation.
 *
 * @package   Relation
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Class GetRelatedMembers.
 */
class Occurrences_GetRelatedMembers_Relation extends Vtiger_GetRelatedList_Relation
{
	/** {@inheritdoc} */
	public const TABLE_NAME = 'u_#__relations_members_entity';

	/**
	 * Field custom list.
	 *
	 * @var array
	 */
	public $customFields = [
		'status_rel' => [
			'label' => 'LBL_STATUS_REL',
			'uitype' => 16,
		],
		'comment_rel' => [
			'label' => 'LBL_COMMENT_REL',
			'uitype' => 21,
			'maximumlength' => 65535
		],
		'rel_created_user' => [
			'label' => 'LBL_RELATION_CREATED_USER',
			'uitype' => 52,
			'displaytype' => 10
		],
	];

	/**
	 * Field list.
	 *
	 * @param bool $editable
	 *
	 * @return array
	 */
	public function getFields(bool $editable = false)
	{
		$fields = [];
		$sourceModule = $this->relationModel->getParentModuleModel();
		if ('Occurrences' !== $sourceModule->getName()) {
			$sourceModule = $this->relationModel->getRelationModuleModel();
		}
		foreach ($this->customFields as $fieldName => $data) {
			$field = new \Vtiger_Field_Model();
			$field->set('name', $fieldName)->set('column', $fieldName)->set('table', static::TABLE_NAME)->set('fromOutsideList', true)->setModule($sourceModule);
			foreach ($data as $key => $value) {
				$field->set($key, $value);
			}
			if (!$editable || !$field->isEditableReadOnly()) {
				$fields[$fieldName] = $field;
			}
		}
		return $fields;
	}

	/** {@inheritdoc} */
	public function getQuery()
	{
		parent::getQuery();
		$tableName = static::TABLE_NAME;
		$queryGenerator = $this->relationModel->getQueryGenerator();
		foreach (array_keys($this->customFields) as $fieldName) {
			$queryGenerator->setCustomColumn(["{$tableName}.{$fieldName}"]);
		}
	}

	/** {@inheritdoc} */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		$result = false;
		if (!$this->getRelationData($sourceRecordId, $destinationRecordId)) {
			$result = \App\Db::getInstance()->createCommand()->insert(static::TABLE_NAME, [
				'crmid' => $sourceRecordId,
				'relcrmid' => $destinationRecordId,
				'rel_created_user' => \App\User::getCurrentUserRealId(),
			])->execute();
		}
		return $result;
	}

	/**
	 * updateRelationData function.
	 *
	 * @param int   $sourceRecordId
	 * @param int   $destinationRecordId
	 * @param array $updateData
	 *
	 * @return bool
	 */
	public function updateRelationData(int $sourceRecordId, int $destinationRecordId, array $updateData): bool
	{
		$conditions = [
			'or',
			['crmid' => $sourceRecordId, 'relcrmid' => $destinationRecordId],
			['crmid' => $destinationRecordId, 'relcrmid' => $sourceRecordId],
		];
		$result = (bool) $this->getRelationData($sourceRecordId, $destinationRecordId);
		if ($result) {
			$result = (bool) \App\Db::getInstance()->createCommand()->update(static::TABLE_NAME, $updateData, $conditions)->execute();
		}
		return $result;
	}

	/**
	 * Get relation data.
	 *
	 * @param int $sourceRecordId
	 * @param int $destinationRecordId
	 *
	 * @return array
	 */
	public function getRelationData(int $sourceRecordId, int $destinationRecordId)
	{
		return (new \App\Db\Query())->from(static::TABLE_NAME)->where([
			'or',
			['crmid' => $sourceRecordId, 'relcrmid' => $destinationRecordId],
			['crmid' => $destinationRecordId, 'relcrmid' => $sourceRecordId],
		])->one();
	}
}

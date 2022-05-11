<?php
/**
 * Main file that includes basic operations on relations.
 *
 * @package   Relation
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * ModTracker_GetRelatedRecord_Relation class.
 */
class ModComments_GetRelatedRecord_Relation extends \App\Relation\RelationAbstraction
{
	/**
	 * Field custom list.
	 *
	 * @var array
	 */
	public $customFields = [
		'children_count' => [
			'label' => 'LBL_CHILDREN_COUNT',
			'uitype' => 7,
		],
	];

	/** {@inheritdoc} */
	public function getRelationType(): int
	{
		return Vtiger_Relation_Model::RELATION_O2M;
	}

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
			$field->set('name', $fieldName)->set('column', $fieldName)->set('table', 'vtiger_modcomments')->set('fromOutsideList', true)->setModule($sourceModule);
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
		$parentId = $this->relationModel->get('parentRecord')->getId();
		$queryGenerator = $this->relationModel->getQueryGenerator();
		$queryGenerator->addCondition('related_to', $parentId, 'eid');
		foreach (array_keys($this->customFields) as $fieldName) {
			$subQuery = (new \App\QueryGenerator('ModComments'))->setFields(['id'])->setSourceRecord($parentId)->createQuery()->select((new \yii\db\Expression('COUNT(1)')))->andWhere(['parent_comments' => new yii\db\Expression('id')])->groupBy(['parent_comments']);
			$queryGenerator->setCustomColumn([$fieldName => $subQuery]);
		}
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

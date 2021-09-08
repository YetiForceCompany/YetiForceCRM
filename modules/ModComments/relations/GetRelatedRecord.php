<?php
/**
 * Main file that includes basic operations on relations.
 *
 * @package   Relation
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
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

	/**
	 * Field custom list.
	 *
	 * @var array
	 */
	public $customFields = [
		'children_count' => [
			'label' => 'LBL_CHILDREN_COUNT',
			'uitype' => 7
		]
	];

	/**
	 * Field list.
	 *
	 * @return array
	 */
	public function getFields()
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
			$fields[$fieldName] = $field;
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

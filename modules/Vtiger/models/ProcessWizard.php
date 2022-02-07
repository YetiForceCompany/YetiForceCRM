<?php
/**
 * Process wizard base model file.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Process wizard base model class.
 */
class Vtiger_ProcessWizard_Model extends \App\Base
{
	/**
	 * The current process wizard map.
	 *
	 * @var array
	 */
	protected $wizardMap;
	/**
	 * Vtiger_Record_Model.
	 *
	 * @var Vtiger_Record_Model
	 */
	protected $recordModel;
	/**
	 * Current process step.
	 *
	 * @var array
	 */
	protected $step;

	/**
	 * Get instance model.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return self
	 */
	public static function getInstance(Vtiger_Record_Model $recordModel): self
	{
		$className = Vtiger_Loader::getComponentClassName('Model', 'ProcessWizard', $recordModel->getModuleName());
		$instance = new $className();
		$instance->recordModel = $recordModel;
		if (method_exists($instance, 'load')) {
			$instance->load();
		}
		$instance->loadGroup();
		$instance->loadConditions();
		return $instance;
	}

	/**
	 * Load current map group.
	 *
	 * @return void
	 */
	public function loadGroup(): void
	{
		if (isset($this->wizardMap[0]['groupConditions'])) {
			foreach ($this->wizardMap as $groupMap) {
				if (isset($groupMap['groupConditions']) && \App\Condition::checkConditions($groupMap['groupConditions'], $this->recordModel)) {
					$this->wizardMap = $groupMap['group'];
					return;
				}
			}
			$this->wizardMap = [];
		}
	}

	/**
	 * Load and check the process wizard conditions.
	 *
	 * @return void
	 */
	public function loadConditions(): void
	{
		foreach ($this->wizardMap as $id => &$map) {
			$map['id'] = $id;
			if (isset($map['conditionsStatus'])) {
				continue;
			}
			if (isset($map['conditions'])) {
				$map['conditionsStatus'] = \App\Condition::checkConditions($map['conditions'], $this->recordModel);
				if ($map['conditionsStatus']) {
					break;
				}
			}
		}
	}

	/**
	 * Get process wizard steps.
	 *
	 * @return array
	 */
	public function getSteps(): array
	{
		return $this->wizardMap;
	}

	/**
	 * Get active process wizard step.
	 *
	 * @return array|null
	 */
	public function getStep(): ?array
	{
		if (isset($this->step)) {
			return $this->step;
		}
		foreach ($this->wizardMap as $id => $map) {
			if ($map['conditionsStatus']) {
				return $this->step = $map;
			}
		}
		return $this->step = null;
	}

	/**
	 * Set the active step of the process wizard.
	 *
	 * @param int $id
	 *
	 * @return void
	 */
	public function setStep(int $id): void
	{
		if (empty($this->getStep()) || $id < $this->getStep()['id']) {
			$this->step = $this->wizardMap[$id];
		}
	}

	/**
	 * Get the blocks of the current step.
	 *
	 * @return array
	 */
	public function getStepBlocks(): array
	{
		$blocks = [];
		if ($step = $this->getStep()) {
			foreach ($step['blocks'] as $block) {
				switch ($block['type']) {
					case 'fields':
						$blocks[] = $this->getFieldsStructure($block);
						break;
					case 'relatedLists':
						$blocks[] = $this->getRelatedListStructure($block);
						break;
					case 'relatedListsFromReference':
						$blocks[] = $this->getRelatedListReferenceStructure($block);
						break;
					case 'description':
						$blocks[] = $block;
						break;
				}
			}
		}
		return $blocks;
	}

	/**
	 * Get fields structure for fields block type.
	 *
	 * @param array $block
	 *
	 * @return array
	 */
	public function getFieldsStructure(array $block): array
	{
		$fields = [];
		foreach ($block['fields'] as $field) {
			if (\is_array($field) && 'relatedField' === $field['type']) {
				$fieldValue = $this->recordModel->get($field['field']);
				if ($fieldValue && App\Record::isExists($fieldValue)) {
					$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($fieldValue);
					$fieldModel = $relatedRecordModel->getField($field['relatedField']);
					if ($fieldModel && $fieldModel->isViewable()) {
						$fieldModel->set('fieldvalue', $relatedRecordModel->get($field['relatedField']));
						$fieldModel->set('displaytype', 10);
						if (isset($field['label'])) {
							$fieldModel->set('label', $field['label']);
						}
						$fields[] = $fieldModel;
					}
				}
			} elseif (\is_array($field) && 'relatedMergedFields' === $field['type']) {
				if (App\Record::isExists($this->recordModel->get($field['field']))) {
					$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($this->recordModel->get($field['field']));
					$text = '';
					foreach ($field['relatedFields'] as $relatedField) {
						$fieldModel = $relatedRecordModel->getField($relatedField);
						if ($fieldModel) {
							if ($fieldModel->isViewable() && '' !== $relatedRecordModel->get($relatedField)) {
								$text .= $fieldModel->getDisplayValue($relatedRecordModel->get($relatedField), false, false, true) . ' ';
							}
						} elseif ('__EOL__' === $relatedField) {
							$text .= PHP_EOL;
						}
					}
					$fieldModel = \Vtiger_Field_Model::init($this->recordModel->getModuleName(), [
						'uitype' => 19,
						'label' => $field['label'],
						'fieldvalue' => $text,
						'displaytype' => 10,
						'isViewableInDetailView' => true,
					], $field['name']);
					$fields[] = $fieldModel;
				}
			} elseif (\is_array($field) && 'relatedField2' === $field['type']) {
				$fieldValue = $this->recordModel->get($field['field']);
				if ($fieldValue && App\Record::isExists($fieldValue)) {
					$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($fieldValue);
					$relatedFieldValue = $relatedRecordModel->get($field['relatedField']);
					if ($relatedFieldValue && App\Record::isExists($relatedFieldValue)) {
						$relatedRecordModel2 = \Vtiger_Record_Model::getInstanceById($relatedFieldValue);
						$fieldModel = $relatedRecordModel2->getField($field['relatedField2']);
						if ($fieldModel && $fieldModel->isViewable()) {
							$fieldModel->set('fieldvalue', $relatedRecordModel2->get($field['relatedField2']));
							$fieldModel->set('displaytype', 10);
							if (isset($field['label'])) {
								$fieldModel->set('label', $field['label']);
							}
							$fields[] = $fieldModel;
						}
					}
				}
			} else {
				$fieldModel = $this->recordModel->getField($field);
				if ($fieldModel && $fieldModel->isViewable()) {
					$fieldModel->set('fieldvalue', $this->recordModel->get($field));
					$fields[] = $fieldModel;
				}
			}
		}
		if (empty($block['icon'])) {
			$block['icon'] = '';
		}
		$block['fieldsStructure'] = $fields;
		return $block;
	}

	/**
	 * Get structure for related lists block type.
	 *
	 * @param array $block
	 *
	 * @return array
	 */
	public function getRelatedListStructure(array $block): array
	{
		$relation = Vtiger_Relation_Model::getInstanceById($block['relationId']);
		$block['relationStructure'] = Vtiger_Link_Model::getInstanceFromValues([
			'linklabel' => $block['label'] ?? $relation->get('label'),
			'linkurl' => $relation->getListUrl($this->recordModel) . ($block['relationConditions'] ?? ''),
			'linkicon' => '',
			'relatedModuleName' => $relation->getRelationModuleName(),
			'relationId' => $relation->getId(),
		]);
		if (empty($block['icon'])) {
			$block['icon'] = '';
		}
		return $block;
	}

	/**
	 * Get structure for related lists block type for reference record.
	 *
	 * @param array $block
	 *
	 * @return array
	 */
	public function getRelatedListReferenceStructure(array $block): array
	{
		$fieldValue = $this->recordModel->get($block['referenceField']);
		if ($fieldValue && App\Record::isExists($fieldValue)) {
			$relation = Vtiger_Relation_Model::getInstanceById($block['relationId']);
			$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($fieldValue);
			$block['relationStructure'] = Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => ($block['label'] ?? $relation->get('label')),
				'linkurl' => $relation->getListUrl($relatedRecordModel) . ($block['relationConditions'] ?? ''),
				'linkicon' => '',
				'relatedModuleName' => $relation->getRelationModuleName(),
				'relationId' => $relation->getId(),
			]);
		}
		if (empty($block['icon'])) {
			$block['icon'] = '';
		}
		return $block;
	}

	/**
	 * Get the actions of the current step..
	 *
	 * @return array
	 */
	public function getActions(): array
	{
		$actions = [];
		if (($step = $this->getStep()) && !empty($step['actions']) && $step['conditionsStatus']) {
			foreach ($step['actions'] as $action) {
				if (!isset($action['permissions']) || $action['permissions']) {
					$actions[] = Vtiger_Link_Model::getInstanceFromValues($action);
				}
			}
		}
		return $actions;
	}

	/**
	 * Check permissions to step.
	 *
	 * @return bool
	 */
	public function checkPermissionsToStep(): bool
	{
		$step = $this->getStep();
		if (isset($step['permissionsToStep'])) {
			if (\is_bool($step['permissionsToStep'])) {
				return $step['permissionsToStep'];
			}
			if (\is_callable($step['permissionsToStep'])) {
				return \call_user_func($step['permissionsToStep']);
			}
		}
		return true;
	}
}

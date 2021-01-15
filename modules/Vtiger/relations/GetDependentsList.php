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
 * Vtiger_GetDependentsList_Relation class.
 */
class Vtiger_GetDependentsList_Relation implements RelationInterface
{
	/** {@inheritdoc} */
	public function getRelationType(): int
	{
		return Vtiger_Relation_Model::RELATION_O2M;
	}

	/** {@inheritdoc} */
	public function getQuery()
	{
		$fieldModel = $this->relationModel->getRelationField();
		$queryGenerator = $this->relationModel->getQueryGenerator();
		$queryGenerator->addNativeCondition([
			$fieldModel->getTableName() . '.' . $fieldModel->getColumnName() => $this->relationModel->get('parentRecord')->getId(),
		]);
		$queryGenerator->addTableToQuery($fieldModel->getTableName());
	}

	/** {@inheritdoc} */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		$moduleModel = $this->relationModel->getRelationModuleModel();
		$parentModuleName = $this->relationModel->getParentModuleModel()->getName();
		$result = false;
		if ($fields = $moduleModel->getReferenceFieldsForModule($parentModuleName)) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($destinationRecordId, $moduleModel);
			foreach ($fields as $fieldModel) {
				if ((int) $recordModel->get($fieldModel->getName()) === (int) $sourceRecordId) {
					$recordModel->set($fieldModel->getName(), 0);
					$result = true;
				}
			}
			$recordModel->save();
		} else {
			\App\Log::warning("Incorrectly deleted relationship: {$sourceRecordId},{$moduleModel->getName()},{$parentModuleName},{$destinationRecordId}");
		}
		return $result;
	}

	/** {@inheritdoc} */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		return true;
	}

	/** {@inheritdoc} */
	public function transfer(int $relatedRecordId, int $fromRecordId, int $toRecordId): bool
	{
		$result = false;
		$relationFieldModel = $this->relationModel->getRelationField();
		if ($relationFieldModel && $relationFieldModel->isEditable()) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($relatedRecordId, $this->relationModel->getRelationModuleName());
			if ($recordModel->isEditable() && $recordModel->get($relationFieldModel->getName()) === $fromRecordId) {
				$recordModel->set($relationFieldModel->getName(), $toRecordId);
				$recordModel->ext['modificationType'] = \ModTracker_Record_Model::TRANSFER_EDIT;
				$recordModel->save();
				$result = true;
			}
		}
		return $result;
	}
}

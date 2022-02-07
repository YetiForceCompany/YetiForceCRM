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
 * Vtiger_GetDependentsList_Relation class.
 */
class Vtiger_GetDependentsList_Relation extends \App\Relation\RelationAbstraction
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

	/**
	 * Load advanced conditions for filtering related records.
	 *
	 * @param App\QueryGenerator $queryGenerator QueryGenerator for the list of records to be tapered based on the relationship
	 *
	 * @return void
	 */
	public function loadAdvancedConditionsByRelationId(App\QueryGenerator $queryGenerator): void
	{
		$fieldModel = $this->relationModel->getRelationField();
		$advancedConditions = $queryGenerator->getAdvancedConditions();
		$relationQueryGenerator = $this->relationModel->getQueryGenerator();
		$relationQueryGenerator->setStateCondition($queryGenerator->getState());
		$relationQueryGenerator->setFields(['id']);
		if (!empty($advancedConditions['relationConditions'])) {
			$relationQueryGenerator->setConditions(\App\Condition::getConditionsFromRequest($advancedConditions['relationConditions']));
		}
		$query = $relationQueryGenerator->createQuery();
		$queryGenerator->addJoin(['INNER JOIN', $fieldModel->getTableName(), "vtiger_crmentity.crmid = {$fieldModel->getTableName()}.{$fieldModel->getColumnName()}"]);
		$queryGenerator->addNativeCondition([
			$fieldModel->getTableName() . '.' . $this->relationModel->getQueryGenerator()->getEntityModel()->tab_name_index[$fieldModel->getTableName()] => $query,
		]);
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
		$fieldModel = $this->relationModel->getRelationField();
		$queryGenerator->addJoin(['INNER JOIN', $fieldModel->getTableName(), "vtiger_crmentity.crmid = {$fieldModel->getTableName()}.{$fieldModel->getColumnName()}"]);
		$queryGenerator->addNativeCondition([
			$fieldModel->getTableName() . '.' . $this->relationModel->getQueryGenerator()->getEntityModel()->tab_name_index[$fieldModel->getTableName()] => $searchParam['value'],
		]);
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

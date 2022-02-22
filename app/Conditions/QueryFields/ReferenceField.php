<?php

namespace App\Conditions\QueryFields;

/**
 * Reference Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class ReferenceField extends BaseField
{
	public function getTables()
	{
		return $this->queryGenerator->getReference($this->fieldModel->getName());
	}

	/**
	 * Get related column name.
	 *
	 * @return string[]
	 */
	public function getRelatedTableName(): array
	{
		if ($this->related) {
			if (\App\Config::performance('SEARCH_REFERENCE_BY_AJAX')) {
				return [$this->fieldModel->getTableName() . $this->related['sourceField'] . '.' . $this->fieldModel->getColumnName()];
			}
			$relatedModuleModel = \Vtiger_Module_Model::getInstance($this->related['relatedModule']);
			$fieldModel = $relatedModuleModel->getFieldByName($this->related['relatedField']);
			return $this->getRelatedTables($fieldModel->getReferenceList(), $this->related['relatedField']);
		}
		return $this->getRelatedTables($this->getTables(), $this->fieldModel->getName());
	}

	/**
	 * Get formatted column references from related records.
	 *
	 * @param array  $modules
	 * @param string $fieldName
	 *
	 * @return string[]
	 */
	public function getRelatedTables(array $modules, string $fieldName): array
	{
		$relatedTableName = [];
		foreach ($modules as $moduleName) {
			$formattedTables = [];
			$entityFieldInfo = \App\Module::getEntityInfo($moduleName);
			$relatedModuleModel = \Vtiger_Module_Model::getInstance($moduleName);
			$relTableIndexes = $relatedModuleModel->getEntityInstance()->tab_name_index;
			foreach ($entityFieldInfo['fieldnameArr'] as $column) {
				if ($relField = $relatedModuleModel->getFieldByColumn($column)) {
					$referenceTable = $relField->getTableName() . $fieldName;
					$this->queryGenerator->addJoin(['LEFT JOIN',
						"{$relField->getTableName()} {$referenceTable}",
						"{$this->getColumnName()} = {$referenceTable}.{$relTableIndexes[$relField->getTableName()]}",
					]);
					$formattedTables[] = "{$referenceTable}.{$column}";
				}
			}
			$relatedTableName[$moduleName] = \count($formattedTables) > 1 ? new \yii\db\Expression('CONCAT(' . implode(",' ',", $formattedTables) . ')') : current($formattedTables);
		}
		return $relatedTableName;
	}

	/**
	 * Auto operator.
	 *
	 * @return array
	 */
	public function operatorA(): array
	{
		if (\App\Config::performance('SEARCH_REFERENCE_BY_AJAX')) {
			if (false === strpos($this->value, '##')) {
				return [$this->getColumnName() => $this->value];
			}
			$condition = ['or'];
			foreach (explode('##', $this->value) as $value) {
				$condition[] = [$this->getColumnName() => $value];
			}
			return $condition;
		}
		return parent::operatorA();
	}

	/**
	 * Equals operator.
	 *
	 * @return array
	 */
	public function operatorE(): array
	{
		$condition = ['or'];
		foreach ($this->getRelatedTableName() as $formattedName) {
			$condition[] = ['=', $formattedName, $this->getValue()];
		}
		return $condition;
	}

	/**
	 * Equals Id operator.
	 *
	 * @return array
	 */
	public function operatorEid()
	{
		return [$this->getColumnName() => $this->getValue()];
	}

	/**
	 * Not equal operator.
	 *
	 * @return array
	 */
	public function operatorN(): array
	{
		$condition = ['or'];
		foreach ($this->getRelatedTableName() as $formattedName) {
			$condition[] = ['<>', $formattedName, $this->getValue()];
		}
		return $condition;
	}

	/**
	 * Starts with operator.
	 *
	 * @return array
	 */
	public function operatorS()
	{
		$condition = ['or'];
		foreach ($this->getRelatedTableName() as $formattedName) {
			$condition[] = ['like', $formattedName, $this->getValue() . '%', false];
		}
		return $condition;
	}

	/**
	 * Ends with operator.
	 *
	 * @return array
	 */
	public function operatorEw()
	{
		$condition = ['or'];
		foreach ($this->getRelatedTableName() as $formattedName) {
			$condition[] = ['like', $formattedName, '%' . $this->getValue(), false];
		}
		return $condition;
	}

	/**
	 * Contains operator.
	 *
	 * @return array
	 */
	public function operatorC(): array
	{
		$condition = ['or'];
		foreach ($this->getRelatedTableName() as $formattedName) {
			$condition[] = ['like', $formattedName, $this->getValue()];
		}
		return $condition;
	}

	/**
	 * Does not contain operator.
	 *
	 * @return array
	 */
	public function operatorK(): array
	{
		$condition = ['or'];
		foreach ($this->getRelatedTableName() as $formattedName) {
			$condition[] = ['not like', $formattedName, $this->getValue()];
		}
		return $condition;
	}

	/**
	 * Is empty operator.
	 *
	 * @return array
	 */
	public function operatorY(): array
	{
		return ['or',
			[$this->getColumnName() => null],
			['=', $this->getColumnName(), ''],
			['=', $this->getColumnName(), 0],
		];
	}

	/**
	 * Is not empty operator.
	 *
	 * @return array
	 */
	public function operatorNy(): array
	{
		return ['and',
			['not', [$this->getColumnName() => null]],
			['<>', $this->getColumnName(), ''],
			['<>', $this->getColumnName(), 0],
		];
	}

	/**
	 * Get order by.
	 *
	 * @param mixed $order
	 *
	 * @return array
	 */
	public function getOrderBy($order = false): array
	{
		$condition = [];
		if ($order && 'DESC' === strtoupper($order)) {
			foreach ($this->getRelatedTableName() as $formattedName) {
				$condition[(string) $formattedName] = SORT_DESC;
			}
		} else {
			foreach ($this->getRelatedTableName() as $formattedName) {
				$condition[(string) $formattedName] = SORT_ASC;
			}
		}
		return $condition;
	}
}

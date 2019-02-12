<?php

namespace App\QueryField;

/**
 * Reference Query Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
	 * @return string
	 */
	public function getRelatedTableName()
	{
		if ($this->related) {
			return [$this->fieldModel->getTableName() . $this->related['sourceField'] . '.' . $this->fieldModel->getColumnName()];
		}
		$relatedTableName = [];
		foreach ($this->getTables() as $moduleName) {
			$entityFieldInfo = \App\Module::getEntityInfo($moduleName);
			$referenceTable = $entityFieldInfo['tablename'] . $this->fieldModel->getFieldName();
			if (count($entityFieldInfo['fieldnameArr']) > 1) {
				$sqlString = 'CONCAT(';
				foreach ($entityFieldInfo['fieldnameArr'] as $column) {
					$sqlString .= "$referenceTable.$column,' ',";
				}
				$formattedName = new \yii\db\Expression(rtrim($sqlString, ',\' \',') . ')');
			} else {
				$formattedName = "$referenceTable.{$entityFieldInfo['fieldname']}";
			}
			$relatedTableName[$moduleName] = $formattedName;
			$this->queryGenerator->addJoin(['LEFT JOIN', $entityFieldInfo['tablename'] . ' ' . $referenceTable, $this->getColumnName() . " = $referenceTable.{$entityFieldInfo['entityidfield']}"]);
		}
		return $relatedTableName;
	}

	/**
	 * Auto operator.
	 *
	 * @return array
	 */
	public function operatorA()
	{
		if (\AppConfig::performance('SEARCH_REFERENCE_BY_AJAX')) {
			if (strpos($this->value, '##') === false) {
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
	public function operatorE()
	{
		$condition = ['or'];
		foreach ($this->getRelatedTableName() as $formattedName) {
			$condition[] = ['=', $formattedName, $this->getValue()];
		}
		return $condition;
	}

	/**
	 * Not equal operator.
	 *
	 * @return array
	 */
	public function operatorN()
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
	public function operatorC()
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
	public function operatorK()
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
	public function operatorY()
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
	public function operatorNy()
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
	 * @return array
	 */
	public function getOrderBy($order = false)
	{
		$condition = [];
		if ($order && strtoupper($order) === 'DESC') {
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

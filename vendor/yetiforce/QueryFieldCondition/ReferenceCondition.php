<?php
namespace App\QueryFieldCondition;

/**
 * Reference Query Condition Parser Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ReferenceCondition extends BaseFieldParser
{

	public function getTables()
	{
		return $this->queryGenerator->getReference($this->fieldModel->getName());
	}

	/**
	 * Get related column name
	 * @return string
	 */
	public function getRelatedTableName()
	{
		$relatedTableName = [];
		foreach ($this->getTables() as &$moduleName) {
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
	 * Equals operator
	 * @return array
	 */
	public function operatorE()
	{
		$condition = ['or'];
		foreach ($this->getRelatedTableName() as &$formattedName) {
			$condition[] = ['=', $formattedName, $this->value];
		}
		return $condition;
	}

	/**
	 * Not equal operator
	 * @return array
	 */
	public function operatorN()
	{
		$condition = ['and'];
		foreach ($this->getRelatedTableName() as &$formattedName) {
			$condition[] = ['<>', $formattedName, $this->value];
		}
		return $condition;
	}

	/**
	 * Starts with operator
	 * @return array
	 */
	public function operatorS()
	{
		$condition = ['or'];
		foreach ($this->getRelatedTableName() as &$formattedName) {
			$condition[] = ['like', $formattedName, $this->value . '%', false];
		}
		return $condition;
	}

	/**
	 * Ends with operator
	 * @return array
	 */
	public function operatorEw()
	{
		$condition = ['or'];
		foreach ($this->getRelatedTableName() as &$formattedName) {
			$condition[] = ['like', $formattedName, '%' . $this->value, false];
		}
		return $condition;
	}

	/**
	 * Contains operator
	 * @return array
	 */
	public function operatorC()
	{
		$condition = ['or'];
		foreach ($this->getRelatedTableName() as &$formattedName) {
			$condition[] = ['like', $formattedName, $this->value];
		}
		return $condition;
	}

	/**
	 * Does not contain operator
	 * @return array
	 */
	public function operatorK()
	{
		$condition = ['or'];
		foreach ($this->getRelatedTableName() as &$formattedName) {
			$condition[] = ['not like', $formattedName, $this->value];
		}
		return $condition;
	}

	/**
	 * Is empty operator
	 * @return array
	 */
	public function operatorY()
	{
		return ['or',
				[$this->getColumnName() => null],
				['=', $this->getColumnName(), ''],
				['=', $this->getColumnName(), 0]
		];
	}

	/**
	 * Is not empty operator
	 * @return array
	 */
	public function operatorNy()
	{
		return ['and',
				['not', [$this->getColumnName() => null]],
				['<>', $this->getColumnName(), ''],
				['<>', $this->getColumnName(), 0]
		];
	}
}

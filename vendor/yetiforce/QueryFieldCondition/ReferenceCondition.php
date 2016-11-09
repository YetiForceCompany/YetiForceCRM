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

	/**
	 * @var Related modules 
	 */
	protected $relatedModules;

	/**
	 * @var Related table name 
	 */
	protected $relatedTableName;

	public function getTables()
	{
		if ($this->relatedModules) {
			return $this->relatedModules;
		}
		return $this->relatedModules = $this->queryGenerator->getReference($this->fieldModel->getName());
	}

	/**
	 * Get related column name
	 * @return string
	 */
	public function getRelatedTableName()
	{
		if ($this->relatedTableName) {
			return $this->relatedTableName;
		}
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
			$this->queryGenerator->addJoin(['LEFT JOIN', $entityFieldInfo['tablename'] . ' ' . $referenceTable, $this->getColumnName() . " = $referenceTable.{$entityFieldInfo['entityidfield']}"]);
		}
		return $this->relatedTableName = $formattedName;
	}

	/**
	 * Equals operator
	 * @return array
	 */
	public function operatorE()
	{
		if (\AppConfig::performance('SEARCH_REFERENCE_BY_AJAX')) {
			$values = explode(',', $this->value);
			$condition = ['or'];
			foreach ($values as $value) {
				$condition[] = [$this->getColumnName() => ltrim($value)];
			}
			return $condition;
		}
		return ['=', $this->getRelatedTableName(), $this->value];
	}

	/**
	 * Not equal operator
	 * @return array
	 */
	public function operatorN()
	{
		if (\AppConfig::performance('SEARCH_REFERENCE_BY_AJAX')) {
			$values = explode(',', $this->value);
			$condition = ['and'];
			foreach ($values as $value) {
				$condition[] = ['<>', $this->getColumnName(), ltrim($value)];
			}
			return $condition;
		}
		return ['<>', $this->getRelatedTableName(), $this->value];
	}

	/**
	 * Starts with operator
	 * @return array
	 */
	public function operatorS()
	{
		return ['like', $this->getRelatedTableName(), $this->value . '%', false];
	}

	/**
	 * Ends with operator
	 * @return array
	 */
	public function operatorEw()
	{
		return ['like', $this->getRelatedTableName(), '%' . $this->value, false];
	}

	/**
	 * Contains operator
	 * @return array
	 */
	public function operatorC()
	{
		return ['like', $this->getRelatedTableName(), $this->value];
	}

	/**
	 * Does not contain operator
	 * @return array
	 */
	public function operatorK()
	{
		return ['not like', $this->getRelatedTableName(), $this->value];
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

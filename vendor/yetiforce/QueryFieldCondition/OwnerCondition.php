<?php
namespace App\QueryFieldCondition;

/**
 * Owner Query Condition Parser Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OwnerCondition extends BaseFieldParser
{

	/**
	 * @var Related modules 
	 */
	protected $relatedModules;

	/**
	 * @var Related table name 
	 */
	protected $relatedTableName;

	/**
	 * Get related column name
	 * @return string
	 */
	public function getRelatedTableName()
	{
		if ($this->relatedTableName) {
			return $this->relatedTableName;
		}
		$this->queryGenerator->addJoin(['LEFT JOIN', $entityFieldInfo['tablename'] . ' ' . $referenceTable, $this->getColumnName() . " = $referenceTable.{$entityFieldInfo['entityidfield']}"]);
		$this->queryGenerator->addJoin(['LEFT JOIN', $entityFieldInfo['tablename'] . ' ' . $referenceTable, $this->getColumnName() . " = $referenceTable.{$entityFieldInfo['entityidfield']}"]);
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
}

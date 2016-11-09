<?php
namespace App\QueryFieldCondition;

/**
 * Reference Query Condition Parser Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class ReferenceCondition extends BaseFieldParser
{

	/**
	 * @var Related modules 
	 */
	protected $relatedModules;
	protected $formattedName;

	public function getTables()
	{
		if ($this->relatedModules) {
			return $this->relatedModules;
		}
		return $this->relatedModules = $this->queryGenerator->getReference($this->fieldModel->getName());
	}

	public function getRelatedTableName()
	{
		if ($this->formattedName) {
			return $this->formattedName;
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
		return $this->formattedName = $formattedName;
	}

	/**
	 * 
	 * @return array
	 */
	public function operatorY()
	{
		return [$this->getColumnName() => $this->value];
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
		return [$this->getRelatedTableName() => $this->value];
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

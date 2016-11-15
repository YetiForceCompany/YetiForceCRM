<?php
namespace App\QueryFieldCondition;

/**
 * Owner Query Condition Parser Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		$entityFieldInfo = \App\Module::getEntityInfo('Users');
		$this->queryGenerator->addJoin(['LEFT JOIN', $entityFieldInfo['tablename'] . ' ' . $referenceTable, $this->getColumnName() . " = $referenceTable.{$entityFieldInfo['entityidfield']}"]);
		$this->queryGenerator->addJoin(['LEFT JOIN', $entityFieldInfo['tablename'] . ' ' . $referenceTable, $this->getColumnName() . " = $referenceTable.{$entityFieldInfo['entityidfield']}"]);
		/*
		  $tableJoinCondition[$fieldName]['vtiger_users' . $fieldName] = $field->getTableName() .
		  '.' . $field->getColumnName() . ' = vtiger_users' . $fieldName . '.id';
		  $tableJoinCondition[$fieldName]['vtiger_groups' . $fieldName] = $field->getTableName() .
		  '.' . $field->getColumnName() . ' = vtiger_groups' . $fieldName . '.groupid';
		  $tableJoinMapping['vtiger_users' . $fieldName] = 'LEFT JOIN vtiger_users AS';
		  $tableJoinMapping['vtiger_groups' . $fieldName] = 'LEFT JOIN vtiger_groups AS';
		 */
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
		if (strpos($this->value, ',') === false) {
			return [$this->getColumnName() => $this->value];
		}
		$values = explode(',', $this->value);
		$condition = ['or'];
		foreach ($values as $value) {
			$condition[] = [$this->getColumnName() => $value];
		}
		return $condition;
	}

	/**
	 * Not equal operator
	 * @return array
	 */
	public function operatorN()
	{
		if (strpos($this->value, ',') === false) {
			return ['<>', $this->getColumnName(), $this->value];
		}
		$values = explode(',', $this->value);
		$condition = ['or'];
		foreach ($values as $value) {
			$condition[] = ['<>', $this->getColumnName(), $value];
		}
		return $condition;
	}

	/**
	 * Currently logged user
	 * @return array
	 */
	public function operatorOm()
	{
		return [$this->getColumnName() => \App\User::getCurrentUserId()];
	}

	/**
	 * Watched record
	 * @return array
	 */
	public function operatorWr()
	{
		$watchdog = \Vtiger_Watchdog_Model::getInstance($this->getModuleName());
		$condition = [];
		if ($watchdog->isActive()) {
			$this->queryGenerator->addJoin(['LEFT JOIN', 'u_#__watchdog_record', 'vtiger_crmentity.crmid = u_#__watchdog_record.record']);
			if ($watchdog->isWatchingModule()) {
				$condition = ['or', ['u_#__watchdog_record.record' => NULL], ['not', ['u_#__watchdog_record.userid' => $watchdog->get('userId'), 'u_#__watchdog_record.state' => 0]]];
			} else {
				$condition = ['u_#__watchdog_record.state' => 1, 'u_#__watchdog_record.userid' => $watchdog->get('userId')];
			}
		}
		return $condition;
	}

	/**
	 * Watched record not
	 * @return array
	 */
	public function operatorNwr()
	{
		$watchdog = \Vtiger_Watchdog_Model::getInstance($this->getModuleName());
		$condition = [];
		if ($watchdog->isActive()) {
			$this->queryGenerator->addJoin(['LEFT JOIN', 'u_#__watchdog_record', 'vtiger_crmentity.crmid = u_#__watchdog_record.record']);
			if ($watchdog->isWatchingModule()) {
				$condition = ['u_#__watchdog_record.userid' => $watchdog->get('userId'), 'u_#__watchdog_record.state' => 0];
			} else {
				$condition = ['or', ['u_#__watchdog_record.record' => NULL], ['not', ['u_#__watchdog_record.userid' => $watchdog->get('userId'), 'u_#__watchdog_record.state' => 1]]];
			}
		}
		return $condition;
	}
}

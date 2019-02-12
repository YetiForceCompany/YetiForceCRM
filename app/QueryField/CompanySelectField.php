<?php

namespace App\QueryField;

/**
 * CompanySelect Query Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class CompanySelectField extends PicklistField
{
	/**
	 * Equals operator.
	 *
	 * @return array
	 */
	public function operatorE()
	{
		if (empty($this->value)) {
			return [];
		}
		$columnName = $this->getColumnName();
		$this->queryGenerator->addJoin(['INNER JOIN', 's_#__companies', "$columnName = s_#__companies.id"]);
		return ['s_#__companies.name' => $this->getValue()];
	}

	/**
	 * Not equal operator.
	 *
	 * @return array
	 */
	public function operatorN()
	{
		if (empty($this->value)) {
			return [];
		}
		$columnName = $this->getColumnName();
		$this->queryGenerator->addJoin(['INNER JOIN', 's_#__companies', "$columnName = s_#__companies.id"]);
		return ['not', ['s_#__companies.name' => $this->getValue()]];
	}

	/**
	 * Contains operator.
	 *
	 * @return array
	 */
	public function operatorC()
	{
		if (empty($this->value)) {
			return [];
		}
		$columnName = $this->getColumnName();
		$this->queryGenerator->addJoin(['INNER JOIN', 's_#__companies', "$columnName = s_#__companies.id"]);
		return ['like', 's_#__companies.name', "%{$this->value}%", false];
	}

	/**
	 * Ends with operator.
	 *
	 * @return array
	 */
	public function operatorEw()
	{
		if (empty($this->value)) {
			return [];
		}
		$columnName = $this->getColumnName();
		$this->queryGenerator->addJoin(['INNER JOIN', 's_#__companies', "$columnName = s_#__companies.id"]);
		return ['like', 's_#__companies.name', '%' . $this->value, false];
	}

	/**
	 * Does not contain operator.
	 *
	 * @return array
	 */
	public function operatorK()
	{
		if (empty($this->value)) {
			return [];
		}
		$columnName = $this->getColumnName();
		$this->queryGenerator->addJoin(['INNER JOIN', 's_#__companies', "$columnName = s_#__companies.id"]);
		return ['not like', 's_#__companies.name', $this->value];
	}
}

<?php

namespace App\QueryField;

/**
 * Shared Owner Query Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class SharedOwnerField extends BaseField
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
		$focus = $this->queryGenerator->getEntityModel();
		$baseTable = $focus->table_name;
		$baseTableIndex = $focus->table_index;
		$this->queryGenerator->addJoin(['INNER JOIN', 'u_#__crmentity_showners', "$baseTable.$baseTableIndex = u_#__crmentity_showners.crmid"]);
		$values = explode('##', $this->value);
		$condition = ['or'];
		foreach ($values as $value) {
			$condition[] = ['u_#__crmentity_showners.userid' => $value];
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
		if (empty($this->value)) {
			return [];
		}
		$focus = $this->queryGenerator->getEntityModel();
		$baseTable = $focus->table_name;
		$baseTableIndex = $focus->table_index;
		$this->queryGenerator->addJoin(['INNER JOIN', 'u_#__crmentity_showners', "$baseTable.$baseTableIndex = u_#__crmentity_showners.crmid"]);
		$values = explode('##', $this->value);
		$condition = ['or'];
		foreach ($values as $value) {
			$condition[] = ['<>', 'u_#__crmentity_showners.userid', $value];
		}

		return $condition;
	}

	/**
	 * Currently logged user.
	 *
	 * @return array
	 */
	public function operatorOm()
	{
		$focus = $this->queryGenerator->getEntityModel();
		$baseTable = $focus->table_name;
		$baseTableIndex = $focus->table_index;
		$this->queryGenerator->addJoin(['INNER JOIN', 'u_#__crmentity_showners', "$baseTable.$baseTableIndex = u_#__crmentity_showners.crmid"]);

		return ['u_#__crmentity_showners.userid' => \App\User::getCurrentUserId()];
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
		$focus = $this->queryGenerator->getEntityModel();
		$baseTable = $focus->table_name;
		$baseTableIndex = $focus->table_index;
		$this->queryGenerator->addJoin(['INNER JOIN', 'u_#__crmentity_showners', "$baseTable.$baseTableIndex = u_#__crmentity_showners.crmid"]);
		$values = explode('##', $this->value);
		$condition = ['or'];
		foreach ($values as $value) {
			$condition[] = ['u_#__crmentity_showners.userid' => $value];
		}

		return $condition;
	}

	/**
	 * Is not empty operator.
	 *
	 * @return array
	 */
	public function operatorNy()
	{
		$focus = $this->queryGenerator->getEntityModel();
		$baseTable = $focus->table_name;
		$baseTableIndex = $focus->table_index;
		$this->queryGenerator->addJoin(['INNER JOIN', 'u_#__crmentity_showners', "$baseTable.$baseTableIndex = u_#__crmentity_showners.crmid"]);
	}

	/**
	 * Is empty operator.
	 *
	 * @return array
	 */
	public function operatorY()
	{
		$focus = $this->queryGenerator->getEntityModel();
		$baseTable = $focus->table_name;
		$baseTableIndex = $focus->table_index;
		$this->queryGenerator->addJoin(['LEFT JOIN', 'u_#__crmentity_showners', "$baseTable.$baseTableIndex = u_#__crmentity_showners.crmid"]);

		return ['u_#__crmentity_showners.userid' => null];
	}
}

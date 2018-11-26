<?php

namespace App\QueryField;

/**
 * Shared Owner Query Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		$query = (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')->where(['userid' => explode('##', $this->value)]);
		return ["{$focus->table_name}.{$focus->table_index}" => $query];
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
		$query = (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')->where(['userid' => explode('##', $this->value)]);
		return ['NOT IN', "{$focus->table_name}.{$focus->table_index}", $query];
	}

	/**
	 * Currently logged user.
	 *
	 * @return array
	 */
	public function operatorOm()
	{
		$focus = $this->queryGenerator->getEntityModel();
		$query = (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')->where(['userid' => \App\User::getCurrentUserId()]);
		return ["{$focus->table_name}.{$focus->table_index}" => $query];
	}

	/**
	 * Currently logged-in user groups.
	 *
	 * @return array
	 */
	public function operatorOgr(): array
	{
		$focus = $this->queryGenerator->getEntityModel();
		$groups = \App\Fields\Owner::getInstance($this->getModuleName())->getGroups(false, 'private');
		$query = (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')->where(['userid' => \array_keys($groups)]);
		return ["{$focus->table_name}.{$focus->table_index}" => $query];
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
		$query = (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')->where(['userid' => explode('##', $this->value)]);
		return ["{$focus->table_name}.{$focus->table_index}" => $query];
	}

	/**
	 * Is not empty operator.
	 *
	 * @return array
	 */
	public function operatorNy()
	{
		$focus = $this->queryGenerator->getEntityModel();
		$query = (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')
			->innerJoin($focus->table_name, "u_#__crmentity_showners.crmid={$focus->table_name}.{$focus->table_index}");
		return ["{$focus->table_name}.{$focus->table_index}" => $query];
	}

	/**
	 * Is empty operator.
	 *
	 * @return array
	 */
	public function operatorY()
	{
		$focus = $this->queryGenerator->getEntityModel();
		$query = (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')
			->innerJoin($focus->table_name, "u_#__crmentity_showners.crmid={$focus->table_name}.{$focus->table_index}");
		return ['NOT IN', "{$focus->table_name}.{$focus->table_index}", $query];
	}
}

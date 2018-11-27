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
	 * {@inheritdoc}
	 */
	public function getColumnName()
	{
		if ($this->fullColumnName) {
			return $this->fullColumnName;
		}
		if ($this->related) {
			$fieldModel = $this->queryGenerator->getModuleField($this->related['sourceField']);
			return $this->fullColumnName = "{$fieldModel->getTableName()}.{$fieldModel->getColumnName()}";
		} else {
			$focus = $this->queryGenerator->getEntityModel();
			return $this->fullColumnName = "{$focus->table_name}.{$focus->table_index}";
		}
	}

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
		return [$this->getColumnName() => (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')->where(['userid' => explode('##', $this->value)])];
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
		return ['NOT IN', $this->getColumnName(), (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')->where(['userid' => explode('##', $this->value)])];
	}

	/**
	 * Currently logged user.
	 *
	 * @return array
	 */
	public function operatorOm()
	{
		$this->value = \App\User::getCurrentUserId();
		return $this->operatorE();
	}

	/**
	 * Currently logged-in user groups.
	 *
	 * @return array
	 */
	public function operatorOgr(): array
	{
		$this->value = implode('##', array_keys(\App\Fields\Owner::getInstance($this->getModuleName())->getGroups(false, 'private')));
		return $this->operatorE();
	}

	/**
	 * Contains operator.
	 *
	 * @return array
	 */
	public function operatorC()
	{
		return $this->operatorE();
	}

	/**
	 * Is not empty operator.
	 *
	 * @return array
	 */
	public function operatorNy()
	{
		if ($this->related) {
			$fieldModel = $this->queryGenerator->getModuleField($this->related['sourceField']);
			$query = (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')
				->innerJoin($fieldModel->getTableName(), "u_#__crmentity_showners.crmid={$fieldModel->getTableName()}.{$fieldModel->getColumnName()}");
		} else {
			$focus = $this->queryGenerator->getEntityModel();
			$query = (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')
				->innerJoin($focus->table_name, "u_#__crmentity_showners.crmid={$focus->table_name}.{$focus->table_index}");
		}
		return [$this->getColumnName() => $query];
	}

	/**
	 * Is empty operator.
	 *
	 * @return array
	 */
	public function operatorY()
	{
		if ($this->related) {
			$fieldModel = $this->queryGenerator->getModuleField($this->related['sourceField']);
			$query = (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')
				->innerJoin($fieldModel->getTableName(), "u_#__crmentity_showners.crmid={$fieldModel->getTableName()}.{$fieldModel->getColumnName()}");
		} else {
			$focus = $this->queryGenerator->getEntityModel();
			$query = (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')
				->innerJoin($focus->table_name, "u_#__crmentity_showners.crmid={$focus->table_name}.{$focus->table_index}");
		}
		return ['NOT IN', $this->getColumnName(), $query];
	}
}

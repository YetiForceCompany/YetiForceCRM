<?php
namespace App\QueryField;

/**
 * Owner Query Field Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class OwnerField extends BaseField
{

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

	/**
	 * Get order by
	 * @return array
	 */
	public function getOrderBy($order = false)
	{
		$this->queryGenerator->addJoin(['LEFT JOIN', 'vtiger_users', 'vtiger_users.id = ' . $this->getColumnName()]);
		$this->queryGenerator->addJoin(['LEFT JOIN', 'vtiger_groups', 'vtiger_groups.groupid = ' . $this->getColumnName()]);
		if ($order && strtoupper($order) === 'DESC') {
			return ['vtiger_users.last_name' => SORT_DESC, 'vtiger_users.first_name' => SORT_DESC, 'vtiger_groups.groupname' => SORT_DESC];
		} else {
			return ['vtiger_users.last_name' => SORT_ASC, 'vtiger_users.first_name' => SORT_ASC, 'vtiger_groups.groupname' => SORT_ASC];
		}
	}

	/**
	 * Is not empty operator
	 * @return array
	 */
	public function operatorNy()
	{
		return ['and',
			['not', [$this->getColumnName() => null]],
			['<>', $this->getColumnName(), 0]
		];
	}

	/**
	 * Is empty operator
	 * @return array
	 */
	public function operatorY()
	{
		return ['or',
			[$this->getColumnName() => null],
			['=', $this->getColumnName(), 0]
		];
	}
}

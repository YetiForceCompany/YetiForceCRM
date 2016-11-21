<?php
namespace App\QueryField;

/**
 * Url Query Field Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class UserRoleField extends StringField
{
	/**
	 * Contains operator
	 * @return array
	 */
	public function operatorC()
	{
		$this->queryGenerator->addJoin(['INNER JOIN', 'vtiger_user2role', 'vtiger_user2role.userid = vtiger_users.id']);
		$this->queryGenerator->addJoin(['INNER JOIN', 'vtiger_role', 'vtiger_role.roleid = ' . $this->getColumnName()]);
		return ['like', 'vtiger_role.rolename', $this->getValue()];
	}
	/**
	 * Get order by
	 * @return array
	 */
	public function getOrderBy($order = false)
	{
		$this->queryGenerator->addJoin(['INNER JOIN', 'vtiger_user2role', 'vtiger_user2role.userid = vtiger_users.id']);
		$this->queryGenerator->addJoin(['INNER JOIN', 'vtiger_role', 'vtiger_role.roleid = ' . $this->getColumnName()]);
		if ($order && strtoupper($order) === 'DESC') {
			return ['vtiger_role.rolename' => SORT_DESC];
		} else {
			return ['vtiger_role.rolename' => SORT_ASC];
		}
	}
}
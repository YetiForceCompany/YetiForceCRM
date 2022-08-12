<?php

/**
 * List View Model file for LoginHistory Settings.
 *
 * @package Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author 	  Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_LoginHistory_ListView_Model extends Settings_Vtiger_ListView_Model
{
	/** {@inheritdoc} */
	public function getBasicListQuery(): App\Db\Query
	{
		$module = $this->getModule();
		$query = (new App\Db\Query())->select(['login_id', 'user_name', 'user_ip', 'agent', 'browser', 'logout_time', 'login_time', 'vtiger_loginhistory.status'])->from($module->baseTable);
		$search_key = $this->get('search_key');
		$value = $this->get('search_value');
		if (!empty($search_key) && !empty($value) && \in_array($search_key, array_keys($module->listFields))) {
			if ('other' === $value) {
				$subQuery = (new \App\Db\Query())->select(['user_name'])->from('vtiger_users');
				$query->where(['not in', "$module->baseTable.$search_key", $subQuery]);
			} else {
				$query->where(["$module->baseTable.$search_key" => $value]);
			}
		}
		$query->orderBy(['login_time' => SORT_DESC]);
		return $query;
	}

	/** {@inheritdoc} */
	public function getListViewLinks()
	{
		return [];
	}

	/** {@inheritdoc} */
	public function getListViewCount()
	{
		$query = $this->getBasicListQuery();
		$query->orderBy([]);
		return $query->count();
	}
}

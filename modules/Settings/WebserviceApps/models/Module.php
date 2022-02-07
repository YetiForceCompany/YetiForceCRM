<?php

/**
 * @package Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_WebserviceApps_Module_Model extends Settings_Vtiger_Module_Model
{
	/**
	 * Get all servers.
	 *
	 * @param bool $onlyActive
	 *
	 * @return array
	 */
	public static function getServers(bool $onlyActive = true): array
	{
		$query = (new \App\Db\Query())->from('w_#__servers');
		if ($onlyActive) {
			$query->where(['status' => 1]);
		}
		return $query->createCommand(\App\Db::getInstance('webservice'))->queryAllByGroup(1);
	}

	/**
	 * Get active servers.
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public static function getActiveServers(string $type = ''): array
	{
		$query = (new \App\Db\Query())->from('w_#__servers')->andWhere(['status' => 1]);
		if (!empty($type)) {
			$query->andWhere(['type' => $type]);
		}
		return $query->createCommand(\App\Db::getInstance('webservice'))->queryAllByGroup(1);
	}
}

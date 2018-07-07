<?php

/**
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WebserviceApps_Module_Model extends Settings_Vtiger_Module_Model
{
	/**
	 * Webservice apps types.
	 *
	 * @return string[]
	 */
	public static function getTypes()
	{
		return ['Portal'];
	}

	public static function getServers()
	{
		return (new \App\Db\Query())->from('w_#__servers')
			->createCommand(\App\Db::getInstance('webservice'))
			->queryAllByGroup(1);
	}

	public static function getActiveServers($type = '')
	{
		$query = (new \App\Db\Query())->from('w_#__servers')->andWhere(['status' => 1]);
		if (!empty($type)) {
			$query->andWhere(['type' => $type]);
		}
		return $query->createCommand(\App\Db::getInstance('webservice'))->queryAllByGroup(1);
	}
}

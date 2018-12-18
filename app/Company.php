<?php

namespace App;

/**
 * Company basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Company extends Base
{
	/**
	 * Function to get the instance of the Company model.
	 *
	 * @param int $id
	 *
	 * @return \self
	 */
	public static function getAll()
	{
		if (Cache::has('CompanyGetAll', '')) {
			return Cache::get('CompanyGetAll', '');
		}
		$rows = (new \App\Db\Query())->from('s_#__companies')->all();
		Cache::save('CompanyGetAll', '', $rows, Cache::LONG);
		return $rows;
	}

	public static function setOfflineSerial($key): bool
	{
		return random_int(0, 1) ? true : false;
	}

	public static function registerOnline(\App\Request $request): bool
	{
		return random_int(0, 1) ? true : false;
	}
}

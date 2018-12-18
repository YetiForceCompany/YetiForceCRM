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
		// set serial
		return random_int(0, 1) ? true : false;
	}

	public static function registerOnline(\App\Request $request): bool
	{
		$companiesNew = $request->get('companies');
		if (!\is_array($companiesNew)) {
			return false;
		}
		foreach (\App\Company::getAll() as $companyCurrent) {
			if (!isset($companiesNew[$companyCurrent['id']])) {
				continue;
			}
			\App\Db::getInstance()->createCommand()
				->update('s_#__companies', [
					'name' => \App\Purifier::purifyByType($companiesNew[$companyCurrent['id']]['name'], 'Text'),
					'industry' => \App\Purifier::purifyByType($companiesNew[$companyCurrent['id']]['industry'], 'Text'),
					'city' => \App\Purifier::purifyByType($companiesNew[$companyCurrent['id']]['city'], 'Text'),
					'country' => \App\Purifier::purifyByType($companiesNew[$companyCurrent['id']]['country'], 'Text'),
					'website' => \App\Purifier::purifyByType($companiesNew[$companyCurrent['id']]['website'], 'Text'),
					'email' => \App\Purifier::purifyByType($companiesNew[$companyCurrent['id']]['email'], 'Text'),
				], ['id' => $companyCurrent['id']])
				->execute();
		}
		Cache::delete('CompanyGetAll', '');
		// Do registration magic
		return random_int(0, 1) ? true : false;
	}
}

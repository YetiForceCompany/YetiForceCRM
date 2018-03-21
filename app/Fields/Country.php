<?php
/**
 * Tools for country class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Fields;

/**
 * Country class.
 */
class Country
{
	/**
	 * Get all country.
	 *
	 * @param bool|string $type
	 *
	 * @return array
	 */
	public static function getAll($type = false)
	{
		if (\App\Cache::has('Country|getAll', $type)) {
			return \App\Cache::get('Country|getAll', $type);
		}
		$select = ['code', 'id', 'name'];
		if ($type && $type === 'uitype') {
			$select = ['name', 'code', 'id'];
		}
		$query = new \App\Db\Query();
		$query->select($select)->from('u_#__countries')->where(['status' => 0])->orderBy('sortorderid');
		if ($type) {
			$query->andWhere([$type => 0]);
		}
		$rows = $query->createCommand()->queryAllByGroup(1);
		\App\Cache::save('Country|getAll', $type, $rows);

		return $rows;
	}
}

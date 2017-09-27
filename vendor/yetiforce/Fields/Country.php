<?php
/**
 * Tools for country class
 * @package YetiForce.App
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
namespace App\Fields;

/**
 * Country class
 */
class Country
{

	/**
	 * Get all country
	 * @return array
	 */
	public static function getAll()
	{
		if (\App\Cache::has('Country|getAll', '')) {
			return \App\Cache::get('Country|getAll', '');
		}
		$query = new \App\Db\Query();
		$query->select(['code', 'id', 'name'])->from('u_#__countries')->where(['status' => 0])->orderBy('sortorderid');
		$rows = $query->createCommand()->queryAllByGroup(1);
		\App\Cache::save('Country|getAll', '', $rows);
		return $rows;
	}
}

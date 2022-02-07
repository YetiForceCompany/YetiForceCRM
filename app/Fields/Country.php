<?php
/**
 * Tools for country class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
		if ($type && 'uitype' === $type) {
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

	/**
	 * Get country code by name.
	 *
	 * @param string $countryName
	 *
	 * @return string|null Return null if not found.
	 */
	public static function getCountryCode(string $countryName): ?string
	{
		return static::getAll('uitype')[$countryName]['code'] ?? null;
	}

	/**
	 * Get country name by code.
	 *
	 * @param string $countryCode
	 *
	 * @return string|null Return null if not found.
	 */
	public static function getCountryName(string $countryCode): ?string
	{
		return static::getAll()[$countryCode]['name'] ?? null;
	}

	/**
	 * Return correct key value of given country in user language.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function findCountryName(string $value): string
	{
		if (empty($value)) {
			return '';
		}
		if (($userLanguage = \App\Language::getLanguage()) !== ($defaultLanguage = \App\Config::main('default_language'))) {
			$secondLanguage = array_map('strtolower', \App\Language::getFromFile('Other/Country', $defaultLanguage)['php']);
		}
		$firstLanguage = array_map('strtolower', \App\Language::getFromFile('Other/Country', $userLanguage)['php']);
		$countryName = ucwords(trim($value));
		$formattedCountryName = strtolower($countryName);
		if (empty($firstLanguage[$countryName])) {
			if (\in_array($formattedCountryName, $firstLanguage)) {
				$countryName = \array_search($formattedCountryName, $firstLanguage);
			} elseif (!empty($secondLanguage) && \in_array($formattedCountryName, $secondLanguage)) {
				$countryName = \array_search($formattedCountryName, $secondLanguage);
			}
		}
		return $countryName;
	}
}

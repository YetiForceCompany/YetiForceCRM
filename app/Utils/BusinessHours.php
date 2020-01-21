<?php
/**
 * Business hours.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Utils;

/**
 * BusinessHours utils class.
 */
class BusinessHours
{
	/**
	 * Default business hours ID.
	 */
	public const DEFAULT_BUSINESS_HOURS_ID = 0;

	/**
	 * Get default business hours.
	 *
	 * @return array
	 */
	public static function getBusinessHours(): array
	{
		$cacheName = 'Date::getBusinessHours';
		if (!\App\Cache::has($cacheName, '')) {
			$businessHours = [];
			$dataReader = (new \App\Db\Query())->from('s_#__business_hours')->createCommand(\App\Db::getInstance('admin'))->query();
			while ($row = $dataReader->read()) {
				$businessHours[$row['id']] = $row;
				if ($row['default']) {
					$businessHours[self::DEFAULT_BUSINESS_HOURS_ID] = $row;
				}
			}
			\App\Cache::save($cacheName, '', $businessHours);
		}
		return \App\Cache::get($cacheName, '');
	}

	/**
	 * Get business hours by id.
	 *
	 * @param int $id
	 *
	 * @return array|null
	 */
	public static function getBusinessHoursById(int $id = self::DEFAULT_BUSINESS_HOURS_ID): ?array
	{
		return self::getBusinessHours()[$id] ?? null;
	}
}

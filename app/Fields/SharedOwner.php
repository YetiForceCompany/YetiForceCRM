<?php
/**
 * Shared owner class.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Fields;

use App\Cache;

/**
 * Class SharedOwner.
 */
class SharedOwner
{
	/**
	 * Get shared owner list by crmid.
	 *
	 * @param int|int[] $crmId
	 *
	 * @return int[]|array
	 */
	public static function getById($crmId)
	{
		$values = [];
		if (\is_array($crmId)) {
			foreach ($crmId as $id) {
				if (Cache::has('SharedOwnerFieldValue', $id)) {
					$values[$id] = Cache::get('SharedOwnerFieldValue', $id);
					unset($crmId[$id]);
				}
			}
		} elseif (Cache::has('SharedOwnerFieldValue', $crmId)) {
			return Cache::get('SharedOwnerFieldValue', $crmId);
		}
		if ($crmId) {
			$query = (new \App\Db\Query())->from('u_#__crmentity_showners')->where(['crmid' => $crmId]);
			if (\is_array($crmId)) {
				$rows = $query->select(['crmid', 'userid'])->createCommand()->queryAllByGroup(2);
				foreach ($rows as $id => $value) {
					$values[$id] = $value;
					Cache::save('SharedOwnerFieldValue', $id, $value, Cache::LONG);
				}
			} else {
				$values = $query->select(['userid'])->distinct()->column();
				if (empty($values)) {
					$values = [];
				}
				Cache::save('SharedOwnerFieldValue', $crmId, $values, Cache::LONG);
			}
		}
		return $values;
	}
}

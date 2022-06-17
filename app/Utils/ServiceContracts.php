<?php
/**
 * Service contracts utils file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Utils;

/**
 * Service contracts utils class.
 */
class ServiceContracts
{
	/**
	 * Fields map.
	 *
	 * @var string[]
	 */
	private static $fieldsMap = [
		'reaction_time' => 'response',
		'resolve_time' => 'solution',
		'idle_time' => 'idle',
	];

	/**
	 * Get the amount of business time between two dates in minutes.
	 *
	 * @param string $start
	 * @param string $end
	 * @param array  $days
	 * @param string $startHour
	 * @param string $endHour
	 * @param bool   $holidays
	 *
	 * @return int
	 */
	public static function businessTime(string $start, string $end, string $startHour, string $endHour, array $days, bool $holidays): int
	{
		$start = new \DateTime($start);
		$end = new \DateTime($end);
		$holidaysDates = $dates = [];
		$date = clone $start;
		$days = array_flip($days);
		if ($holidays) {
			$holidaysDates = array_flip(array_keys(\App\Fields\Date::getHolidays($start->format('Y-m-d'), $end->format('Y-m-d'))));
		}
		while ($date <= $end) {
			$datesEnd = (clone $date)->setTime(23, 59, 59);
			if (isset($days[$date->format('N')]) && (!$holidays || ($holidays && !isset($holidaysDates[$date->format('Y-m-d')])))) {
				$dates[] = [
					'start' => clone $date,
					'end' => clone ($end < $datesEnd ? $end : $datesEnd),
				];
			}
			$date->modify('+1 day')->setTime(0, 0, 0);
		}
		[$sh,$sm,$ss] = explode(':', $startHour);
		[$eh,$em,$es] = explode(':', $endHour);
		return array_reduce($dates, function ($carry, $item) use ($sh, $sm, $ss, $eh, $em, $es) {
			$businessStart = (clone $item['start'])->setTime($sh, $sm, $ss);
			$businessEnd = (clone $item['end'])->setTime($eh, $em, $es);
			$start = ($item['start'] < $businessStart) ? $businessStart : $item['start'];
			$end = ($item['end'] > $businessEnd) ? $businessEnd : $item['end'];
			return $carry += max(0, $end->getTimestamp() - $start->getTimestamp());
		}, 0) / 60;
	}

	/**
	 * Get default business hours.
	 *
	 * @return array
	 */
	public static function getDefaultBusinessHours(): array
	{
		if (\App\Cache::has('UtilsServiceContracts::getDefaultBusinessHours', '')) {
			return \App\Cache::get('UtilsServiceContracts::getDefaultBusinessHours', '');
		}
		$rows = (new \App\Db\Query())->from('s_#__business_hours')->where(['default' => 1])->all(\App\Db::getInstance('admin'));
		\App\Cache::save('UtilsServiceContracts::getDefaultBusinessHours', '', $rows);
		return $rows;
	}

	/**
	 * Get all business hours.
	 *
	 * @return array
	 */
	public static function getAllBusinessHours(): array
	{
		if (\App\Cache::has('UtilsServiceContracts::getAllBusinessHours', '')) {
			return \App\Cache::get('UtilsServiceContracts::getAllBusinessHours', '');
		}
		$rows = (new \App\Db\Query())->from('s_#__business_hours')->all(\App\Db::getInstance('admin'));
		\App\Cache::save('UtilsServiceContracts::getAllBusinessHours', '', $rows);
		return $rows;
	}

	/**
	 * Get business hours by ids .
	 *
	 * @param string $ids ex. '1,2'
	 *
	 * @return array
	 */
	public static function getBusinessHoursByIds(array $ids): array
	{
		$cacheKey = implode(',', $ids);
		if (\App\Cache::has('UtilsServiceContracts::getBusinessHoursById', $cacheKey)) {
			return \App\Cache::get('UtilsServiceContracts::getBusinessHoursById', $cacheKey);
		}
		$rows = (new \App\Db\Query())->from('s_#__business_hours')->where(['id' => $ids])->all(\App\Db::getInstance('admin'));
		\App\Cache::save('UtilsServiceContracts::getBusinessHoursById', $cacheKey, $rows);
		return $rows;
	}

	/**
	 * Get sla policy by id.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	private static function getSlaPolicyById(int $id): array
	{
		if (\App\Cache::has('UtilsServiceContracts::getSlaPolicyById', $id)) {
			return \App\Cache::get('UtilsServiceContracts::getSlaPolicyById', $id);
		}
		$row = (new \App\Db\Query())->from('s_#__sla_policy')->where(['id' => $id])->one(\App\Db::getInstance('admin'));
		\App\Cache::save('UtilsServiceContracts::getSlaPolicyById', $id, $row);
		return $row;
	}

	/**
	 * Get sla policy by module id.
	 *
	 * @param int $moduleId
	 *
	 * @return array
	 */
	public static function getSlaPolicyByModule(int $moduleId): array
	{
		if (\App\Cache::has('UtilsServiceContracts::getSlaPolicyByModule', $moduleId)) {
			return \App\Cache::get('UtilsServiceContracts::getSlaPolicyByModule', $moduleId);
		}
		$rows = (new \App\Db\Query())->from('s_#__sla_policy')->where(['tabid' => $moduleId])->all(\App\Db::getInstance('admin'));
		\App\Cache::save('UtilsServiceContracts::getSlaPolicyByModule', $moduleId, $rows);
		return $rows;
	}

	/**
	 * Get sla policy from service contracts by crm id.
	 *
	 * @param int      $serviceContractId Service contracts id
	 * @param int|null $sourceModuleId
	 *
	 * @return array
	 */
	public static function getSlaPolicyForServiceContracts(int $serviceContractId, ?int $sourceModuleId = null): array
	{
		if (\App\Cache::has('UtilsServiceContracts::getSlaPolicyForServiceContracts', $serviceContractId)) {
			$rows = \App\Cache::get('UtilsServiceContracts::getSlaPolicyForServiceContracts', $serviceContractId);
		} else {
			$rows = (new \App\Db\Query())->from('u_#__servicecontracts_sla_policy')->where(['crmid' => $serviceContractId])->all();
			\App\Cache::save('UtilsServiceContracts::getSlaPolicyForServiceContracts', $serviceContractId, $rows);
		}
		if ($sourceModuleId) {
			foreach ($rows as $key => $value) {
				if ($sourceModuleId !== $value['tabid']) {
					unset($rows[$key]);
				}
			}
		}
		return $rows;
	}

	/**
	 * Delete sla policy for service contracts.
	 *
	 * @param int      $crmId
	 * @param int      $sourceModuleId
	 * @param int|null $rowId
	 *
	 * @return void
	 */
	public static function deleteSlaPolicy(int $crmId, int $sourceModuleId, ?int $rowId = null)
	{
		$where = ['crmid' => $crmId, 'tabid' => $sourceModuleId];
		if ($rowId) {
			$where['id'] = $rowId;
		}
		\App\Db::getInstance()->createCommand()
			->delete('u_#__servicecontracts_sla_policy', $where)->execute();
		\App\Cache::delete('UtilsServiceContracts::getSlaPolicyForServiceContracts', $crmId);
	}

	/**
	 * Save sla policy for service contracts.
	 *
	 * @param array $data
	 * @param bool  $delete
	 *
	 * @return void
	 */
	public static function saveSlaPolicy(array $data, bool $delete = true)
	{
		$db = \App\Db::getInstance();
		if ($delete) {
			self::deleteSlaPolicy($data['crmid'], $data['tabid']);
		}
		if ($data['policy_type']) {
			$db->createCommand()->insert('u_#__servicecontracts_sla_policy', $data)->execute();
			return $db->getLastInsertID();
		}
		return 0;
	}

	/**
	 * Get rules for service contracts.
	 *
	 * @param int                  $serviceContractId Service contracts id
	 * @param \Vtiger_Record_Model $recordModel       Record the model that will be updated
	 *
	 * @return array
	 */
	public static function getRulesForServiceContracts(int $serviceContractId, \Vtiger_Record_Model $recordModel): array
	{
		$times = $businessHours = [];
		foreach (self::getSlaPolicyForServiceContracts($serviceContractId, $recordModel->getModule()->getId()) as $row) {
			switch ($row['policy_type']) {
				case 1:
					$slaPolicy = self::getSlaPolicyById($row['sla_policy_id']);
					$conditions = \App\Json::decode($slaPolicy['conditions']);
					if ($conditions && \App\Condition::checkConditions($conditions, $recordModel)) {
						if (empty($slaPolicy['operational_hours'])) {
							return $slaPolicy;
						}
						if ($slaPolicy['business_hours']) {
							return self::optimizeBusinessHours(explode(',', $slaPolicy['business_hours']));
						}
					}
					break;
				case 2:
					$conditions = \App\Json::decode($row['conditions']);
					if ($conditions && $row['business_hours'] && \App\Condition::checkConditions($conditions, $recordModel)) {
						$businessHours = \array_merge($businessHours, explode(',', $row['business_hours']));
						if ((isset($times['reaction_time']) && \App\Fields\TimePeriod::convertToMinutes($row['reaction_time']) < \App\Fields\TimePeriod::convertToMinutes($times['reaction_time']))
						|| !isset($times['reaction_time'])) {
							$times = [
								'reaction_time' => $row['reaction_time'],
								'idle_time' => $row['idle_time'],
								'resolve_time' => $row['resolve_time'],
							];
						}
					}
					break;
			}
		}
		if ($businessHours) {
			$result = [];
			foreach (self::optimizeBusinessHours(\array_unique($businessHours)) as $value) {
				$result[] = array_merge($value, $times);
			}
			return $result;
		}
		return [];
	}

	/**
	 * Get rules for record model which will be updated.
	 *
	 * @param \Vtiger_Record_Model $recordModel Record the model that will be updated
	 *
	 * @return array
	 */
	public static function getSlaPolicyRulesForModule(\Vtiger_Record_Model $recordModel): array
	{
		$times = $businessHours = [];
		foreach (self::getSlaPolicyForModule($recordModel->getModule()->getId()) as $row) {
			$conditions = \App\Json::decode($row['conditions']);
			if ($conditions && $row['business_hours'] && \App\Condition::checkConditions($conditions, $recordModel)) {
				$businessHours = \array_merge($businessHours, explode(',', $row['business_hours']));
				if ((isset($times['reaction_time']) && \App\Fields\TimePeriod::convertToMinutes($row['reaction_time']) < \App\Fields\TimePeriod::convertToMinutes($times['reaction_time']))
						|| !isset($times['reaction_time'])) {
					$times = [
						'reaction_time' => $row['reaction_time'],
						'idle_time' => $row['idle_time'],
						'resolve_time' => $row['resolve_time'],
					];
				}
				break;
			}
		}
		if ($businessHours) {
			$result = [];
			foreach (self::optimizeBusinessHours(\array_unique($businessHours)) as $value) {
				$result[] = array_merge($value, $times);
			}
			return $result;
		}
		return [];
	}

	/**
	 * Get sla policy by crm id.
	 *
	 * @param int $moduleId
	 *
	 * @return array
	 */
	public static function getSlaPolicyForModule(int $moduleId): array
	{
		if (\App\Cache::has('UtilsServiceContracts::getSlaPolicyForModule', $moduleId)) {
			$rows = \App\Cache::get('UtilsServiceContracts::getSlaPolicyForModule', $moduleId);
		} else {
			$rows = (new \App\Db\Query())->from('s_#__sla_policy')->where(['tabid' => $moduleId, 'available_for_record_time_count' => 1])->all(\App\Db::getInstance('admin'));
			\App\Cache::save('UtilsServiceContracts::getSlaPolicyForModule', $moduleId, $rows);
		}
		return $rows;
	}

	/**
	 * Parse business hours to days.
	 *
	 * @param array $rows
	 *
	 * @return array
	 */
	private static function parseBusinessHoursToDays(array $rows): array
	{
		$days = $holidays = [];
		foreach ($rows as $row) {
			foreach (explode(',', $row['working_days']) as $day) {
				if ((isset($days[$day]['working_hours_from']) && (int) $row['working_hours_from'] < (int) $days[$day]['working_hours_from'])
					|| empty($days[$day]['working_hours_from'])) {
					$days[$day] = [
						'working_hours_from' => $row['working_hours_from'],
						'working_hours_to' => $row['working_hours_to'],
						'reaction_time' => $row['reaction_time'],
						'idle_time' => $row['idle_time'],
						'resolve_time' => $row['resolve_time'],
					];
				}
			}
			if (!empty($row['holidays']) && ((isset($holidays['working_hours_from']) && (int) $row['working_hours_from'] < (int) $holidays['working_hours_from'])
				|| empty($holidays['working_hours_from']))) {
				$holidays = [
					'working_hours_from' => $row['working_hours_from'],
					'working_hours_to' => $row['working_hours_to'],
					'reaction_time' => $row['reaction_time'],
					'idle_time' => $row['idle_time'],
					'resolve_time' => $row['resolve_time'],
				];
			}
		}
		return ['days' => $days, 'holidays' => $holidays];
	}

	/**
	 * Undocumented function.
	 *
	 * @param array $businessHours
	 *
	 * @return array
	 */
	private static function optimizeBusinessHours(array $businessHours): array
	{
		$result = [];
		['days' => $days, 'holidays' => $holidays] = self::parseBusinessHoursToDays(self::getBusinessHoursByIds($businessHours));
		foreach ($days as $day => $value) {
			$key = "{$value['working_hours_from']}|{$value['working_hours_to']}";
			if (isset($result[$key])) {
				$result[$key] = ['working_days' => $result[$key]['working_days'] . ',' . $day] + $value;
			} else {
				$result[$key] = ['working_days' => $day] + $value;
			}
		}
		if ($holidays) {
			$key = "{$holidays['working_hours_from']}|{$holidays['working_hours_to']}";
			$result[$key] = $result[$key] + ['holidays' => 1];
		}
		return $result;
	}

	/**
	 * Function returning difference in format between date times.
	 *
	 * @param string               $start
	 * @param string               $end
	 * @param \Vtiger_Record_Model $recordModel Record the model that will be updated
	 *
	 * @return int
	 */
	public static function getDiff(string $start, \Vtiger_Record_Model $recordModel, string $end = ''): int
	{
		if (!$end) {
			$end = date('Y-m-d H:i:s');
		}
		$fieldModel = current($recordModel->getModule()->getReferenceFieldsForModule('ServiceContracts'));
		if ($fieldModel && ($value = $recordModel->get($fieldModel->getName()))) {
			return self::getDiffFromServiceContracts($start, $end, $value, $recordModel);
		}
		if (\is_int($diff = self::getDiffFromSlaPolicy($start, $end, $recordModel))) {
			return $diff;
		}
		if (!($diff = self::getDiffFromDefaultBusinessHours($start, $end))) {
			$diff = \App\Fields\DateTime::getDiff($start, $end, 'minutes');
		}
		return $diff;
	}

	/**
	 * Get the amount of business time between the two dates in minutes based on the service contracts.
	 *
	 * @param string               $start
	 * @param string               $end
	 * @param int                  $serviceContractId Service contracts id
	 * @param \Vtiger_Record_Model $recordModel       Record the model that will be updated
	 *
	 * @return int
	 */
	public static function getDiffFromServiceContracts(string $start, string $end, int $serviceContractId, \Vtiger_Record_Model $recordModel): int
	{
		if ($rules = self::getRulesForServiceContracts($serviceContractId, $recordModel)) {
			if (isset($rules['id'])) {
				return round(\App\Fields\DateTime::getDiff($start, $end, 'minutes'));
			}
			$time = 0;
			foreach ($rules as $row) {
				$time += self::businessTime($start, $end, $row['working_hours_from'], $row['working_hours_to'], explode(',', $row['working_days']), !empty($row['holidays']));
			}
			return $time;
		}
		if (!($diff = self::getDiffFromDefaultBusinessHours($start, $end))) {
			$diff = round(\App\Fields\DateTime::getDiff($start, $end, 'minutes'));
		}
		return $diff;
	}

	/**
	 * Get the amount of business time between the two dates in minutes based on the global sla policy.
	 *
	 * @param string               $start
	 * @param string               $end
	 * @param \Vtiger_Record_Model $recordModel Record the model that will be updated
	 *
	 * @return int|null
	 */
	public static function getDiffFromSlaPolicy(string $start, string $end, \Vtiger_Record_Model $recordModel): ?int
	{
		if ($rules = self::getSlaPolicyRulesForModule($recordModel)) {
			$time = 0;
			foreach ($rules as $row) {
				$time += self::businessTime($start, $end, $row['working_hours_from'], $row['working_hours_to'], explode(',', $row['working_days']), !empty($row['holidays']));
			}
			return $time;
		}
		return null;
	}

	/**
	 * Get the amount of default business time between two dates in minutes.
	 *
	 * @param string $start
	 * @param string $end
	 *
	 * @return int
	 */
	public static function getDiffFromDefaultBusinessHours(string $start, string $end): int
	{
		$businessHours = self::getDefaultBusinessHours();
		if (!$businessHours) {
			return false;
		}
		$time = 0;
		foreach ($businessHours as $row) {
			if ($row['working_days']) {
				$time += self::businessTime($start, $end, $row['working_hours_from'], $row['working_hours_to'], explode(',', $row['working_days']), (bool) $row['holidays']);
			}
		}
		return $time;
	}

	/**
	 * Update expected times.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 * @param array                $type
	 *
	 * @return void
	 */
	public static function updateExpectedTimes(\Vtiger_Record_Model $recordModel, array $type)
	{
		$field = \App\Field::getRelatedFieldForModule($recordModel->getModuleName(), 'ServiceContracts');
		$serviceContract = ($field && !empty($recordModel->get($field['fieldname']))) ? $recordModel->get($field['fieldname']) : 0;
		foreach (self::getExpectedTimes($serviceContract, $recordModel, $type) as $key => $time) {
			$recordModel->set($key . '_expected', $time);
		}
	}

	/**
	 * Get expected times from ServiceContracts.
	 *
	 * @param int                  $id          Service contract id
	 * @param \Vtiger_Record_Model $recordModel
	 * @param array                $type
	 *
	 * @return array
	 */
	private static function getExpectedTimes(int $id, \Vtiger_Record_Model $recordModel, array $type): array
	{
		$return = [];
		$date = new \DateTime();
		if ($id && ($rules = self::getRulesForServiceContracts($id, $recordModel)) || ($rules = self::getSlaPolicyRulesForModule($recordModel))) {
			if (isset($rules['id'])) {
				foreach (self::$fieldsMap as $key => $fieldKey) {
					if (\in_array($fieldKey, $type)) {
						$minutes = \App\Fields\TimePeriod::convertToMinutes($rules[$key]);
						$return[$fieldKey] = (clone $date)->modify("+$minutes minute")->format('Y-m-d H:i:s');
					}
				}
				return $return;
			}
			$days = self::parseBusinessHoursToDays($rules);
		} elseif ($businessHours = self::getDefaultBusinessHours()) {
			$days = self::parseBusinessHoursToDays($businessHours);
		} else {
			return [];
		}
		$day = $date->format('N');
		$daySetting = null;
		if (\App\Fields\Date::getHolidays($date->format('Y-m-d'), $date->format('Y-m-d'))) {
			$daySetting = $days['holidays'];
		} elseif (empty($days['days'][$day])) {
			$daySetting = self::getNextBusinessDay($date, $days);
		} else {
			$daySetting = $days['days'][$day];
		}
		if ($daySetting) {
			$interval = \App\Fields\DateTime::getDiff($date->format('Y-m-d') . ' ' . $daySetting['working_hours_to'], $date->format('Y-m-d H:i:s'), 'minutes');
			foreach (self::$fieldsMap as $key => $fieldKey) {
				if (\in_array($fieldKey, $type)) {
					$minutes = \App\Fields\TimePeriod::convertToMinutes($daySetting[$key]);
					if ($minutes < $interval) {
						$return[$fieldKey] = (clone $date)->modify("+$minutes minute")->format('Y-m-d H:i:s');
					} else {
						$tmpDate = clone $date;
						$tmpInterval = $interval;
						while ($minutes > $tmpInterval) {
							$minutes -= $tmpInterval;
							$tmpDaySetting = self::getNextBusinessDay($tmpDate, $days);
							$tmpInterval = \App\Fields\DateTime::getDiff($tmpDate->format('Y-m-d') . ' ' . $tmpDaySetting['working_hours_to'], $tmpDate->format('Y-m-d H:i:s'), 'minutes');
							if ($minutes < $tmpInterval) {
								$return[$fieldKey] = (clone $tmpDate)->modify("+$minutes minute")->format('Y-m-d H:i:s');
							}
						}
					}
				}
			}
			return $return;
		}
		return [];
	}

	/**
	 * Get next business day.
	 *
	 * @param \DateTime $date
	 * @param array     $days
	 *
	 * @return array|null
	 */
	private static function getNextBusinessDay(\DateTime &$date, array $days): ?array
	{
		$tempDay = (int) $date->format('N') + 1;
		$counter = 1;
		$result = null;
		while ($counter < 14) {
			$date->modify('+1 day');
			if (\App\Fields\Date::getHolidays($date->format('Y-m-d'), $date->format('Y-m-d'))) {
				$result = $days['holidays'];
				break;
			}
			if (isset($days['days'][$tempDay])) {
				$result = $days['days'][$tempDay];
				break;
			}
			++$tempDay;
			if (8 === $tempDay) {
				$tempDay = 1;
			}
			++$counter;
		}
		if ($result) {
			\call_user_func_array([$date, 'setTime'], explode(':', $result['working_hours_from']));
		}
		return $result;
	}

	/**
	 * Get modules name related to ServiceContracts.
	 *
	 * @return string[]
	 */
	public static function getModules(): array
	{
		$modules = [];
		foreach (\App\Field::getRelatedFieldForModule(false, 'ServiceContracts') as $moduleName => $value) {
			if (\App\RecordStatus::getFieldName($moduleName)) {
				$modules[] = $moduleName;
			}
		}
		return $modules;
	}
}

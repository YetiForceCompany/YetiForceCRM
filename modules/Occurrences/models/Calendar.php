<?php

/**
 * Occurrences calendar model class.
 *
 * @package Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Occurrences_Calendar_Model extends Vtiger_Calendar_Model
{
	/**
	 * Get query.
	 *
	 * @return \App\Db\Query
	 */
	public function getQuery()
	{
		$queryGenerator = new App\QueryGenerator($this->getModuleName());
		if ($this->has('customFilter')) {
			$queryGenerator->initForCustomViewById($this->get('customFilter'));
		}
		$queryGenerator->setFields(['id', 'date_start', 'date_end', 'occurrences_type', 'occurrences_status', 'topic', 'assigned_user_id']);
		if (!$this->isEmpty('types')) {
			$queryGenerator->addNativeCondition(['u_#__occurrences.occurrences_type' => $this->get('types')]);
		}
		$components = ['PLL_ARCHIVED', 'PLL_CANCELLED'];
		if ('current' == $this->get('time')) {
			$queryGenerator->addCondition('occurrences_status', implode('##', $components), 'n');
		} elseif ('history' == $this->get('time')) {
			$queryGenerator->addCondition('occurrences_status', implode('##', $components), 'e');
		}
		if ($this->get('start') && $this->get('end')) {
			$dbStartDateOject = DateTimeField::convertToDBTimeZone($this->get('start'));
			$dbStartDateTime = $dbStartDateOject->format('Y-m-d H:i:s');
			$dbStartDate = $dbStartDateOject->format('Y-m-d');
			$dbEndDateObject = DateTimeField::convertToDBTimeZone($this->get('end'));
			$dbEndDateTime = $dbEndDateObject->format('Y-m-d H:i:s');
			$dbEndDate = $dbEndDateObject->format('Y-m-d');
			$queryGenerator->addNativeCondition([
				'or',
				[
					'and',
					['>=', 'u_yf_occurrences.date_start', $dbStartDateTime],
					['<=', 'u_yf_occurrences.date_start', $dbEndDateTime],
				],
				[
					'and',
					['>=', 'u_yf_occurrences.date_end', $dbStartDateTime],
					['<=', 'u_yf_occurrences.date_end', $dbEndDateTime],
				],
				[
					'and',
					['<', 'u_yf_occurrences.date_start', $dbStartDate],
					['>', 'u_yf_occurrences.date_end', $dbEndDate],
				]
			]);
		}

		$query = $queryGenerator->createQuery();
		if ($this->has('filters')) {
			foreach ($this->get('filters') as $filter) {
				$filterClassName = Vtiger_Loader::getComponentClassName('CalendarFilter', $filter['name'], $this->getModuleName());
				$filterInstance = new $filterClassName();
				if ($filterInstance->checkPermissions() && $conditions = $filterInstance->getCondition($filter['value'])) {
					$query->andWhere($conditions);
				}
			}
		}
		$conditions = [];
		if (!empty($this->get('user'))) {
			$conditions[] = ['vtiger_crmentity.smownerid' => $this->get('user')];
			$subQuery = (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')->where(['userid' => $this->get('user')]);
			$conditions[] = ['vtiger_crmentity.crmid' => $subQuery];
		}
		if ($conditions) {
			$query->andWhere(array_merge(['or'], $conditions));
		}
		$query->orderBy(['u_yf_occurrences.date_start' => SORT_ASC]);

		return $query;
	}

	/**
	 * Function to get records.
	 *
	 * @return array
	 */
	public function getEntity()
	{
		$dataReader = $this->getQuery()->createCommand()->query();
		$result = [];
		$moduleModel = $this->getModule();
		$isSummaryViewSupported = $moduleModel->isSummaryViewSupported();
		while ($record = $dataReader->read()) {
			$item = [];
			$item['id'] = $record['id'];
			$item['title'] = \App\Purifier::encodeHtml($record['topic']);

			$dateTimeInstance = new DateTimeField($record['date_start']);
			$item['start'] = DateTimeField::convertToUserTimeZone($record['date_start'])->format('Y-m-d') . ' ' . $dateTimeInstance->getFullcalenderTime();
			$item['start_display'] = $dateTimeInstance->getDisplayDateTimeValue();

			$dateTimeInstance = new DateTimeField($record['date_end']);
			$item['end'] = DateTimeField::convertToUserTimeZone($record['date_end'])->format('Y-m-d') . ' ' . $dateTimeInstance->getFullcalenderTime();
			$item['end_display'] = $dateTimeInstance->getDisplayDateTimeValue();

			$item['className'] = 'js-popover-tooltip--record ownerCBg_' . $record['assigned_user_id'] . ' picklistCBr_Occurrences_occurrences_type_' . $record['occurrences_type'];
			if ($isSummaryViewSupported) {
				$item['url'] = 'index.php?module=' . $this->getModuleName() . '&view=QuickDetailModal&record=' . $record['id'];
				$item['className'] .= ' js-show-modal';
			} else {
				$item['url'] = $moduleModel->getDetailViewUrl($record['id']);
			}
			$result[] = $item;
		}
		$dataReader->close();
		return $result;
	}

	/**
	 * Function to get type of calendars.
	 *
	 * @return string[]
	 */
	public function getCalendarTypes()
	{
		return $this->getModule()->getFieldByName('occurrences_type')->getPicklistValues();
	}

	/**
	 * Get entity count for year view.
	 *
	 * @return array
	 */
	public function getEntityYearCount()
	{
		$currentUser = \App\User::getCurrentUserModel();
		$startDate = DateTimeField::convertToDBTimeZone($this->get('start'));
		$startDate = strtotime($startDate->format('Y-m-d H:i:s'));
		$endDate = DateTimeField::convertToDBTimeZone($this->get('end'));
		$endDate = strtotime($endDate->format('Y-m-d H:i:s'));
		$dataReader = $this->getQuery()->createCommand()->query();
		$return = [];
		while ($record = $dataReader->read()) {
			$dateFormat = \App\Fields\DateTime::formatToDisplay($record['date_start']);
			$dateTimeComponents = explode(' ', $dateFormat);
			$startDateFormated = \App\Fields\Date::sanitizeDbFormat($dateTimeComponents[0], $currentUser->getDetail('date_format'));

			$dateFormat = \App\Fields\DateTime::formatToDisplay($record['date_start']);
			$dateTimeComponents = explode(' ', $dateFormat);
			$endDateFormated = \App\Fields\Date::sanitizeDbFormat($dateTimeComponents[0], $currentUser->getDetail('date_format'));

			$begin = new DateTime($startDateFormated);
			$end = new DateTime($endDateFormated);
			$end->modify('+1 day');
			$interval = DateInterval::createFromDateString('1 day');
			foreach (new DatePeriod($begin, $interval, $end) as $dt) {
				$date = strtotime($dt->format('Y-m-d'));
				if ($date >= $startDate && $date <= $endDate) {
					$date = date('Y-m-d', $date);
					$return[$date]['date'] = $date;
					if (isset($return[$date]['count'])) {
						++$return[$date]['count'];
					} else {
						$return[$date]['count'] = 1;
					}
				}
			}
		}
		$dataReader->close();
		return array_values($return);
	}

	/**
	 * {@inheritdoc}
	 */
	public function updateEvent(int $recordId, string $data, array $delta)
	{
		$start = DateTimeField::convertToDBTimeZone($data, \App\User::getCurrentUserModel(), false);
		$dateStart = $start->format('Y-m-d H:i:s');
		try {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $this->getModuleName());
			if ($success = $recordModel->isEditable()) {
				$end = $this->changeDateTime($recordModel->get('date_end'), $delta);
				$dueDate = $end['date'] . ' ' . $end['time'];
				$recordModel->set('date_start', $dateStart);
				$recordModel->set('date_end', $dueDate);
				$recordModel->save();
				$success = true;
			}
		} catch (Exception $e) {
			\App\Log::error($e->__toString());
			$success = false;
		}
		return $success;
	}
}

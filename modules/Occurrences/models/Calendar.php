<?php

/**
 * Occurrences calendar model files.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Occurrences calendar model class.
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
				],
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
		if (!empty($this->get('user')) && isset($this->get('user')['selectedIds'][0])) {
			$selectedUsers = $this->get('user');
			$selectedIds = $selectedUsers['selectedIds'];
			if ('all' !== $selectedIds[0]) {
				$conditions[] = ['vtiger_crmentity.smownerid' => $selectedIds];
				$subQuery = (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')->where(['userid' => $selectedIds]);
				$conditions[] = ['vtiger_crmentity.crmid' => $subQuery];
			}
			if (isset($selectedUsers['excludedIds']) && 'all' === $selectedIds[0]) {
				$conditions[] = ['not in', 'vtiger_crmentity.smownerid', $selectedUsers['excludedIds']];
			}
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
		$colors = \App\Fields\Picklist::getColors('occurrences_type', false);
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

			$item['borderColor'] = $colors[$record['occurrences_type']] ?? '';
			$item['className'] = 'js-popover-tooltip--record ownerCBg_' . $record['assigned_user_id'];
			if ($isSummaryViewSupported) {
				$item['url'] = 'index.php?module=' . $this->getModuleName() . '&view=Detail&record=' . $record['id'];
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

	/** {@inheritdoc} */
	public function updateEvent(int $recordId, string $start, string $end, App\Request $request): bool
	{
		try {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $this->getModuleName());
			if ($success = $recordModel->isEditable()) {
				$recordModel->set('date_start', App\Fields\DateTime::formatToDb($start));
				$recordModel->set('date_end', App\Fields\DateTime::formatToDb($end));
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

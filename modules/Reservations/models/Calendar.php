<?php

/**
 * Reservations calendar model file.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Reservations calendar model class.
 */
class Reservations_Calendar_Model extends Vtiger_Calendar_Model
{
	/** {@inheritdoc} */
	public function getSideBarLinks($linkParams)
	{
		$links = parent::getSideBarLinks($linkParams);
		$link = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'SIDEBARWIDGET',
			'linklabel' => 'LBL_TYPE',
			'linkdata' => ['cache' => 'calendar-types', 'name' => 'types'],
			'template' => 'Filters/ActivityTypes.tpl',
			'filterData' => Vtiger_CalendarRightPanel_Model::getCalendarTypes($this->getModuleName()),
		]);
		array_unshift($links, $link);
		return $links;
	}

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
		$queryGenerator->setFields(['id', 'date_start', 'time_start', 'time_end', 'due_date', 'title', 'assigned_user_id', 'reservations_status']);
		if ($types = $this->get('types')) {
			$queryGenerator->addCondition('type', implode('##', $types), 'e');
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
					['>=', new \yii\db\Expression("CONCAT(vtiger_reservations.date_start, ' ', vtiger_reservations.time_start)"), $dbStartDateTime],
					['<=', new \yii\db\Expression("CONCAT(vtiger_reservations.date_start, ' ', vtiger_reservations.time_start)"), $dbEndDateTime],
				],
				[
					'and',
					['>=', new \yii\db\Expression("CONCAT(vtiger_reservations.due_date, ' ', vtiger_reservations.time_end)"), $dbStartDateTime],
					['<=', new \yii\db\Expression("CONCAT(vtiger_reservations.due_date, ' ', vtiger_reservations.time_end)"), $dbEndDateTime],
				],
				[
					'and',
					['<', 'vtiger_reservations.date_start', $dbStartDate],
					['>', 'vtiger_reservations.due_date', $dbEndDate],
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
		$query->orderBy(['vtiger_reservations.date_start' => SORT_ASC, 'vtiger_reservations.time_start' => SORT_ASC]);

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
		$moduleModel = Vtiger_Module_Model::getInstance($this->getModuleName());
		$isSummaryViewSupported = $moduleModel->isSummaryViewSupported();
		$colors = \App\Fields\Picklist::getColors('reservations_status', false);
		while ($record = $dataReader->read()) {
			$item = [];
			$item['id'] = $record['id'];
			$item['title'] = \App\Purifier::encodeHtml($record['title']);

			$dateTimeInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$item['start'] = DateTimeField::convertToUserTimeZone($record['date_start'] . ' ' . $record['time_start'])->format('Y-m-d') . ' ' . $dateTimeInstance->getFullcalenderTime();
			$item['start_display'] = $dateTimeInstance->getDisplayDateTimeValue();

			$dateTimeInstance = new DateTimeField($record['due_date'] . ' ' . $record['time_end']);
			$item['end'] = DateTimeField::convertToUserTimeZone($record['due_date'] . ' ' . $record['time_end'])->format('Y-m-d') . ' ' . $dateTimeInstance->getFullcalenderTime();
			$item['end_display'] = $dateTimeInstance->getDisplayDateTimeValue();

			$item['borderColor'] = $colors[$record['reservations_status']] ?? '';
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
	 * Function to get calendar types.
	 *
	 * @return string[]
	 */
	public function getCalendarTypes()
	{
		$calendarTypes = [];
		$moduleField = $this->getModule()->getFieldByName('type');
		if ($moduleField && $moduleField->isActiveField()) {
			$calendarTypes = (new App\Db\Query())->select(['tree', 'label'])->from('vtiger_trees_templates_data')
				->where(['templateid' => $moduleField->getFieldParams()])
				->createCommand()->queryAllByGroup(0);
		}
		return $calendarTypes;
	}
}

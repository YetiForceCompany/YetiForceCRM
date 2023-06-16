<?php

/**
 * Calendar model file.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author   Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Calendar model class.
 */
abstract class Vtiger_Calendar_Model extends App\Base
{
	/** @var string Module name */
	public $moduleName;
	/** @var \Vtiger_Module_Model Module model */
	public $module;

	/**
	 * Get module name.
	 *
	 * @return string
	 */
	public function getModuleName()
	{
		return $this->moduleName;
	}

	/**
	 * Get module model.
	 *
	 * @return \Vtiger_Module_Model
	 */
	public function getModule()
	{
		if (!isset($this->module)) {
			$this->module = Vtiger_Module_Model::getInstance($this->getModuleName());
		}
		return $this->module;
	}

	/**
	 * Get records count for extended calendar left column.
	 *
	 * @return int|string
	 */
	public function getEntityRecordsCount()
	{
		return $this->getQuery()->count();
	}

	/**
	 * Function to get the right side bar links for the module.
	 *
	 * @param array $linkParams
	 *
	 * @return Vtiger_Link_Model[]
	 */
	public function getSideBarLinks(array $linkParams): array
	{
		$links = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), ['SIDEBARWIDGET'], $linkParams)['SIDEBARWIDGET'] ?? [];
		$links[] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'SIDEBARWIDGET',
			'linklabel' => 'LBL_TYPE',
			'linkclass' => 'text-center',
			'template' => 'Calendar/Filters/Switch.tpl',
		]);
		if ($types = $this->getCalendarTypes()) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_TYPE',
				'linkdata' => ['cache' => 'calendar-types', 'name' => 'types'],
				'template' => 'Calendar/Filters/ActivityTypes.tpl',
				'filterData' => $types,
			]);
		}
		$request = \App\Request::init();
		$historyUsers = $request->has('user') ? $request->get('user') : [];
		$links[] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'SIDEBARWIDGET',
			'linkclass' => 'js-users-form usersForm ',
			'template' => 'Calendar/Filters/Users.tpl',
			'filterData' => Vtiger_CalendarRightPanel_Model::getUsersList($this->getModuleName()),
			'historyUsers' => $historyUsers,
		]);
		$links[] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'SIDEBARWIDGET',
			'linkclass' => 'js-group-form groupForm',
			'template' => 'Calendar/Filters/Groups.tpl',
			'filterData' => Vtiger_CalendarRightPanel_Model::getGroupsList($this->getModuleName()),
			'historyUsers' => $historyUsers,
		]);
		$privileges = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($privileges->hasModuleActionPermission($this->getModuleName(), 'CalendarExtraSources')) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_EXTRA_SOURCES',
				'linkclass' => 'js-extra-sources-form',
				'template' => 'Calendar/Filters/ExtraSources.tpl',
				'filterData' => Vtiger_CalendarExtSource_Model::getByModule($this->getModule()->getId()),
				'history' => $request->has('extraSources') ? $request->get('extraSources') : [],
			]);
		}
		return $links;
	}

	/**
	 * Static Function to get the instance of Vtiger Module Model for the given id or name.
	 *
	 * @param mixed id or name of the module
	 * @param mixed $moduleName
	 */
	public static function getInstance(string $moduleName)
	{
		$className = Vtiger_Loader::getComponentClassName('Model', 'Calendar', $moduleName);
		$handler = new $className();
		$handler->moduleName = $moduleName;
		return $handler;
	}

	/**
	 * Get calendar types list.
	 *
	 * @return string[]
	 */
	public function getCalendarTypes(): array
	{
		return [];
	}

	/**
	 * Get public holidays for rendering them on the calendar.
	 *
	 * @return array
	 */
	public function getPublicHolidays()
	{
		$result = [];
		foreach (App\Fields\Date::getHolidays(DateTimeField::convertToDBTimeZone($this->get('start'))->format('Y-m-d'), DateTimeField::convertToDBTimeZone($this->get('end'))->format('Y-m-d')) as $holiday) {
			$item = [
				'title' => $holiday['name'],
				'start' => $holiday['date'],
				'display' => 'background',
			];
			if ('national' === $holiday['type']) {
				$item['color'] = '#FFAB91';
				$item['icon'] = 'fas fa-flag';
			} else {
				$item['color'] = '#81D4FA';
				$item['icon'] = 'fas fa-church';
			}
			$result[] = $item;
		}
		return $result;
	}

	/**
	 * Gest query.
	 *
	 * @return \App\Db\Query
	 */
	abstract public function getQuery();

	/**
	 * Function to get records.
	 *
	 * @return array
	 */
	abstract public function getEntity();

	/**
	 * Update event.
	 *
	 * @param int          $recordId Record ID
	 * @param string       $start    Start date
	 * @param string       $end      End date
	 * @param \App\Request $request  Request instance
	 *
	 * @return bool
	 */
	public function updateEvent(int $recordId, string $start, string $end, App\Request $request): bool
	{
		try {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $this->getModuleName());
			if ($success = $recordModel->isEditable()) {
				$start = DateTimeField::convertToDBTimeZone($start);
				$recordModel->set('date_start', $start->format('Y-m-d'));
				$recordModel->set('time_start', $start->format('H:i:s'));
				$end = DateTimeField::convertToDBTimeZone($end);
				$recordModel->set('due_date', $end->format('Y-m-d'));
				$recordModel->set('time_end', $end->format('H:i:s'));
				$recordModel->save();
				$success = true;
			}
		} catch (Exception $e) {
			\App\Log::error($e->__toString());
			$success = false;
		}
		return $success;
	}

	/**
	 * Get calendar extra sources rows.
	 *
	 * @return array
	 */
	public function getExtraSources(): array
	{
		$result = [];
		if ($this->get('extraSources')) {
			foreach ($this->get('extraSources') as $sourceId) {
				$source = Vtiger_CalendarExtSource_Model::getInstanceById($sourceId);
				$source->set('start', $this->get('start'));
				$source->set('end', $this->get('end'));
				$source->set('user', $this->get('user'));
				$result = array_merge($result, $source->getRows());
			}
		}
		return $result;
	}

	/**
	 * Get calendar extra sources counter.
	 *
	 * @return int
	 */
	public function getExtraSourcesCount(): int
	{
		$counter = 0;
		if ($this->get('extraSources')) {
			foreach ($this->get('extraSources') as $sourceId) {
				$source = Vtiger_CalendarExtSource_Model::getInstanceById($sourceId);
				$source->set('start', $this->get('start'));
				$source->set('end', $this->get('end'));
				$source->set('user', $this->get('user'));
				$counter += $source->getExtraSourcesCount();
			}
		}
		return $counter;
	}
}

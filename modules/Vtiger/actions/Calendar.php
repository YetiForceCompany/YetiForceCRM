<?php

/**
 * Calendar actions file.
 *
 * @package Action
 *
 * @copyright 	YetiForce S.A.
 * @license 	YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author   	Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    	Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Calendar actions class.
 */
class Vtiger_Calendar_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		$privileges = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$privileges->hasModulePermission($moduleName) || !\method_exists(Vtiger_Module_Model::getInstance($moduleName), 'getCalendarViewUrl')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if ('updateEvent' === $request->getMode() && ($request->isEmpty('id', true) || !\App\Privilege::isPermitted($moduleName, 'EditView', $request->getInteger('id')))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if ('saveExtraSources' === $request->getMode() || 'deleteExtraSources' === $request->getMode()) {
			if ($privileges->hasModuleActionPermission($moduleName, 'CalendarExtraSources')) {
				throw new \App\Exceptions\NoPermitted('ERR_ILLEGAL_VALUE');
			}
			if (!$request->isEmpty('id')) {
				$source = Vtiger_CalendarExtSource_Model::getInstanceById($request->getInteger('id'));
				if (!$privileges->isAdminUser() && $source->get('user_id') != $privileges->getId()) {
					throw new \App\Exceptions\NoPermitted('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
				}
			}
		}
	}

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getEvents');
		$this->exposeMethod('getEventsYear');
		$this->exposeMethod('updateEvent');
		$this->exposeMethod('getCountEventsGroup');
		$this->exposeMethod('pinOrUnpinUser');
		$this->exposeMethod('saveExtraSources');
		$this->exposeMethod('deleteExtraSources');
	}

	public function getEvents(App\Request $request)
	{
		$record = $this->getCalendarModel($request);
		$entity = array_merge($record->getEntity(), $record->getPublicHolidays(), $record->getExtraSources());
		$response = new Vtiger_Response();
		$response->setResult($entity);
		$response->emit();
	}

	/**
	 * Get count Events for extended calendar's left column.
	 *
	 * @param \App\Request $request
	 */
	public function getCountEventsGroup(App\Request $request)
	{
		$request->delete('end');
		$record = $this->getCalendarModel($request);
		$result = [];
		foreach ($request->getArray('dates', 'date') as $datePair) {
			$record->set('start', App\Fields\DateTime::formatToDisplay($datePair[0] . ' 00:00:00'));
			$record->set('end', App\Fields\DateTime::formatToDisplay($datePair[1] . ' 23:59:59'));
			$result[] = $record->getEntityRecordsCount() + $record->getExtraSourcesCount();
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Get calendar model.
	 *
	 * @param App\Request $request
	 *
	 * @return Vtiger_Calendar_Model
	 */
	public function getCalendarModel(App\Request $request): Vtiger_Calendar_Model
	{
		$record = Vtiger_Calendar_Model::getInstance($request->getModule());
		$record->set('user', $request->getArray('user', 'Alnum'));
		$record->set('types', $request->getArray('types', 'Text'));
		$record->set('time', $request->isEmpty('time') ? '' : $request->getByType('time'));
		if ($request->has('start') && $request->has('end')) {
			$record->set('start', $request->getByType('start', 'DateInUserFormat'));
			$record->set('end', $request->getByType('end', 'DateInUserFormat'));
		}
		if ($request->has('filters')) {
			$record->set('filters', $request->getByType('filters', 'Alnum'));
		}
		if ($request->has('cvid')) {
			$record->set('customFilter', $request->getInteger('cvid'));
		}
		if ($request->has('extraSources')) {
			$record->set('extraSources', $request->getArray('extraSources', 'Integer'));
		}
		return $record;
	}

	/**
	 * Update event.
	 *
	 * @param App\Request $request
	 */
	public function updateEvent(App\Request $request)
	{
		$record = Vtiger_Calendar_Model::getInstance($request->getModule());
		$success = $record->updateEvent($request->getInteger('id'), $request->getByType('start', 'dateTimeInUserFormat'), $request->getByType('end', 'dateTimeInUserFormat'), $request);
		$response = new Vtiger_Response();
		$response->setResult($success);
		$response->emit();
	}

	/**
	 * Get count Events for extended calendar's left column.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function pinOrUnpinUser(App\Request $request): void
	{
		$db = \App\Db::getInstance();
		$userId = \App\User::getCurrentUserId();
		if (!$request->isEmpty('element_id')) {
			$id = $request->getInteger('element_id');
			if (\App\User::isExists($id)) {
				$users = Vtiger_CalendarRightPanel_Model::getFavoriteUsers($request->getModule());
				if (empty($users[$id])) {
					$db->createCommand()->insert('u_#__users_pinned', [
						'user_id' => $userId,
						'tabid' => \App\Module::getModuleId($request->getModule()),
						'fav_id' => $id,
					])->execute();
					$result = 'pin';
				} else {
					$db->createCommand()->delete('u_#__users_pinned', ['id' => $users[$id]])->execute();
					$result = 'unpin';
				}
				\App\Cache::delete('FavoriteUsers', $userId);
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Save extra sources.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public static function saveExtraSources(App\Request $request): void
	{
		$model = Vtiger_CalendarExtSource_Model::getCleanInstance($request->getModule());
		$model->setData([
			'id' => $request->isEmpty('id', true) ? 0 : $request->getInteger('id'),
			'label' => $request->getByType('label', \App\Purifier::TEXT),
			'base_module' => $request->getInteger('base_module'),
			'target_module' => $request->getInteger('target_module'),
			'type' => $request->getInteger('type'),
			'public' => $request->getBoolean('public') ? 1 : 0,
			'include_filters' => $request->getBoolean('include_filters') ? 1 : 0,
			'color' => $request->isEmpty('color', true) ? '' : $request->getByType('color', 'Color'),
			'custom_view' => $request->getInteger('custom_view'),
			'fieldid_a_date' => $request->getInteger('fieldid_a_date'),
			'fieldid_a_time' => $request->isEmpty('fieldid_a_time', true) ? 0 : $request->getInteger('fieldid_a_time'),
			'fieldid_b_date' => $request->isEmpty('fieldid_b_date', true) ? 0 : $request->getInteger('fieldid_b_date'),
			'fieldid_b_time' => $request->isEmpty('fieldid_b_time', true) ? 0 : $request->getInteger('fieldid_b_time'),
		]);
		$response = new Vtiger_Response();
		$response->setResult($model->save());
		$response->emit();
	}

	/**
	 * Delete extra sources.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public static function deleteExtraSources(App\Request $request): void
	{
		$response = new Vtiger_Response();
		$response->setResult(Vtiger_CalendarExtSource_Model::getInstanceById($request->getInteger('id'))->delete());
		$response->emit();
	}
}

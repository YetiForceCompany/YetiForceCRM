<?php

/**
 * Change value handler action file.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Change value handler action class.
 */
class Vtiger_ChangeValueHandler_Action extends \App\Controller\Action
{
	/**
	 * Record model instance.
	 *
	 * @var Vtiger_Record_Model
	 */
	protected $record;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		if ($request->isEmpty('record', true)) {
			$this->record = Vtiger_Record_Model::getCleanInstance($moduleName);
			if (!$this->record->isCreateable()) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		} else {
			$recordId = $request->getInteger('record');
			if (!\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId)) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
			$this->record = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			if (!$this->record->isEditable()) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$this->getRecordModelFromRequest($request);
		$eventHandler = $this->record->getEventHandler();
		$events = [];
		foreach ($eventHandler->getHandlers(\App\EventHandler::EDIT_VIEW_CHANGE_VALUE) as $className => $handler) {
			if ($handlerResponse = $eventHandler->triggerHandler($handler)) {
				$events[$className] = $handlerResponse;
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($events);
		$response->emit();
	}

	/**
	 * Function to get the record model based on the request parameters.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	protected function getRecordModelFromRequest(App\Request $request): void
	{
		$fieldModelList = $this->record->getModule()->getFields();
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			if (!$fieldModel->isWritable()) {
				continue;
			}
			if ($request->has($fieldName)) {
				$fieldModel->getUITypeModel()->setValueFromRequest($request, $this->record);
			}
		}
		if ($request->has('inventory') && $this->record->getModule()->isInventory()) {
			$this->record->initInventoryDataFromRequest($request);
		}
	}
}

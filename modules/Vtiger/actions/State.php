<?php

/**
 * Record state action class.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class Vtiger_State_Action.
 */
class Vtiger_State_Action extends \App\Controller\Action
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
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$this->record = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		if (!(('Archived' === $request->getByType('state') && $this->record->privilegeToArchive())
			|| ('Trash' === $request->getByType('state') && $this->record->privilegeToMoveToTrash())
			|| ('Active' === $request->getByType('state') && $this->record->privilegeToActivate()))
		) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$result = [];
		$eventHandler = $this->record->getEventHandler();
		$skipHandlers = $request->getArray('skipHandlers', \App\Purifier::ALNUM, [], \App\Purifier::INTEGER);
		foreach ($eventHandler->getHandlers(\App\EventHandler::PRE_STATE_CHANGE) as $handler) {
			$handlerId = $handler['eventhandler_id'];
			$response = $eventHandler->triggerHandler($handler);
			if (!($response['result'] ?? null) && (!isset($response['hash'], $skipHandlers[$handlerId]) || $skipHandlers[$handlerId] !== $response['hash'])) {
				if ($result && 'confirm' === ($response['type'] ?? '')) {
					break;
				}
				$result[$handlerId] = $response;
			}
		}

		if (!$result) {
			$this->record->changeState($request->getByType('state'));
			if ('List' === $request->getByType('sourceView')) {
				$result = ['notify' => ['type' => 'success', 'text' => \App\Language::translate('LBL_CHANGES_SAVED')]];
			} else {
				$result = ['url' => $this->record->getDetailViewUrl()];
			}
		}

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
}

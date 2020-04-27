<?php

/**
 * Meeting.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class Vtiger_Meeting_Action.
 */
class Vtiger_Meeting_Action extends \App\Controller\Action
{
	/**
	 * Record model instance.
	 *
	 * @var Vtiger_Record_Model
	 */
	protected $record;

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		if ($request->isEmpty('record', true)) {
			$this->record = \Vtiger_Record_Model::getCleanInstance($moduleName);
			$permission = $this->record->isCreateable();
		} else {
			$this->record = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
			$permission = $this->record->isEditable();
		}
		$fieldModel = $this->record->getField($request->getByType('fieldName', \App\Purifier::ALNUM));
		if (!$permission || !$fieldModel || !$fieldModel->isEditable() || !\App\MeetingService::getInstance()->isActive()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$meeting = \App\MeetingService::getInstance();
		$url = '';
		try {
			$room = $meeting->generateRoomName((string) \App\User::getCurrentUserRealId() . '_' . $this->record->getId());
			$url = $meeting->getUrl(['room' => $room, 'exp' => strtotime(date('Y-m-d') . ' 23:59:59')]);
		} catch (\Throwable $e) {
			\App\Log::error($e->__toString());
		}
		$response = new Vtiger_Response();
		$response->setResult(['success' => !empty($url), 'url' => $url]);
		$response->emit();
	}
}

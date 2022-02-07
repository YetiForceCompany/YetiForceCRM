<?php

/**
 * Meeting.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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

	/** {@inheritdoc} */
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

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$meeting = \App\MeetingService::getInstance();
		$url = '';
		$date = date('Y-m-d');
		try {
			$moduleModel = Vtiger_Module_Model::getInstance($request->getModule());
			$room = $meeting->generateRoomName((string) $request->getByType('roomName', \App\Purifier::TEXT, ''));
			if ($request->has('exp') && ($expFieldName = $request->getByType('expField', \App\Purifier::ALNUM))
				&& ($expField = $moduleModel->getFieldByName($expFieldName)) && $expField->isActiveField()
			) {
				$date = $request->getByType('exp', \App\Purifier::DATE_USER_FORMAT, true);
			}
			$url = $meeting->getUrl(['room' => $room, 'exp' => strtotime($date . ' 23:59:59')]);
		} catch (\Throwable $e) {
			\App\Log::error($e->__toString());
		}
		$response = new Vtiger_Response();
		$response->setResult(['success' => !empty($url), 'url' => $url]);
		$response->emit();
	}
}

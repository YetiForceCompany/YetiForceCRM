<?php

/**
 * Record activity file.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Record activity class.
 */
class Vtiger_RecordActivity_View extends Vtiger_Index_View
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
		if ($request->isEmpty('record', true) || !\App\Config::performance('recordActivityNotifier', false) || !($this->record = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule()))->isViewable() || !$this->record->getModule()->isTrackingEnabled() || !$this->record->getModule()->isPermitted('RecordActivityNotifier')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $this->record->getModuleName();
		$dateTime = $request->getByType('dateTime', 'dateTimeInUserFormat');
		$dateTime = \App\Fields\DateTime::formatToDb($dateTime);
		$currentTime = (new \DateTimeField(date('Y-m-d H:i:s')))->getDisplayFullDateTimeValue();
		$actions = ModTracker::getAllActionsTypes();
		unset($actions[ModTracker::$SHOW_HIDDEN_DATA]);
		$updates = [];
		$content = '';

		$query = (new \App\Db\Query())
			->from('vtiger_modtracker_basic')
			->where(['vtiger_modtracker_basic.crmid' => $this->record->getId()])
			->andWhere(['>=', 'vtiger_modtracker_basic.changedon', $dateTime])
			->andWhere(['<>', 'vtiger_modtracker_basic.whodid', \App\User::getCurrentUserRealId()])
			->andWhere(['vtiger_modtracker_basic.status' => array_keys($actions)])
			->orderBy(['vtiger_modtracker_basic.id' => SORT_ASC])->limit(5);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$recordModel = new ModTracker_Record_Model();
			$recordModel->setData($row)->setParent($row['crmid'], $moduleName);
			$updates[$recordModel->getId()] = $recordModel;
		}
		if ($updates) {
			$viewer = $this->getViewer($request);
			$viewer->assign('UPDATES', $updates);
			$viewer->assign('SOURCE_MODULE_NAME', 'ModTracker');
			$content = $viewer->view('RecordActivityContent.tpl', 'ModTracker', true);
		}

		$response = new Vtiger_Response();
		$response->setEmitType(\Vtiger_Response::$EMIT_JSON);
		$response->setResult(['title' => \App\Language::translate('LBL_ATTENTION'), 'dateTime' => $currentTime, 'text' => $content]);
		$response->emit();
	}

	/** {@inheritdoc} */
	public function isSessionExtend(App\Request $request)
	{
		return false;
	}
}

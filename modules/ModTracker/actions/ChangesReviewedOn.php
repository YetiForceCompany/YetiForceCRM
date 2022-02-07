<?php

/**
 * ChangesReviewedOn Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class ModTracker_ChangesReviewedOn_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
		$sourceModule = $request->getByType('sourceModule', 2);
		if ($request->has('record')) {
			$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'));
			if (!$recordModel->isViewable() || !$recordModel->getModule()->isTrackingEnabled()) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		} elseif ($sourceModule) {
			$moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
			if (!$moduleModel || !$moduleModel->isTrackingEnabled()) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		} else {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getUnreviewed');
		$this->exposeMethod('reviewChanges');
	}

	public function process(App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);

			return;
		}
		$record = $request->getInteger('record');
		$result = ModTracker_Record_Model::setLastReviewed($record);
		ModTracker_Record_Model::unsetReviewed($record, false, $result);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function getUnreviewed(App\Request $request)
	{
		$records = $request->getArray('recordsId', 'Integer');
		foreach ($records as $key => $record) {
			if (!\App\Privilege::isPermitted($request->getByType('sourceModule', 2), 'DetailView', $record)) {
				unset($records[$key]);
			}
		}
		$result = ModTracker_Record_Model::getUnreviewed($records, false, true);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function marks forwarded records as reviewed.
	 *
	 * @param \App\Request $request
	 */
	public function reviewChanges(App\Request $request)
	{
		$moduleName = $request->getModule();
		$sourceModule = $request->getByType('sourceModule', 2);
		$request->set('module', $sourceModule);
		$result = false;
		$recordsList = Vtiger_Mass_Action::getRecordsListFromRequest($request);
		if (\is_array($recordsList) && \count($recordsList) > App\Config::module($moduleName, 'REVIEW_CHANGES_LIMIT')) {
			$selectedIds = $request->getArray('selected_ids', \App\Purifier::ALNUM);
			$params = $selectedIds && 'all' === $selectedIds[0] ? ['viewname', 'selected_ids', 'excluded_ids', 'search_key', 'search_value', 'operator', 'search_params', 'entityState'] : ['selected_ids'];
			foreach ($params as $variable) {
				if ($request->has($variable)) {
					$data[$variable] = $request->get($variable);
				}
			}
			ModTracker_Relation_Model::reviewChangesQueue($data, $sourceModule);
			$cronInfo = \vtlib\Cron::getInstance('LBL_MARK_RECORDS_AS_REVIEWED');
			$message = \App\Language::translate('LBL_REVIEW_CHANGES_LIMIT_DESCRIPTION', $moduleName);
			if ($cronInfo && $cronInfo->getStatus()) {
				$message .= '<br />' . \App\Language::translate('LBL_ESTIMATED_TIME', $moduleName) . ': ' . ($cronInfo->getFrequency() / 60) . \App\Language::translate('LBL_MINUTES');
			}
			$result = [$message];
		} else {
			ModTracker_Relation_Model::reviewChanges($recordsList);
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}

<?php

/**
 * ChangesReviewedOn Class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class ModTracker_ChangesReviewedOn_Action extends Vtiger_Action_Controller
{

	public function checkPermission(\App\Request $request)
	{
		$record = $request->get('record');
		$sourceModule = $request->get('sourceModule');
		if (!empty($record)) {
			$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($record);
			if (!$recordModel->getModule()->isTrackingEnabled()) {
				throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
			}
		} elseif (!empty($sourceModule)) {
			$moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
			if (!$moduleModel || $moduleModel->isTrackingEnabled()) {
				throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
			}
		} else {
			throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
	}

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getUnreviewed');
		$this->exposeMethod('reviewChanges');
	}

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		$record = $request->get('record');
		$result = ModTracker_Record_Model::setLastReviewed($record);
		ModTracker_Record_Model::unsetReviewed($record, false, $result);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function getUnreviewed(\App\Request $request)
	{
		$records = $request->get('recordsId');
		$result = ModTracker_Record_Model::getUnreviewed($records, false, true);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function marks forwarded records as reviewed
	 * @param \App\Request $request
	 */
	public function reviewChanges(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$sourceModule = $request->get('sourceModule');
		$request->set('module', $sourceModule);
		$result = false;
		$recordsList = Vtiger_Mass_Action::getRecordsListFromRequest($request);
		if (is_array($recordsList) && count($recordsList) > AppConfig::module($moduleName, 'REVIEW_CHANGES_LIMIT')) {
			$params = $request->get('selected_ids') === 'all' ? ['viewname', 'selected_ids', 'excluded_ids', 'search_key', 'search_value', 'operator', 'search_params'] : ['selected_ids'];
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

	public function validateRequest(\App\Request $request)
	{
		return $request->validateWriteAccess();
	}
}

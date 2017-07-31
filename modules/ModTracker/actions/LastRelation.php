<?php

/**
 * LastRelation Class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class ModTracker_LastRelation_Action extends Vtiger_Action_Controller
{

	/**
	 * Checking permission
	 * @param \App\Request $request
	 * @throws \Exception\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		$sourceModule = $request->get('sourceModule');
		$records = $request->get('recordsId');
		if (!empty($sourceModule)) {
			if (!in_array($sourceModule, AppConfig::module('ModTracker', 'SHOW_TIMELINE_IN_LISTVIEW')) || !\App\Privilege::isPermitted($sourceModule, 'TimeLineList')) {
				throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
			}
			foreach ($records as $key => $recordId) {
				if (!App\Privilege::isPermitted($sourceModule, 'DetailView', $recordId)) {
					throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
				}
			}
		} else {
			throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Process
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$records = $request->get('recordsId');
		$result = ModTracker_Record_Model::getLastRelation($records, $request->get('sourceModule'));
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Validate request
	 * @param \App\Request $request
	 * @return type
	 */
	public function validateRequest(\App\Request $request)
	{
		return $request->validateWriteAccess();
	}
}

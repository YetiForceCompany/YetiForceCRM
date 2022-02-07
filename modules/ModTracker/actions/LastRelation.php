<?php

/**
 * LastRelation Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class ModTracker_LastRelation_Action extends \App\Controller\Action
{
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
		$records = $request->getArray('recordsId', 'Integer');
		if ($sourceModule) {
			if (!\in_array($sourceModule, App\Config::module('ModTracker', 'SHOW_TIMELINE_IN_LISTVIEW')) || !\App\Privilege::isPermitted($sourceModule, 'TimeLineList')) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
			foreach ($records as $recordId) {
				if (!App\Privilege::isPermitted($sourceModule, 'DetailView', $recordId)) {
					throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
				}
			}
		} else {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$records = $request->getArray('recordsId', 'Integer');
		$result = ModTracker_Record_Model::getLastRelation($records, $request->getByType('sourceModule', 2));
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}

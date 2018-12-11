<?php

/**
 * Mass records delete action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class RecycleBin_MassDelete_Action extends Vtiger_Mass_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		$userPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPriviligesModel->hasModuleActionPermission($request->getModule(), 'MassDelete')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getByType('sourceModule', 2);
		$recordIds = self::getRecordsListFromRequest($request);
		$deleteMaxCount = AppConfig::module('RecycleBin', 'DELETE_MAX_COUNT');
		$skipped = [];
		foreach ($recordIds as $key => $recordId) {
			if (0 < $deleteMaxCount) {
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
				if (!$recordModel->privilegeToDelete()) {
					$skipped[] = $recordModel->getName();
					continue;
				}
				$recordModel->delete();
				unset($recordModel);
				$deleteMaxCount--;
			} else {
				(new App\BatchMethod(['method' => 'RecycleBin_Module_Model::deleteRecords', 'params' => App\Json::encode($recordIds)]))->save();
				break;
			}
			unset($recordIds[$key]);
		}
		$text = \App\Language::translate('LBL_CHANGES_SAVED');
		$type = 'success';
		if ($skipped) {
			$type = 'info';
			$text .= PHP_EOL . \App\Language::translate('LBL_OMITTED_RECORDS');
			foreach ($skipped as $name) {
				$text .= PHP_EOL . $name;
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(['notify' => ['text' => $text, 'type' => $type]]);
		$response->emit();
	}
}

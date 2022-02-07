<?php

/**
 * Unlocking record.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class Vtiger_Unlock_Action.
 */
class Vtiger_RecordUnlock_Action extends \App\Controller\Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record', true) || !Vtiger_Record_Model::getInstanceById($request->getInteger('record'))->isUnlockByFields()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD2', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'));
		foreach ($recordModel->getUnlockFields() as $fieldName => $values) {
			if ($request->has($fieldName)) {
				$recordModel->getField($fieldName)->getUITypeModel()->setValueFromRequest($request, $recordModel);
			}
		}
		$recordModel->clearPrivilegesCache();
		$result = false;
		if (!$recordModel->getUnlockFields()) {
			$recordModel->save();
			$result = true;
		}
		$response = new Vtiger_Response();
		$response->setResult(['success' => $result, 'url' => $recordModel->getDetailViewUrl()]);
		$response->emit();
	}
}

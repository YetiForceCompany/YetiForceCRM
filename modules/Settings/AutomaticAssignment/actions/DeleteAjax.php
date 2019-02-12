<?php

/**
 * Class to delete.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_AutomaticAssignment_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/**
	 * Function  proccess.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$recordModel = Settings_AutomaticAssignment_Record_Model::getInstanceById($request->getInteger('record'));
		$recordModel->delete();
		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($recordModel->getId());
		$responceToEmit->emit();
	}
}

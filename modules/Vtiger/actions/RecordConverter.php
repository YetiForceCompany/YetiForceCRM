<?php

/**
 * Record Converter Action Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class Vtiger_RecordConverter_Action extends \App\Controller\Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule(), 'RecordConventer')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Function process.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$records = Vtiger_Mass_Action::getRecordsListFromRequest($request);
		$convertId = $request->getInteger('convertId');
		$convertInstance = \App\RecordConverter::getInstanceById($convertId, $moduleName);
		$redirect = '';
		if (count($records) === 1 && $convertInstance->get('redirect_to_edit') && count(explode(',', $convertInstance->get('destiny_module')))) {
			$redirect = 'index.php?module=' . App\Module::getModuleName($convertInstance->get('destiny_module')) . '&view=Edit&recordConverter=' . $convertId . '&sourceId=' . $records[0] . '&sourceModule=' . $moduleName;
		} else {
			$convertInstance->process($records);
		}
		$response = new Vtiger_Response();
		$response->setResult(['redirect' => $redirect, 'createdRecords' => sprintf(\App\Language::translate('LBL_CREATED_CONVERT_RECORDS', $moduleName), $convertInstance->createdRecords), 'error' => $convertInstance->error]);
		$response->emit();
	}
}

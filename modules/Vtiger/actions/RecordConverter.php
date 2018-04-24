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
		$moduleName = $request->getModule();
		if (!\App\Privilege::isPermitted($moduleName, 'RecordConventer')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$convertInstance = \App\RecordConverter::getInstanceById($request->getInteger('convertId'), $moduleName);
		foreach (explode(',', $convertInstance->get('destiny_modules')) as $destinyModuleId) {
			if (!\App\Privilege::isPermitted(\App\Module::getModuleName($destinyModuleId), 'CreateView')) {
				\App\Log::warning("No permitted to action CreateView in module $destinyModuleId in view RecordConventer");
			}
		}
	}

	/**
	 * Function process.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return bool|void
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$records = Vtiger_Mass_Action::getRecordsListFromRequest($request);
		$convertInstance = \App\RecordConverter::getInstanceById($request->getInteger('convertId'), $moduleName);
		$redirect = '';
		$convertInstance->process($records);
		if (count($records) === 1 && $convertInstance->get('redirect_to_edit')) {
			$redirect = 'index.php?module=' . $request->getByType('destinyModule') . '&view=Edit&recordConverter=' . $request->getInteger('convertId') . '&sourceId=' . $records[0] . '&sourceModule=' . $moduleName;
		}
		$response = new Vtiger_Response();
		$response->setResult(['redirect' => $redirect, 'createdRecords' => sprintf(\App\Language::translate('LBL_CREATED_CONVERT_RECORDS', $moduleName), $convertInstance->createdRecords), 'error' => $convertInstance->error]);
		$response->emit();
	}
}

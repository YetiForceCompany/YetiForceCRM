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
		/**
		 * TODO uprawnienia.
		 */
		return true;
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$records = Vtiger_Mass_Action::getRecordsListFromRequest($request);
		$convertInstance = \App\RecordConverter::getInstanceById($request->getInteger('convertId'), $moduleName);
		$convertInstance->process($records, $request->getByType('selectedModule'));
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}

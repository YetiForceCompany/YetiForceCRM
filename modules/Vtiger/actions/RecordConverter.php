<?php

/**
 * Record Converter Action Class.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Class RecordConverter.
 */
class Vtiger_RecordConverter_Action extends \App\Controller\Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule(), 'RecordConventer')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$records = Vtiger_Mass_Action::getRecordsListFromRequest($request);
		$convertId = $request->getInteger('convertId');
		$convertInstance = \App\RecordConverter::getInstanceById($convertId, $moduleName);
		$redirect = '';
		if (1 === \count($records) && $convertInstance->get('redirect_to_edit') && $convertInstance->isPermitted(current($records))) {
			$redirect = 'index.php?module=' . App\Module::getModuleName($convertInstance->get('destiny_module')) . '&view=Edit&recordConverter=' . $convertId . '&sourceRecord=' . $records[0] . '&sourceModule=' . $moduleName;
		} else {
			$convertInstance->process($records);
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'redirect' => $redirect,
			'createdRecords' => \App\Language::translateArgs('LBL_CREATED_CONVERT_RECORDS', $moduleName, \count($convertInstance->createdRecords)),
			'error' => $convertInstance->error
		]);
		$response->emit();
	}
}

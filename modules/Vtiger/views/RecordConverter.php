<?php

/**
 * Record converter view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class Vtiger_RecordConverter_View extends \App\Controller\Modal
{
	/**
	 * Function checks permission to view.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$moduleConverters = \App\RecordConverter::getModuleConverters(\App\Module::getModuleId($moduleName), $request->get('inView'));
		if (!\App\Privilege::isPermitted($moduleName, 'RecordConventer')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		foreach ($moduleConverters as $converter) {
			$destinyModules = explode(',', $converter['destiny_module']);
			foreach ($destinyModules as $destinyModuleId) {
				if (!\App\Privilege::isPermitted(\App\Module::getModuleName($destinyModuleId), 'CreateView')) {
					\App\Log::warning("No permitted to action CreateView in module $destinyModuleId in view RecordConventer");
				}
			}
		}
	}

	/**
	 * Process function.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$records = Vtiger_Mass_Action::getRecordsListFromRequest($request);
		$recordsAmount = count($records);
		$viewer = $this->getViewer($request);
		$modulesWithoutPermission = [];
		$viewer->assign('CREATED_RECORDS', $recordsAmount);
		if (!$request->isEmpty('convertType')) {
			$fieldMerge = $request->isEmpty('fieldMerge') ? '' : $request->getByType('fieldMerge');
			$viewer->assign('SELECTED_CONVERT_TYPE', $request->getInteger('convertType'));
			$viewer->assign('SELECTED_MODULE', $request->getByType('destinyModule'));
			$viewer->assign('CREATED_RECORDS', \App\RecordConverter::countCreatedRecords($moduleName, $records, $fieldMerge));
		}
		$moduleConverters = \App\RecordConverter::getModuleConverters(\App\Module::getModuleId($moduleName), $request->get('inView'));
		foreach ($moduleConverters as $key => $converter) {
			$destinyModules = explode(',', $converter['destiny_module']);
			foreach ($destinyModules as $destinyModuleKey => $destinyModuleId) {
				$destinyModuleName = \App\Module::getModuleName($destinyModuleId);
				if (!\App\Privilege::isPermitted($destinyModuleName, 'CreateView')) {
					unset($destinyModules[$destinyModuleKey]);
					$modulesWithoutPermission[$destinyModuleName] = $destinyModuleName;
				}
			}
			if ($destinyModules) {
				$moduleConverters[$key]['destiny_module'] = implode(',', $destinyModules);
			} else {
				unset($moduleConverters[$key]);
			}
		}
		$viewer->assign('ALL_RECORDS', $recordsAmount);
		$viewer->assign('CONVERTERS', $moduleConverters);
		$viewer->assign('MODULE_WITHOUT_PERMISSIONS', $modulesWithoutPermission);
		$viewer->view('RecordConverter.tpl', $moduleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function initializeContent($request, $viewer)
	{
	}
}

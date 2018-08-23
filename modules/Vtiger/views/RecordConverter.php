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
		if (!\App\Privilege::isPermitted($request->getModule(), 'RecordConventer')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(\App\Request $request)
	{
		$moduleName = $request->getModule($request);
		$this->modalIcon = "modCT_{$moduleName} userIcon-{$moduleName}";
		parent::preProcessAjax($request);
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
			$converter = \App\RecordConverter::getInstanceById($request->getInteger('convertType'));
			$converter->init();
			$viewer->assign('SELECTED_CONVERT_TYPE', $request->getInteger('convertType'));
			$viewer->assign('CREATED_RECORDS', $converter->countCreatedRecords($records));
		}
		$moduleConverters = \App\RecordConverter::getModuleConverters($moduleName, $request->getByType('inView', 'Text'));
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
		$viewer->view('Modals/RecordConverter.tpl', $moduleName);
	}
}

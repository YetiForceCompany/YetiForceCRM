<?php

/**
 * Record converter view class.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class RecordConverter.
 */
class Vtiger_RecordConverter_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $modalSize = 'modal-md';
	/** {@inheritdoc} */
	public $successBtn = 'LBL_SAVE';
	/** {@inheritdoc} */
	public $pageTitle = 'LBL_RECORD_CONVERTER';
	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-exchange-alt';

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule(), 'RecordConventer')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		$moduleName = $request->getModule();
		$records = Vtiger_Mass_Action::getRecordsListFromRequest($request);
		$viewer = $this->getViewer($request);
		$fromView = $request->getByType('sourceView', \App\Purifier::STANDARD);
		if ('Detail' === $fromView) {
			$converters = \App\RecordConverter::getModuleConverters($moduleName, $request->getByType('sourceView', \App\Purifier::STANDARD), $records);
		} else {
			$converters = \App\RecordConverter::getModuleConverters($moduleName, $request->getByType('sourceView', \App\Purifier::STANDARD));
		}
		$convertId = $request->has('convertId') && isset($converters[$request->getInteger('convertId')]) ? $request->getInteger('convertId') : key($converters);
		if ($convertId) {
			$this->converter = \App\RecordConverter::getInstanceById($convertId);
		}
		if (!$converters) {
			$this->successBtn = '';
		}

		$viewer->assign('SELECTED_CONVERT_TYPE', $convertId);
		$viewer->assign('CONVERTERS', $converters);
		$viewer->assign('SOURCE_VIEW', $fromView);
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('Modals/RecordConverter.tpl', $request->getModule());
	}
}

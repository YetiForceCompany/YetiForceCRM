<?php

/**
 * Records list view class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class Vtiger_RecordState_View.
 */
class Vtiger_RecordState_View extends \App\Controller\Modal
{
	/**
	 * {@inheritdoc}
	 */
	public $modalSize = 'modal-md';

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		if ($request->isEmpty('record')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'));
		if (!$recordModel->isPermitted('OpenRecord') || !$recordModel->isPermitted('EditView') || $recordModel->checkLockFields()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$this->modalIcon = "modCT_{$moduleName} userIcon-{$moduleName}";
		$this->initializeContent($request);
		parent::preProcessAjax($request);
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('Modals/RecordState.tpl', $request->getModule());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPageTitle(\App\Request $request)
	{
		return \App\Language::translate('BTN_RECORD_OPEN', $request->getModule());
	}

	/**
	 * {@inheritdoc}
	 */
	public function initializeContent(\App\Request $request)
	{
		$lockFieldsModel = [];
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'));
		$lockFields = array_merge_recursive(
			$recordModel->getEntity()->getLockFields(),
			\App\Fields\Picklist::getCloseStates($recordModel->getModule()->getId())
		);
		foreach ($lockFields as $fieldName => $values) {
			if (in_array($recordModel->get($fieldName), $values)) {
				$lockFieldsModel[$fieldName] = $recordModel->getField($fieldName);
			}
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('LOCK_FIELDS', $lockFieldsModel);
		$viewer->assign('BTN_SUCCESS', $this->successBtn);
		$viewer->assign('BTN_DANGER', $this->dangerBtn);
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcessAjax(\App\Request $request)
	{
	}
}

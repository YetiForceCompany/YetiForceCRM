<?php

/**
 * Unlocking record.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class Vtiger_RecordUnlock_View.
 */
class Vtiger_RecordUnlock_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $modalSize = 'modal-md';

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record', true) || !Vtiger_Record_Model::getInstanceById($request->getInteger('record'))->isUnlockByFields()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		$moduleName = $request->getModule();
		$this->modalIcon = "modCT_{$moduleName} yfm-{$moduleName}";
		$this->initializeContent($request);
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('Modals/RecordUnlock.tpl', $request->getModule());
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		return \App\Language::translate('BTN_RECORD_OPEN', $request->getModule());
	}

	/** {@inheritdoc} */
	public function initializeContent(App\Request $request)
	{
		$lockFields = [];
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'));
		foreach ($recordModel->getUnlockFields() as $fieldName => $values) {
			$fieldModel = $recordModel->getField($fieldName);
			if ('picklist' === $fieldModel->getFieldDataType() || 'multipicklist' === $fieldModel->getFieldDataType()) {
				$fieldModel->picklistValues = array_diff_key($fieldModel->getPicklistValues(), array_flip($values));
			}
			$lockFields[$fieldName] = $fieldModel;
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('LOCK_FIELDS', $lockFields);
		$viewer->assign('BTN_SUCCESS', $this->successBtn);
		$viewer->assign('BTN_DANGER', $this->dangerBtn);
	}

	/** {@inheritdoc} */
	public function postProcessAjax(App\Request $request)
	{
	}
}

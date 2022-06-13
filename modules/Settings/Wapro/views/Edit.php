<?php

/**
 * Settings WAPRO ERP edit/create view file.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Settings WAPRO ERP edit/create view class.
 */
class Settings_Wapro_Edit_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	public $showFooter = false;

	/** {@inheritdoc} */
	public $successBtn = 'LBL_SAVE_AND_VERIFY';

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$record = !$request->isEmpty('record') ? $request->getInteger('record') : '';
		if ($record) {
			$recordModel = Settings_Wapro_Record_Model::getInstanceById($record);
		} else {
			$recordModel = Settings_Wapro_Record_Model::getCleanInstance();
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE_NAME', $request->getModule());
		$viewer->assign('BTN_SUCCESS', $this->successBtn);
		$viewer->assign('BTN_SUCCESS_ICON', $this->successBtnIcon);
		$viewer->assign('BTN_DANGER', $this->dangerBtn);
		$viewer->view('Edit/Modal.tpl', $request->getModule(false));
	}
}

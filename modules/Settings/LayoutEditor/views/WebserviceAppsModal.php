<?php

/**
 * Webservice apps config modal view file.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Webservice apps config modal view class.
 */
class Settings_LayoutEditor_WebserviceAppsModal_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	protected $pageTitle = 'BTN_WEBSERVICE_APP_EDIT';

	/** {@inheritdoc} */
	public $modalIcon = 'yfi yfi-full-editing-view';

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$fieldId = $request->getInteger('fieldId');
		$webserviceApp = $request->getInteger('wa');
		$fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($fieldId);
		$fieldInstance->loadWebserviceData($webserviceApp);
		$viewer = $this->getViewer($request);
		$viewer->assign('FIELD_ID', $fieldId);
		$viewer->assign('WEBSERVICE_APP', $webserviceApp);
		$viewer->assign('DATA', $fieldInstance->getWebserviceData($webserviceApp));
		$viewer->assign('FIELD_MODEL', $fieldInstance);
		$viewer->view('Modals/WebserviceAppsModal.tpl', $request->getModule(false));
	}
}

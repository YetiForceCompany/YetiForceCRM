<?php

/**
 * History access activity file.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

/**
 * History access activity class.
 */
class Settings_WebserviceUsers_HistoryAccessActivity_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	public $modalSize = 'modal-full';

	/** {@inheritdoc} */
	public $modalIcon = 'yfi yfi-login-history';

	/** {@inheritdoc} */
	public $pageTitle = 'LBL_HISTORY_ACTIVITY';

	/** {@inheritdoc}  */
	public $showFooter = false;

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$container = $request->getByType('typeApi', 'Alnum');
		$moduleInstance = Settings_Vtiger_Module_Model::getInstance('Settings:WebserviceUsers');
		$moduleInstance->typeApi = $container;
		$recordModel = Settings_WebserviceUsers_Record_Model::getInstanceById($request->getInteger('record', ''), $container);
		$viewer = $this->getViewer($request);
		$viewer->assign('TABLE_COLUMNS', $moduleInstance->getService()->columnsToShow);
		$viewer->assign('HISTORY_ACTIVITY_ENTRIES', $recordModel->getUserHistoryAccessActivity($container));
		$viewer->view('HistoryAccessActivity.tpl', $qualifiedModuleName);
	}
}

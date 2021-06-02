<?php

/**
 * History access activity file.
 *
 * @package Settings.View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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

	/** @var array Columns to show on the list session. */
	public static $columnsToShow = [
		'time' => 'FL_LOGIN_TIME',
		'status' => 'FL_STATUS',
		'agent' => 'LBL_USER_AGENT',
		'ip' => 'LBL_IP_ADDRESS',
	];

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$typeApi = $request->getByType('typeApi', 'Alnum');
		$recordModel = Settings_WebserviceUsers_Record_Model::getInstanceById($request->getInteger('record', ''), $typeApi);
		$viewer = $this->getViewer($request);
		$viewer->assign('TABLE_COLUMNS', static::$columnsToShow);
		$viewer->assign('HISTORY_ACTIVITY_ENTRIES', $recordModel->getUserHistoryAccessActivity($typeApi));
		$viewer->view('HistoryAccessActivity.tpl', $qualifiedModuleName);
	}
}

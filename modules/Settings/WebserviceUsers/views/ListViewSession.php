<?php

/**
 * List view session file.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

/**
 * List view session class.
 */
class Settings_WebserviceUsers_ListViewSession_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	public $modalSize = 'modal-full';

	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-users-cog';

	/** {@inheritdoc} */
	public $pageTitle = 'LBL_SESSION_RECORD';

	/** {@inheritdoc}  */
	public $showFooter = false;

	/** @var array Columns to show on the list session. */
	public static $columnsToShow = [
		'language' => 'FL_LANGUAGE',
		'created' => 'FL_LOGIN_TIME',
		'changed' => 'FL_DATETIME_LAST_QUERY',
		'params' => 'LBL_PARAMS',
		'last_method' => 'FL_LAST_METHOD',
		'agent' => 'LBL_USER_AGENT',
		'parent_id' => 'Accounts',
	];

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$container = $request->getByType('typeApi', 'Alnum');
		$recordModel = Settings_WebserviceUsers_Record_Model::getInstanceById($request->getInteger('record', ''), $container);
		$viewer = $this->getViewer($request);
		$viewer->assign('TABLE_COLUMNS', static::$columnsToShow);
		$viewer->assign('SESSION_HISTORY_ENTRIES', $recordModel->getUserSession($container));
		$viewer->view('ListViewSession.tpl', $qualifiedModuleName);
	}
}

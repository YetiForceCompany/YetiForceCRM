<?php

/**
 * List view session file.
 *
 * @package Settings
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

/**
 * List view session class.
 */
class Settings_WebserviceUsers_ListViewSession_View extends Settings_Vtiger_BasicModal_View
{
	/** {@inheritdoc} */
	public function getSize(App\Request $request)
	{
		return 'modal-full';
	}

	/** @var array Columns to show on the list session. */
	public static $columnsToShow = [
		'user_id' => 'LBL_USER',
		'language' => 'FL_LANGUAGE',
		'created' => 'FL_LOGIN_TIME',
		'changed' => 'LBL_CHANGED',
		'params' => 'LBL_PARAMS',
	];

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		parent::preProcess($request);
		$qualifiedModuleName = $request->getModule(false);
		$recordModel = Settings_WebserviceUsers_Record_Model::getInstanceById($request->getInteger('record', ''), $request->getByType('typeApi', 'Alnum'));
		$viewer = $this->getViewer($request);
		$viewer->assign('TABLE_COLUMNS', static::$columnsToShow);
		$viewer->assign('SESSION_HISTORY_ENTRIES', $recordModel->getUserSession());
		$viewer->view('ListViewSession.tpl', $qualifiedModuleName);
		parent::postProcess($request);
	}
}

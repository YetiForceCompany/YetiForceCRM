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
		'id' => 'LBL_ID_SESSION',
		'user_id' => 'LBL_USER',
		'language' => 'LBL_LANGUAGE',
		'created' => 'LBL_CREATED',
		'changed' => 'LBL_CHANGED',
		'params' => 'LBL_PARAMS',
	];

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		parent::preProcess($request);
		$qualifiedModuleName = $request->getModule(false);
		$recordId = $request->getInteger('record', '');
		$type = $request->getByType('typeApi', 'Alnum');
		if (!empty($recordId)) {
			$recordModel = Settings_WebserviceUsers_Record_Model::getInstanceById($recordId, $type);
		} else {
			$recordModel = Settings_WebserviceUsers_Record_Model::getCleanInstance($type);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordId);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('TYPE_API', $type);
		$viewer->assign('MODULE_MODEL', $recordModel->getModule());
		$viewer->assign('TABLE_COLUMNS', static::$columnsToShow);
		$viewer->assign('SESSION_HISTORY_ENTRIES', $recordModel->getUserSession());
		$viewer->view('ListViewSession.tpl', $qualifiedModuleName);
		parent::postProcess($request);
	}
}

<?php

/**
 * List view session file.
 *
 * @package Settings.View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

/**
 * List view session class.
 */
class Settings_WebserviceUsers_ListViewSession_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	public $modalSize = 'modal-full';

	/** {@inheritdoc}  */
	public $showFooter = false;

	/** @var array Columns to show on the list session. */
	public static $columnsToShow = [
		'language' => 'FL_LANGUAGE',
		'created' => 'FL_LOGIN_TIME',
		'changed' => 'LBL_CHANGED',
		'params' => 'LBL_PARAMS',
		'last_method' => 'FL_LAST_METHOD'
	];

	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(App\Request $request)
	{
		$this->modalIcon = 'fas fa-users-cog';
		$this->pageTitle = \App\Language::translate('LBL_SESSION_RECORD', $this->qualifiedModuleName);
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$recordModel = Settings_WebserviceUsers_Record_Model::getInstanceById($request->getInteger('record', ''), $request->getByType('typeApi', 'Alnum'));
		$viewer = $this->getViewer($request);
		$viewer->assign('TABLE_COLUMNS', static::$columnsToShow);
		$viewer->assign('SESSION_HISTORY_ENTRIES', $recordModel->getUserSession());
		$viewer->view('ListViewSession.tpl', $qualifiedModuleName);
	}
}

<?php
/**
 * Settings groups delete view file.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Settings groups delete view class.
 */
class Settings_Groups_Delete_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_DELETE_GROUP';

	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-trash-alt';

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODEL', Settings_Groups_Record_Model::getInstance($request->getInteger('record')));
		$viewer->assign('ALL_USERS', Users_Record_Model::getAll());
		$viewer->assign('MODULE_NAME', $request->getModule());
		$viewer->assign('ALL_GROUPS', Settings_Groups_Record_Model::getAll());
		$viewer->view('Delete.tpl', $request->getModule(false));
	}
}

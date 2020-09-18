<?php
/**
 * Detail modal view file for Mail RBL module.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Detail modal view class for Mail RBL module.
 */
class Settings_MailRbl_DetailModal_View extends \App\Controller\ModalSettings
{
	/**
	 * {@inheritdoc}
	 */
	protected $pageTitle = 'LBL_MAIL_MESSAGE_DETAILS';
	/**
	 * {@inheritdoc}
	 */
	public $modalIcon = 'fas fa-search-plus';
	/**
	 * {@inheritdoc}
	 */
	public $modalSize = 'modal-full';

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', Settings_MailRbl_Record_Model::getRequestById($request->getInteger('id')));
		$viewer->view('DetailModal.tpl', $request->getModule(false));
	}
}

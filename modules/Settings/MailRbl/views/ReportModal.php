<?php
/**
 * Report modal view file for Mail RBL module.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Report modal view class for Mail RBL module.
 */
class Settings_MailRbl_ReportModal_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	public $pageTitle = 'BTN_SEND_REPORT';
	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-paper-plane';
	/** {@inheritdoc} */
	public $modalSize = 'modal-blg';
	/** {@inheritdoc} */
	public $successBtn = 'BTN_SEND_REPORT';
	/** {@inheritdoc} */
	public $successBtnIcon = 'fas fa-paper-plane';
	/** {@inheritdoc} */
	public $showHeader = false;

	/** {@inheritdoc} */
	public function preProcessAjax(\App\Request $request)
	{
		parent::preProcessAjax($request);
		$viewer = $this->getViewer($request);
		$viewer->view('ReportModalHeader.tpl', $request->getModule(false));
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$recordModel = \App\Mail\Rbl::getRequestById($request->getInteger('id'));
		$recordModel->parse();
		$type = $recordModel->get('type') ? 'White' : 'Black';
		$viewer->assign('IP', $recordModel->getSender()['ip'] ?? '-');
		$viewer->assign('BODY', $recordModel->get('body'));
		$viewer->assign('HEADER', $recordModel->get('header'));
		$viewer->assign('TYPE', $type);
		$viewer->assign('MODAL_DESC', 'LBL_REPORT_MODAL_DESC_' . strtoupper($type));
		$viewer->assign('TYPE_NAME', $recordModel->get('type') ? 'LBL_REPORT_WHITE' : 'LBL_REPORT_BLACK');
		$viewer->assign('RECORD', $request->getInteger('id'));
		$viewer->assign('CATEGORIES', \App\Mail\Rbl::LIST_CATEGORIES[$type]);
		$viewer->view('ReportModal.tpl', $request->getModule(false));
	}
}

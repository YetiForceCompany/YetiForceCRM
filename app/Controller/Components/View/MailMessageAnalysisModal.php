<?php
/**
 * Mail message analysis modal view file.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller\Components\View;

/**
 * Mail message analysis modal view class.
 */
class MailMessageAnalysisModal extends \App\Controller\Modal
{
	/**
	 * MailRbl record model.
	 *
	 * @var \App\Mail\Rbl
	 */
	protected $recordModel;
	/**
	 * {@inheritdoc}
	 */
	public $modalIcon = 'fas fa-lock';
	/**
	 * {@inheritdoc}
	 */
	public $modalSize = 'modal-full';
	/**
	 * {@inheritdoc}
	 */
	public $successBtn = false;
	/**
	 * {@inheritdoc}
	 */
	public $dangerBtn = 'LBL_CLOSE';

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		if ($request->has('record')) {
			if (!\App\Security\AdminAccess::isPermitted('MailRbl')) {
				throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
			}
		} elseif ($request->has('content')) {
			if (!\Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission('OSSMail')) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
		} else {
			throw new \App\Exceptions\AppException('ERR_NO_CONTENT', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPageTitle(\App\Request $request)
	{
		return \App\Language::translate('LBL_MAIL_MESSAGE_DETAILS', 'Settings:MailRbl');
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(\App\Request $request)
	{
		if ($request->has('record')) {
			$this->recordModel = \App\Mail\Rbl::getRequestById($request->getInteger('record'));
		} elseif ($request->has('header') && $request->has('body')) {
			$this->recordModel = \App\Mail\Rbl::getInstance([]);
			$this->recordModel->set('header', $request->getRaw('header'));
			$this->recordModel->set('body', $request->getRaw('body'));
		} else {
			$this->recordModel = \App\Mail\Rbl::getInstance([]);
			[$headers] = explode("\r\n\r\n", str_replace(["\r\n", "\r", "\n"], ["\n", "\n", "\r\n"], $request->getRaw('content')), 2);
			$this->recordModel->set('rawBody', $request->getRaw('content'));
			$this->recordModel->set('header', $headers);
		}
		$this->recordModel->parse();
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $this->recordModel);
		$viewer->assign('SENDER', $this->recordModel->getSender());
		$viewer->assign('VERIFY_SENDER', $this->recordModel->verifySender());
		$viewer->assign('VERIFY_SPF', $this->recordModel->verifySpf());
		$viewer->assign('VERIFY_DKIM', $this->recordModel->verifyDkim());
		$viewer->assign('VERIFY_DMARC', $this->recordModel->verifyDmarc());
		$viewer->assign('LANG_MODULE_NAME', 'Settings:MailRbl');
		parent::preProcessAjax($request);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function preProcessTplName(\App\Request $request)
	{
		return 'MailMessageAnalysisModalHeader.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('CARD_MAP', [
			'from' => [
				'Name' => ['icon' => 'fas fa-upload', 'label' => 'LBL_SERVER_NAME_FROM_DESC'],
				'Hostname' => ['icon' => 'fas fa-server', 'label' => 'LBL_SERVER_HOST_NAME_FROM'],
				'IP' => ['icon' => 'fas fa-network-wired', 'label' => 'LBL_SERVER_IP_FROM'],
			],
			'by' => [
				'Name' => ['icon' => 'fas fa-download', 'label' => 'LBL_SERVER_NAME_BY_DESC'],
				'Hostname' => ['icon' => 'fas fa-server', 'label' => 'LBL_SERVER_HOST_NAME_BY'],
				'IP' => ['icon' => 'fas fa-network-wired', 'label' => 'LBL_SERVER_IP_BY'],
			],
			'extra' => [
				'Comments' => ['icon' => 'far fa-comment-alt', 'label' => 'LBL_SERVER_COMMENTS'],
				'With' => ['icon' => 'fab fa-expeditedssl', 'label' => 'LBL_PROTOCOL'],
			]
		]);
		$viewer->view('MailMessageAnalysisModal.tpl', $request->getModule(false));
	}
}

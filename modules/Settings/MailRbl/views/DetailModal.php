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
	 * MailRbl record model.
	 *
	 * @var \App\Mail\Rbl
	 */
	private $recordModel;
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
	public $successBtn = false;
	/**
	 * {@inheritdoc}
	 */
	public $dangerBtn = 'LBL_CLOSE';

	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->recordModel = \App\Mail\Rbl::getRequestById(\App\Request::_getInteger('record'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPageTitle(App\Request $request)
	{
		return \App\Language::translate('LBL_MAIL_MESSAGE_DETAILS', $request->getModule(false));
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $this->recordModel);
		$viewer->assign('SENDER', $this->recordModel->getSender());
		$viewer->assign('CHECK_SPF', $this->recordModel->checkSpf());
		$viewer->assign('CHECK_SENDER', $this->recordModel->checkSender());
		parent::preProcessAjax($request);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function preProcessTplName(App\Request $request)
	{
		return 'DetailHeaderModal.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
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
		$viewer->view('DetailModal.tpl', $request->getModule(false));
	}
}

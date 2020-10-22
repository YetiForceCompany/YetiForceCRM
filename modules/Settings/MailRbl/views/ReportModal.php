<?php
/**
 * Report modal view file for Mail RBL module.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	public $modalSize = 'modal-full';
	/** {@inheritdoc} */
	public $successBtn = 'BTN_SEND_REPORT';
	/** {@inheritdoc} */
	public $successBtnIcon = 'fas fa-paper-plane';

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$recordModel = \App\Mail\Rbl::getRequestById($request->getInteger('id'));
		$recordModel->parse();
		$type = $recordModel->get('type') ? 'White' : 'Black';
		$viewer->assign('SENDER', $recordModel->getSender());
		$viewer->assign('BODY', $recordModel->get('body'));
		$viewer->assign('HEADER', $recordModel->get('header'));
		$viewer->assign('TYPE', $type);
		$viewer->assign('MODAL_DESC', 'LBL_REPORT_MODAL_DESC_' . strtoupper($type));
		$viewer->assign('TYPE_NAME', $recordModel->get('type') ? 'LBL_REPORT_WHITE' : 'LBL_REPORT_BLACK');
		$viewer->assign('RECORD', $request->getInteger('id'));
		$viewer->assign('CATEGORIES', [
			'[SPAM] Single unwanted message' => 'LBL_SPAM_SINGLE_UNWANTED_MESSAGE',
			'[SPAM] Mass unwanted message' => 'LBL_SPAM_MASS_UNWANTED_MESSAGE',
			'[SPAM] Sending an unsolicited message repeatedly' => 'LBL_SPAM_SENDING_UNSOLICITED_MESSAGE_REPEATEDLY',
			'[Fraud] Money scam' => 'LBL_FRAUD_MONEY_SCAM',
			'[Fraud] Phishing' => 'LBL_FRAUD_PHISHING',
			'[Fraud] An attempt to persuade people to buy a product or service' => 'LBL_FRAUD_ATTEMPT_TO_PERSUADE_PEOPLE_TO_BUY',
			'[Security] An attempt to impersonate another person' => 'LBL_SECURITY_ATTEMPT_TO_IMPERSONATE_ANOTHER_PERSON',
			'[Security] An attempt to persuade the recipient to open a resource from outside the organization' => 'LBL_SECURITY_ATTEMPT_TO_PERSUADE_FROM_ORGANIZATION',
			'[Security] An attempt to persuade the recipient to open a resource inside the organization' => 'LBL_SECURITY_ATTEMPT_TO_PERSUADE_INSIDE_ORGANIZATION',
			'[Security] Infrastructure and application scanning' => 'LBL_SECURITY_INFRASTRUCTURE_AND_APPLICATION_SCANNING',
			'[Security] Attack on infrastructure or application' => 'LBL_SECURITY_ATTACK_INFRASTRUCTURE_OR_APPLICATION',
			'[Security] Overloading infrastructure or application' => 'LBL_SECURITY_OVERLOADING_INFRASTRUCTURE_OR_APPLICATION',
			'[Other] The essage contains inappropriate words' => 'LBL_OTHER_ESSAGE_CONTAINS_INAPPROPRIATE_WORDS',
			'[Other] The message contains inappropriate materials' => 'LBL_OTHER_MESSAGE_CONTAINS_INAPPROPRIATE_MATERIALS',
			'[Other] Malicious message' => 'LBL_OTHER_MALICIOUS_MESSAGE',
		]);
		$viewer->view('ReportModal.tpl', $request->getModule(false));
	}
}

<?php
/**
 * Import action file.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Import action class.
 */
class MailIntegration_Import_Action extends \App\Controller\Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!\App\Privilege::isPermitted('OSSMailView', 'CreateView')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$mail = App\Mail\Message::getScannerByEngine('Outlook');
		$mail->set('subject', $request->isEmpty('mailSubject') ? '-' : \App\TextParser::textTruncate($request->getByType('mailSubject', 'Text'), 65535, false));
		$mail->set('from_email', $request->getByType('mailFrom', 'Email'));
		$mail->set('to_email', $request->getArray('mailTo', 'Email'));
		$mail->set('cc_email', $request->getArray('mailCc', 'Email'));
		$mail->set('date', $request->getByType('mailDateTimeCreated', 'DateTimeInIsoFormat'));
		$mail->set('message_id', $request->getByType('mailMessageId', 'MailId'));
		$mail->set('body', $request->getForHtml('mailBody'));
		$mail->process();

		$response = new Vtiger_Response();
		$response->setResult($mail->getMailCrmId());
		$response->emit();
	}
}

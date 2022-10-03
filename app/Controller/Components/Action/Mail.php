<?php
/**
 * Mail action file.
 *
 * @package   Controller
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller\Components\Action;

/**
 * Mail action class.
 */
class Mail extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('sendMail');
	}

	/** {@inheritdoc} */
	public function checkPermission(\App\Request $request)
	{
		return true;
	}

	/**
	 * Send mail by user composer.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function sendMail(\App\Request $request): void
	{
		$composer = \App\Mail::getMailComposer();
		$response = new \Vtiger_Response();
		$response->setResult(array_merge(['composer' => $composer], \App\Mail::getComposerInstance($composer)->sendMail($request)));
		$response->emit();
	}
}

<?php
/**
 * Send modal view file for Mail RBL module.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
/**
 * Send modal view class for Mail RBL module.
 */
class Settings_MailRbl_SendModal_View extends \App\Controller\ModalSettings
{

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('SendModal.tpl', $request->getModule(false));
	}
}

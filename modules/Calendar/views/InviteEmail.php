<?php

/**
 * Modal window class - Add participant by email.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Calendar_InviteEmail_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $successBtn = 'LBL_ADD';

	/** {@inheritdoc} */
	public $modalSize = 'modal-dialog-centered';

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule(), 'EditView')) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('InviteEmail.tpl', $request->getModule());
	}
}

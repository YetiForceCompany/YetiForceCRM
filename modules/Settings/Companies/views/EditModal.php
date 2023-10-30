<?php

/**
 * YetiForce registration modal view class file.
 *
 * @package   Modules
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Online registration modal view class.
 */
class Settings_Companies_EditModal_View extends \App\Controller\ModalSettings
{
	/**
	 * Registration modal event parameters.
	 */
	public const MODAL_EVENT = [
		'name' => 'YetiForceRegistration',
		'type' => 'modal',
		'execution' => 'constant',
		'url' => [
			'url' => 'index.php?parent=Settings&module=Companies&view=EditModal',
			'type' => 'GET',
			'data' => ['isForced' => true],
		],
	];
	/** {@inheritdoc} */
	public $modalSize = 'modal-full';

	/** @var string The name of the send button. */
	public $successBtn = 'LBL_SEND';

	/** {@inheritdoc} */
	public $lockExit = true;

	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-globe';

	/** {@inheritdoc} */
	public $pageTitle = 'LBL_YETIFORCE_REGISTRATION';

	/** {@inheritdoc} */
	public $showFooter = false;

	/** {@inheritdoc} */
	protected bool $blurBackground = true;

	/** {@inheritdoc} */
	protected bool $draggable = false;

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request): void
	{
		$viewer = $this->getViewer($request);

		$company = \App\Company::getCompany();
		if (!$company) {
			$company = new Settings_Companies_Record_Model();
		}
		$viewer->assign('RECORD_MODEL', $company);
		$viewer->assign('RECORD_ID', $company['id']);
		$viewer->assign('IS_MODAL', true);
		if (\App\User::getCurrentUserModel()->isAdmin()) {
			$viewer->assign('EMAIL_URL', Settings_Companies_EmailVerificationModal_View::MODAL_EVENT['url']);
		}
		$viewer->view('EditModal.tpl', $request->getModule(false));
	}
}

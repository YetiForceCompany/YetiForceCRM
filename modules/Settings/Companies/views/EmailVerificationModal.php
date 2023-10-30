<?php
/**
 * Email verification modal view class file.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Klaudia Łozowska <k.lozowska@yetiforce.com>
 */

/**
 * Email verification modal view class.
 */
class Settings_Companies_EmailVerificationModal_View extends \App\Controller\ModalSettings
{
	/**
	 * Registration modal event parameters.
	 */
	public const MODAL_EVENT = [
		'name' => 'EmailVerification',
		'priority' => 10,
		'type' => 'modal',
		'execution' => 'constant',
		'url' => 'index.php?parent=Settings&module=Companies&view=EmailVerificationModal',
	];
	/** {@inheritdoc} */
	public $modalSize = 'modal-xl';

	/** {@inheritdoc} */
	public $pageTitle = 'LBL_EMAIL_VERIFICATION_MODAL_HEADER';

	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-globe';

	/** {@inheritdoc} */
	public $successBtn = 'LBL_SEND';

	/** {@inheritdoc} */
	public $lockExit = true;

	/** {@inheritdoc} */
	public $successBtnIcon = 'fas fa-paper-plane';

	/** {@inheritdoc} */
	public $showFooter = false;

	/** {@inheritdoc} */
	protected bool $blurBackground = true;

	/** {@inheritdoc} */
	protected bool $draggable = false;

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		$this->lockExit = !App\YetiForce\Register::isPreRegistered();
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request): void
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $request->getModule());
		$viewer->assign('EMAIL', \App\Company::getCompany()['email'] ?? '');

		$viewer->view('EmailVerificationModal.tpl', $request->getModule(false));
	}

	/** {@inheritdoc} */
	public function getModalScripts(App\Request $request): array
	{
		return array_merge(parent::getModalScripts($request), $this->checkAndConvertJsScripts([
			'modules.Settings.Companies.resources.EmailVerificationModal',
		]));
	}
}

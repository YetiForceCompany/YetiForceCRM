<?php
/**
 * Deny access to non-administrators file.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Klaudia Łozowska <k.lozowska@yetiforce.com>
 */

/**
 * Deny access to non-administrators class.
 */
class Users_NoAccessModal_View extends \App\Controller\Modal
{
	/**
	 * Event parameters.
	 */
	public const MODAL_EVENT = [
		'name' => 'NoAccessModal',
		'priority' => 10,
		'type' => 'modal',
		'execution' => 'constant',
		'url' => 'index.php?module=Users&view=NoAccessModal',
	];
	/** {@inheritdoc} */
	public $modalSize = 'modal-lg';

	/** {@inheritdoc} */
	public $pageTitle = 'LBL_NO_ACCESS_TITLE';

	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-radiation-alt';

	/** @inheritdoc  */
	public $lockExit = true;

	/** @inheritdoc  */
	public $showFooter = false;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request): void
	{
		if (App\YetiForce\Register::isPreRegistered() || \App\User::getCurrentUserModel()->isAdmin()) {
			throw new \App\Exceptions\AppException('LBL_PERMISSION_DENIED');
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request): void
	{
		$viewer = $this->getViewer($request);
		$viewer->view('Modals/NoAccessModal.tpl', $request->getModule(false));
	}
}

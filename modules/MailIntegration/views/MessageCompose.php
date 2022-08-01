<?php
/**
 * Message compose view class.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 *
 * @see https://support.microsoft.com/en-us/help/4482725/custom-add-ins-may-not-display-all-fields-correctly-in-outlook
 * @see https://docs.microsoft.com/en-us/office/dev/add-ins/concepts/browsers-used-by-office-web-add-ins
 */

/**
 * Message compose view class.
 */
class MailIntegration_MessageCompose_View extends \App\Controller\View\Base
{
	/**
	 * Error message.
	 *
	 * @var string
	 */
	protected $error;

	/** {@inheritdoc} */
	public function loginRequired()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if ((explode('-', $request->getByType('query', 'AlnumExtended'))[0] ?? '') !== substr(\App\YetiForce\Register::getInstanceKey(), 0, 30)) {
			$this->error = 'LBL_PERMISSION_DENIED';
			\App\Log::error("Incorrect integration key: {$request->getByType('query', 'AlnumExtended')}.");
			new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if ('outlook' !== $request->getByType('source')) {
			$this->error = 'LBL_PERMISSION_DENIED';
			new \App\Exceptions\NoPermitted('ERR_PAID_FUNCTIONALITY||YetiForceOutlook', 406);
		}
		\CsrfMagic\Csrf::$frameBreaker = \Config\Security::$csrfFrameBreaker = false;
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		if (!\App\User::getCurrentUserId()) {
			$viewer = $this->getViewer($request);
			$viewer->view('LoginIframe.tpl', $moduleName);
		} elseif (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($moduleName)) {
			$this->error = 'LBL_PERMISSION_DENIED';
			$id = \App\User::getCurrentUserRealId();
			\App\Log::error("No access to the module: {$id}.");
			new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (isset($this->error)) {
			$viewer = $this->getViewer($request);
			$viewer->assign('MESSAGE', \App\Language::translate($this->error));
			$viewer->assign('MESSAGE_EXPANDED', false);
			$viewer->assign('HEADER_MESSAGE', \App\Language::translate('LBL_ERROR'));
			$viewer->view('Exceptions/ExceptionError.tpl', 'Vtiger');
		} else {
			$viewer = $this->getViewer($request);
			$viewer->view('MessageCompose.tpl', $moduleName);
		}
	}

	/** {@inheritdoc} */
	public function getHeaderScripts(App\Request $request)
	{
		return array_merge(parent::getHeaderScripts($request), $this->checkAndConvertJsScripts([
			'https://appsforoffice.microsoft.com/lib/1.1/hosted/office.js',
		]));
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			"modules.{$request->getModule()}.resources.{$request->getByType('source')}{$request->getByType('view')}",
		]));
	}
}

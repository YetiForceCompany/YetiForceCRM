<?php
/**
 * Message detail view class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 *
 * @see https://support.microsoft.com/en-us/help/4482725/custom-add-ins-may-not-display-all-fields-correctly-in-outlook
 * @see https://docs.microsoft.com/en-us/office/dev/add-ins/concepts/browsers-used-by-office-web-add-ins
 */

/**
 * Message detail view class.
 */
class MailIntegration_MessageDetail_View extends \App\Controller\View\Base
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		if ((explode('-', $request->getByType('query', 'AlnumExtended'))[0] ?? '') !== substr(\App\YetiForce\Register::getInstanceKey(), 0, 30)) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		\CsrfMagic\Csrf::$frameBreaker = false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function loginRequired()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		if (!\App\User::getCurrentUserId()) {
			$viewer = $this->getViewer($request);
			$viewer->view('LoginIframe.tpl', $moduleName);
		} elseif (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($moduleName)) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderScripts(App\Request $request)
	{
		return array_merge(parent::getHeaderScripts($request), $this->checkAndConvertJsScripts([
			'https://appsforoffice.microsoft.com/lib/1.1/hosted/office.js',
		]));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(App\Request $request)
	{
		$jsFileNames = [
			"modules.{$request->getModule()}.resources.{$request->getByType('source')}{$request->getByType('view')}"
		];
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
	}
}

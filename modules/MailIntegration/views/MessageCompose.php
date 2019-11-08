<?php
/**
 * Message compose view class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Message compose view class.
 */
class MailIntegration_MessageCompose_View extends \App\Controller\View\Base
{
	public function checkPermission(App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->view('MessageCompose.tpl', $moduleName);
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
			"modules.{$request->getModule()}.resources.{$request->getByType('source')}{$request->getByType('view')}",
		];
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
	}
}

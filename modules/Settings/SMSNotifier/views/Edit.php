<?php
/**
 * Edit View Class.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Edit View Class.
 */
class Settings_SMSNotifier_Edit_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_SMSPROVIDER_EDIT';
	/** {@inheritdoc} */
	public $modalIcon = 'yfi yfi-full-editing-view';
	/** {@inheritdoc} */
	public $modalSize = 'modal-md';

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		if ($request->isEmpty('record')) {
			$title = \App\Language::translate('LBL_SMSPROVIDER_CREATE', $moduleName);
		} else {
			$title = \App\Language::translate($this->pageTitle, $moduleName);
		}

		return $title;
	}

	/**
	 * Check Permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		if ($request->isEmpty('provider', true) || !\App\Integrations\SMSProvider::getProviderByName($request->getByType('provider', \App\Purifier::ALNUM)) || (!$request->isEmpty('record') && !\App\Integrations\SMSProvider::getById($request->getInteger('record')))) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$provider = $request->getByType('provider', \App\Purifier::ALNUM);
		if (!$request->isEmpty('record')) {
			$recordModel = Settings_SMSNotifier_Record_Model::getInstanceById($request->getInteger('record'));
		} else {
			$recordModel = Settings_SMSNotifier_Record_Model::getCleanInstance($provider);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('MODULE_MODEL', $recordModel->getModule());
		$viewer->assign('PROVIDER', \App\Integrations\SMSProvider::getProviderByName($provider));
		$viewer->view($this->getTemplateName(), $qualifiedModuleName);
	}

	/**
	 * Template name.
	 *
	 * @return string
	 */
	public function getTemplateName(): string
	{
		return 'Edit.tpl';
	}

	/** {@inheritdoc} */
	public function getModalScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewName = $request->getByType('view', \App\Purifier::STANDARD);
		return $this->checkAndConvertJsScripts([
			'modules.Settings.Vtiger.resources.Edit',
			"modules.Settings.{$moduleName}.resources.Edit",
			"modules.Settings.{$moduleName}.resources.$viewName",
		]);
	}
}

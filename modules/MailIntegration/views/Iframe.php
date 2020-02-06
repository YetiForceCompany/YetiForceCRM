<?php
/**
 * Detail view file.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Detail view class.
 */
class MailIntegration_Iframe_View extends \App\Controller\Modal
{
	/**
	 * {@inheritdoc}
	 */
	public $showHeader = false;
	/**
	 * {@inheritdoc}
	 */
	public $showFooter = false;

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		if (Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule())) {
			$mail = App\Mail\Message::getScannerByEngine($request->getByType('source'));
			$mail->initFromRequest($request);
			if ($mailId = $mail->getMailCrmId()) {
				$viewer->assign('MODULES', $this->getModules());
				$relations = $mail->getRelatedRecords();
			} else {
				$relations = $mail->findRelatedRecords();
			}
			$viewer->assign('RELATIONS', $relations);
			$viewer->assign('MAIL_ID', $mailId);
			$viewer->assign('URL', App\Config::main('site_URL'));
			$viewer->assign('MODAL_SCRIPTS', $this->getModalScripts($request));
		}
		$viewer->view('Iframe/Container.tpl', $moduleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModalScripts(App\Request $request)
	{
		return $this->checkAndConvertJsScripts([
			"modules.{$request->getModule()}.resources.{$request->getByType('view', 2)}",
			'modules.Vtiger.resources.Edit',
			'~layouts/resources/Field.js',
			'~layouts/resources/validator/BaseValidator.js',
			'~layouts/resources/validator/FieldValidator.js'
		]);
	}

	/**
	 * Get modules.
	 *
	 * @return string[]
	 */
	public function getModules(): array
	{
		$modules = [];
		$quickCreate = App\Config::module('MailIntegration', 'modulesListQuickCreate', []);
		foreach (App\Relation::getAll(null, ['related_tabid' => App\Module::getModuleId('OSSMailView'), 'presence' => 0, 'related_module_presence' => 0]) as $relations) {
			foreach ($relations as $relation) {
				$moduleName = App\Module::getModuleName($relation['tabid']);
				if (App\Privilege::isPermitted($moduleName)) {
					$modules[$moduleName] = $quickCreate[$moduleName] ?? Vtiger_Module_Model::getInstance($moduleName)->isQuickCreateSupported();
				}
			}
		}
		if ($order = App\Config::module('MailIntegration', 'modulesListOrder', [])) {
			$ordered = [];
			foreach ($order as $orderVal) {
				if (isset($modules[$orderVal])) {
					$ordered[$orderVal] = $modules[$orderVal];
					unset($modules[$orderVal]);
				}
			}
			$modules = array_merge($ordered, $modules);
		}
		return $modules;
	}
}

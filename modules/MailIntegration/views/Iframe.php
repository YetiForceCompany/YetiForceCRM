<?php
/**
 * Detail view file.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Detail view class.
 */
class MailIntegration_Iframe_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $showHeader = false;
	/** {@inheritdoc} */
	public $showFooter = false;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		return true;
	}

	/** {@inheritdoc} */
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

	/** {@inheritdoc} */
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
		foreach (\App\Relation::getByModule('OSSMailView', true) as $relation) {
			if (0 === $relation['presence'] && 'getRecordToMails' === $relation['name'] && App\Privilege::isPermitted($relation['related_modulename'])) {
				$quickCreateSupported = Vtiger_Module_Model::getInstance($relation['related_modulename'])->isQuickCreateSupported();
				$modules[$relation['related_modulename']] = $quickCreateSupported && (!$quickCreate || \in_array($relation['related_modulename'], $quickCreate));
			}
		}

		return $modules;
	}
}

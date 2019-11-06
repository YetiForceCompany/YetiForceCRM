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
class MailIntegration_Detail_View extends \App\Controller\Modal
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
	 * Are there relations with the record.
	 *
	 * @var bool
	 */
	private $areRelations = false;

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
			$this->getModules($request);
			$mail = App\Mail\Message::getScannerByEngine($request->getByType('source'));
			$mail->initFromRequest($request);
			if ($mailId = $mail->getMailCrmId()) {
				$viewer->assign('MODULES', $this->getModules());
				$relations = $mail->getRelatedRecords();
			} else {
				$relations = $this->getRelatedRecords($request);
			}
			$this->areRelations = !empty($relations);
			$viewer->assign('RELATIONS', $relations);
			$viewer->assign('MAIL_ID', $mailId);
			$viewer->assign('URL', App\Config::main('site_URL'));
			$viewer->assign('MODAL_SCRIPTS', $this->getModalScripts($request));
		}
		$viewer->view('Detail/Panel.tpl', $moduleName);
	}

	/**
	 * Set HTTP Headers.
	 */
	public function setHeaders()
	{
	}

	/**
	 * Set CSP Headers.
	 */
	public function setCspHeaders()
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModalScripts(App\Request $request)
	{
		$viewName = $request->getByType('view', 2);
		$jsFileNames = [
			"modules.{$request->getModule()}.resources.$viewName",
		];
		if (!$this->areRelations) {
			$jsFileNames = array_merge($jsFileNames, [
				'modules.Vtiger.resources.Edit',
				'~layouts/resources/Field.js',
				'~layouts/resources/validator/BaseValidator.js',
				'~layouts/resources/validator/FieldValidator.js'
			]);
		}
		return $this->checkAndConvertJsScripts($jsFileNames);
	}

	/**
	 * Get related records if the mail does not exist.
	 *
	 * @param App\Request $request
	 *
	 * @return array
	 */
	public function getRelatedRecords(App\Request $request): array
	{
		$records = array_merge(array_flatten(App\Mail\RecordFinder::findByEmail([$request->getByType('mailFrom', 'Email')])), App\Mail\RecordFinder::findBySubject($request->getByType('mailSubject', 'Text'), ['HelpDesk']));
		foreach ($records as &$record) {
			$record = [
				'id' => $record,
				'module' => \App\Record::getType($record),
				'label' => \App\Record::getLabel($record),
			];
		}
		return $records;
	}

	/**
	 * Get modules.
	 *
	 * @return string[]
	 */
	public function getModules(): array
	{
		$modules = [];
		foreach (\App\ModuleHierarchy::getModulesByLevel() as $value) {
			$modules = array_merge($modules, array_keys($value));
		}
		return $modules;
	}
}

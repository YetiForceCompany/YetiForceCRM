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
			if ($mailId = $this->getMailId($request)) {
				$viewer->assign('MAIL_ID', $mailId);
				$viewer->assign('MODULES', $this->getModules());
				$viewer->assign('RELATIONS', $this->getRelatedDetails($request));
			} else {
				$viewer->assign('MAIL_ID', false);
				$viewer->assign('RELATIONS', $this->getRelatedDetails($request));
			}
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
		return $this->checkAndConvertJsScripts([
			"modules.{$request->getModule()}.resources.$viewName"
		]);
	}

	/**
	 * Get related details if the mail does not exist.
	 *
	 * @param App\Request $request
	 *
	 * @return array
	 */
	public function getRelatedDetails(App\Request $request): array
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
	 * Get mail crm id.
	 *
	 * @param App\Request $request
	 *
	 * @return bool|int
	 */
	public function getMailId(App\Request $request)
	{
		$mail = new OSSMail_Mail_Model();
		$mail->set('from_email', $request->getByType('mailFrom', 'Email'));
		$mail->set('subject', $request->getByType('mailSubject', 'Text'));
		$mail->set('date', $request->getByType('mailDateTimeCreated', 'DateTimeInIsoFormat'));
		$mail->set('message_id', $request->getByType('mailMessageId', 'MailId'));
		$query = (new \App\Db\Query())->select(['ossmailviewid'])->from('vtiger_ossmailview')->where(['cid' => $mail->getUniqueId()])->limit(1);
		return $query->scalar();
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

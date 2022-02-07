<?php
/**
 * Import action file.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Import action class.
 */
class MailIntegration_Mail_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('deleteRelation');
		$this->exposeMethod('addRelation');
		$this->exposeMethod('findEmail');
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$mode = $request->getMode();
		if ('deleteRelation' === $mode || 'addRelation' === $mode) {
			if (!\App\Privilege::isPermitted('OSSMailView', 'DetailView', $request->getInteger('mailId'))) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
			if (!\App\Privilege::isPermitted($request->getByType('recordModule', 'Alnum'), 'DetailView', $request->getInteger('record'))) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}
	}

	/**
	 * Remove relationship with record.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function deleteRelation(App\Request $request)
	{
		$relationModel = Vtiger_Relation_Model::getInstance(
			Vtiger_Module_Model::getInstance('OSSMailView'),
			Vtiger_Module_Model::getInstance($request->getByType('recordModule', 'Alnum'))
			);
		$result = false;
		if ($relationModel->privilegeToDelete()) {
			$result = $relationModel->deleteRelation($request->getInteger('mailId'), $request->getInteger('record'));
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Add relationship with record.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function addRelation(App\Request $request)
	{
		$relationModel = Vtiger_Relation_Model::getInstance(
			Vtiger_Module_Model::getInstance('OSSMailView'),
			Vtiger_Module_Model::getInstance($request->getByType('recordModule', 'Alnum'))
			);
		$result = $relationModel->addRelation($request->getInteger('mailId'), $request->getInteger('record'));
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Find the email address in the address book.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function findEmail(App\Request $request)
	{
		$search = $request->getByType('search', 'Text');
		$contacts = [];
		$addressBookFile = ROOT_DIRECTORY . '/cache/addressBook/mails_' . App\User::getCurrentUserRealId() . '.php';
		if (is_file($addressBookFile)) {
			include $addressBookFile;
			$contacts = preg_grep("/{$search}/i", $bookMails);
		}
		$response = new Vtiger_Response();
		$response->setResult(array_values($contacts));
		$response->emit();
	}
}

<?php

/**
 * Chat Detail View Class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Tomasz Poradzewski <a.adach@yetiforce.com>
 */
class Chat_Detail_View extends \App\Controller\View
{
	use \App\Controller\ExposeMethod;

	/**
	 * Record model.
	 *
	 * @var \Vtiger_Record_Model
	 */
	private $recordModel;

	/**
	 * Constructor with a list of allowed methods.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showChatTab');
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 406);
		}
		if ($currentUserPriviligesModel->getId() !== \App\User::getCurrentUserRealId()) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 406);
		}
		$this->checkPermissionByRoom($request);
	}

	/**
	 * Check permission by room.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	private function checkPermissionByRoom(App\Request $request): void
	{
		if ($request->has('recordId')) {
			$this->recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('recordId'));
			if (!$this->recordModel->isViewable()) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}
	}

	/**
	 * Show chat tab in detail view.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 *
	 * @return \html
	 */
	public function showChatTab(App\Request $request)
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'));
		if (!$recordModel->isViewable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$viewer = $this->getViewer($request);
		return $viewer->view('Detail/Chat.tpl', 'Chat', true);
	}
}

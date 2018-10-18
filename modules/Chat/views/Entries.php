<?php

/**
 * Chat Entries View Class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Chat_Entries_View extends \App\Controller\View
{
	use \App\Controller\ExposeMethod;

	/**
	 * Constructor with a list of allowed methods.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('send');
		$this->exposeMethod('get');
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 406);
		}
	}

	/**
	 * Send message function.
	 *
	 * @param \App\Request $request
	 */
	public function send(\App\Request $request)
	{
		echo $request->get('message');
//		$viewer = $this->getViewer($request);
//		$viewer->view('Modal.tpl', $request->getModule());
	}

	public function get(\App\Request $request)
	{
		if ($request->has('roomType') && $request->has('roomId')) {
			$request->getByType('roomType');
			$request->getInteger('roomId');
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('CHAT_ENTRIES', []);
		$viewer->view('Entries.tpl', $request->getModule());
	}

	/**
	 * {@inheritdoc}
	 */
	public function isSessionExtend()
	{
		return false;
	}
}

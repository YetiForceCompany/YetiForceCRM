<?php

/**
 * Chat Entries Action Class.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Chat_Entries_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Constructor with a list of allowed methods.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('add');
		$this->exposeMethod('addChatRoom');
		$this->exposeMethod('switchChatRoom');
	}

	/**
	 * Add entries function.
	 *
	 * @param \App\Request $request
	 */
	public function add(\App\Request $request)
	{
		\App\Chat::getInstanceById($request->getInteger('chat_room_id'))->add($request->get('message'));
		$view = new Chat_Entries_View();
		$view->get($request);
	}

	/**
	 * Add entries function.
	 *
	 * @param \App\Request $request
	 */
	public function addChatRoom(\App\Request $request)
	{
		$chatRoom = \App\Chat::createRoomById($request->getInteger('record'));
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'chat_room_id' => $chatRoom->getChatRoomId(),
			'name' => $chatRoom->getChatRoomName()
		]);
		$response->emit();
	}

	/**
	 * Switch chat room.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function switchChatRoom(\App\Request $request)
	{
		\App\Chat::setCurrentRoomId($request->getInteger('chat_room_id'));
		(new Chat_Entries_View())->get($request);
	}
}

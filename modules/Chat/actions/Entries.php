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
		if (
			!$currentUserPriviligesModel->hasModulePermission($request->getModule()) ||
			!\App\Module::isModuleActive('Chat')
		) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (
			$request->has('record') &&
			!Vtiger_Record_Model::getInstanceById($request->getInteger('record'))->isViewable()
		) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (
			$request->has('chat_room_id') &&
			$request->getInteger('chat_room_id') !== 0 &&
			!Vtiger_Record_Model::getInstanceById($request->getInteger('chat_room_id'))->isViewable()
		) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Constructor with a list of allowed methods.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('addMessage');
		$this->exposeMethod('addRoom');
		$this->exposeMethod('switchRoom');
	}

	/**
	 * Add entries function.
	 *
	 * @param \App\Request $request
	 */
	public function addMessage(\App\Request $request)
	{
		$chatRoom = \App\Chat::getInstanceById($request->getInteger('chat_room_id'));
		$isAssigned = $chatRoom->isAssigned();
		$chatRoom->addMessage($request->get('message'));
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'html' => (new Chat_Entries_View())->getHTML($request, $chatRoom),
			'user_added_to_room' => $isAssigned !== $chatRoom->isAssigned(),
			'room' => ['name' => $chatRoom->getRoomName(), 'room_id' => $chatRoom->getRoomId()]
		]);
		$response->emit();
	}

	/**
	 * Add entries function.
	 *
	 * @param \App\Request $request
	 */
	public function addRoom(\App\Request $request)
	{
		$chatRoom = \App\Chat::createRoomById($request->getInteger('record'));
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'chat_room_id' => $chatRoom->getRoomId(),
			'name' => $chatRoom->getRoomName()
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
	public function switchRoom(\App\Request $request)
	{
		\App\Chat::setCurrentRoomId($request->getInteger('chat_room_id'));
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'html' => (new Chat_Entries_View())->getHTML($request)
		]);
		$response->emit();
	}
}

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
	 * Constructor with a list of allowed methods.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('addMessage');
		$this->exposeMethod('addRoom');
		$this->exposeMethod('switchRoom');
		$this->exposeMethod('removeRoom');
		$this->exposeMethod('addRoomToFavorite');
	}

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$mode = $request->getMode();
		if (
			!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule()) ||
			($mode === 'addMessage' && !$request->has('chat_room_id')) ||
			($mode === 'addRoom' && !$request->has('record')) ||
			($mode === 'switchRoom' && !$request->has('chat_room_id'))
		) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (
			$request->has('record') &&
			!Vtiger_Record_Model::getInstanceById($request->getInteger('record'))->isViewable()
		) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (
			$request->has('chat_room_id') &&
			$request->getInteger('chat_room_id') !== 0 &&
			!Vtiger_Record_Model::getInstanceById($request->getInteger('chat_room_id'))->isViewable()
		) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Add entries function.
	 *
	 * @param \App\Request $request
	 */
	public function addMessage(\App\Request $request)
	{
		$room = \App\Chat::getInstanceById($request->getInteger('chat_room_id'));
		$isAssigned = $room->isAssigned();
		$room->addMessage($request->get('message'));
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'html' => (new Chat_Entries_View())->getHTML($request, $room),
			'user_added_to_room' => $isAssigned !== $room->isAssigned(),
			'room' => ['name' => $room->getNameOfRoom(), 'room_id' => $room->getRoomId()]
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
		$room = \App\Chat::createRoom($request->getInteger('record'));
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'chat_room_id' => $room->getRoomId(),
			'name' => $room->getNameOfRoom()
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

	/**
	 * Remove chat room from the list of favorites.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \yii\db\Exception
	 */
	public function removeRoom(\App\Request $request)
	{
		$roomId = $request->getInteger('chat_room_id');
		if ($roomId === \App\Chat::getCurrentRoomId()) {
			$roomId = 0;
			\App\Chat::setCurrentRoomId(0);
		}
		$room = \App\Chat::getInstanceById($roomId);
		$room->markAsFavorite(false);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'html' => (new Chat_Entries_View())->getHTML(
				$request, \App\Chat::getInstanceById(\App\Chat::getCurrentRoomId())
			),
			'chat_room_id' => \App\Chat::getCurrentRoomId()
		]);
		$response->emit();
	}

	/**
	 * Add room to favorite.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \yii\db\Exception
	 */
	public function addRoomToFavorite(\App\Request $request)
	{
		$room = \App\Chat::getInstanceById($request->getInteger('chat_room_id'));
		$room->markAsFavorite($request->getBoolean('favorite'));
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'chat_room_id' => $room->getRoomId(),
			'name_of_room' => $room->getNameOfRoom(),
			'favorite' => $room->isFavorite(),
		]);
		$response->emit();
	}
}

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
class Chat_Room_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Constructor with a list of allowed methods.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('removeFromFavorites');
		$this->exposeMethod('removeUserFromRoom');
		$this->exposeMethod('addToFavorites');
	}

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 406);
		}
	}

	/**
	 * Remove from favorites.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \yii\db\Exception
	 */
	public function removeFromFavorites(App\Request $request)
	{
		$this->checkPermissionByRoom($request);
		\App\Chat::getInstance($request->getByType('roomType'), $request->getInteger('recordId'))->removeFromFavorites();
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Remove user from room.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \yii\db\Exception
	 */
	public function removeUserFromRoom(App\Request $request)
	{
		$this->checkPermissionByRoom($request);
		\App\Chat::getInstance($request->getByType('roomType'), $request->getInteger('recordId'))->removeFromFavorites($request->getInteger('userId'));
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Add to favorites.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \yii\db\Exception
	 */
	public function addToFavorites(App\Request $request)
	{
		$this->checkPermissionByRoom($request);
		\App\Chat::getInstance($request->getByType('roomType'), $request->getInteger('recordId'))->addToFavorites();
		$response = new Vtiger_Response();
		$response->setResult(\App\Chat::getRoomsByUser());
		$response->emit();
	}

	/**
	 * {@inheritdoc}
	 */
	public function isSessionExtend(App\Request $request)
	{
		return false;
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
		switch ($request->getByType('roomType')) {
			case 'crm':
				$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('recordId'));
				if (!$recordModel->isViewable()) {
					throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
				}
				break;
			case 'group':
				if (!\in_array($request->getInteger('recordId'), \App\User::getCurrentUserModel()->getGroups())) {
					throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
				}
				break;
			case 'global':
				break;
			case 'private':
				$chat = \App\Chat::getInstance($request->getByType('roomType'), $request->getInteger('recordId'));
				if (!$chat->isRoomModerator($request->getInteger('recordId')) && !$chat->isRecordOwner()) {
					throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
				}
				break;
			default:
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}
}

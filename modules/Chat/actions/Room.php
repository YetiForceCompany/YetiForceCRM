<?php

/**
 * Chat Entries Action Class.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$userPrivileges = \Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivileges->hasModulePermission($request->getModule()) || $userPrivileges->getId() !== $userPrivileges->getRealId()) {
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
		$result = \App\Chat::getInstance($request->getByType('roomType'), $request->getInteger('recordId'))->removeFromFavorites();
		$response = new Vtiger_Response();
		$response->setResult($result);
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
		$chat = \App\Chat::getInstance($request->getByType('roomType'), $request->getInteger('recordId'));
		$chat->addToFavorites();
		$response = new Vtiger_Response();
		$response->setResult($chat->getQueryRoom()->one());
		$response->emit();
	}

	/** {@inheritdoc} */
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
			case 'private':
				$chat = \App\Chat::getInstance($request->getByType('roomType'), $request->getInteger('recordId'));
				if (!$chat->isRoomModerator($request->getInteger('recordId')) && !$chat->isRecordOwner()) {
					throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
				}
				break;
			case 'global':
				break;
			case 'user':
				break;
			default:
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}
}

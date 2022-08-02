<?php
/**
 * Api actions.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license 	YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\ManageConsents;

use OpenApi\Annotations as OA;

/**
 * BaseAction class.
 *
 * @OA\Info(
 * 		title="YetiForce API for Webservice App. Type: Manage consents",
 * 		description="Skip the `/webservice` fragment for connections via ApiProxy. There are two ways to connect to API, with or without rewrite, below are examples of both:
 * rewrite
 * - __CRM_URL__/webservice/ManageConsents/Users/Login
 * - __CRM_URL__/webservice/ManageConsents/Accounts/RecordRelatedList/117/Contacts
 * without rewrite
 * - __CRM_URL__/webservice.php?_container=ManageConsents&module=Users&action=Login
 * - __CRM_URL__/webservice.php?_container=ManageConsents&module=Accounts&action=RecordRelatedList&record=117&param=Contacts",
 * 		version="0.2",
 * 		termsOfService="https://yetiforce.com/",
 *   	@OA\Contact(
 *     		email="devs@yetiforce.com",
 *     		name="Devs API Team",
 *     		url="https://yetiforce.com/"
 *   	),
 *   	@OA\License(
 *    		name="YetiForce Public License",
 *     		url="https://yetiforce.com/en/yetiforce/license"
 *		),
 * )
 * @OA\Server(
 *		url="https://gitdeveloper.yetiforce.com",
 *		description="Demo server of the development version",
 * )
 * @OA\Server(
 *		url="https://gitstable.yetiforce.com",
 *		description="Demo server of the latest stable version",
 * )
 */
class BaseAction extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	protected function checkPermission(): void
	{
		$db = \App\Db::getInstance('webservice');
		$userTable = 'w_#__manage_consents_user';
		$userData = (new \App\Db\Query())
			->from($userTable)
			->where([
				'token' => $this->controller->request->getHeader('x-token'),
				'status' => 1,
				'server_id' => $this->controller->app['id'],
			])
			->limit(1)->one($db);
		if (!$userData) {
			throw new \Api\Core\Exception('Invalid data access', 401);
		}
		$this->setAllUserData($userData);
		$db->createCommand()->update($userTable, ['login_time' => date('Y-m-d H:i:s')], ['id' => $userData['id']])->execute();
		\App\User::setCurrentUserId($userData['user_id']);
	}

	/** {@inheritdoc} */
	public function updateSession(array $data = []): void
	{
	}
}

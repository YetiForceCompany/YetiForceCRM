<?php
/**
 * Api actions.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\SMS;

use OpenApi\Annotations as OA;

/**
 * BaseAction class.
 *
 * @OA\Info(
 * 		title="YetiForce API for SMS. Type: SMS",
 * 		description="",
 * 		version="0.1",
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
		$userTable = 'w_#__sms_user';
		$userData = (new \App\Db\Query())
			->from($userTable)
			->where([
				'server_id' => $this->controller->app['id'],
				'token' => $this->controller->request->getByType('x-token', \App\Purifier::ALNUM),
				'status' => 1,
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

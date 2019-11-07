<?php
/**
 * Api actions.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o.
 * @license 	YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
 * 		version="0.1",
 *   	@OA\Contact(
 *     		email="devs@yetiforce.com",
 *     		name="Devs API Team",
 *     		url="https://yetiforce.com/"
 *   	),
 *   	@OA\License(
 *    		name="YetiForce Public License v3",
 *     		url="https://yetiforce.com/en/yetiforce/license"
 *   	),
 *   	termsOfService="https://yetiforce.com/"
 * )
 */
class BaseAction extends \Api\Core\BaseAction
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission()
	{
		$db = \App\Db::getInstance('webservice');
		$userTable = 'w_#__manage_consents_user';
		$row = (new \App\Db\Query())
			->from($userTable)
			->where([
				'token' => $this->controller->request->getHeader('x-token'),
				'status' => 1,
				'server_id' => $this->controller->app['id']
			])
			->limit(1)->one($db);
		if (!$row) {
			throw new \Api\Core\Exception('Invalid data access', 401);
		}
		$db->createCommand()->update($userTable, ['login_time' => date('Y-m-d H:i:s')], ['id' => $row['id']])->execute();
		\Vtiger_Field_Model::setDefaultUiTypeClassName('\\Api\\Core\\Modules\\Vtiger\\UiTypes\\Base');
		$this->session = new \App\Base();
		$this->session->setData($row);
		\App\User::setCurrentUserId($this->session->get('user_id'));

		return true;
	}
}

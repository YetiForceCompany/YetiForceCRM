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
 *   	@OA\Contact(email="devs@yetiforce.com", name="Devs API Team", url="https://yetiforce.com/"),
 *   	@OA\License(name="YetiForce Public License", url="https://yetiforce.com/en/yetiforce/license"),
 * )
 *	@OA\ExternalDocumentation(
 *		description="Platform API Interactive Docs",
 *		url="https://doc.yetiforce.com/api/?urls.primaryName=Manage%20consents"
 *	),
 * 	@OA\Server(description="Demo server of the development version", url="https://gitdeveloper.yetiforce.com")
 * 	@OA\Server(description="Demo server of the latest stable version", url="https://gitstable.yetiforce.com")
 * 	@OA\Tag(name="BaseModule", x={"displayName" : "Base module"})
 * 	@OA\Tag(name="ApprovalsRegister", x={"displayName" : "Approvals register"})
 * 	@OA\Tag(name="Approvals", x={"displayName" : "Approvals"})
 *	@OA\SecurityScheme(
 *		type="http",
 *		securityScheme="basicAuth",
 *		scheme="basic",
 *   	description="Basic Authentication header"
 *	),
 *	@OA\SecurityScheme(
 * 		name="X-API-KEY",
 *   	type="apiKey",
 *    	in="header",
 *		securityScheme="ApiKeyAuth",
 *   	description="Webservice api key header"
 *	),
 *	@OA\SecurityScheme(
 * 		name="X-TOKEN",
 *   	type="apiKey",
 *   	in="header",
 *		securityScheme="token",
 *   	description="Webservice api token by user header"
 *	),
 *	@OA\Schema(
 *		schema="Exception",
 *		title="General - Error exception",
 *		type="object",
 *  	@OA\Property(
 * 			property="status",
 *			description="0 - error",
 * 			enum={0},
 *			type="integer",
 *			example=0
 * 		),
 *		@OA\Property(
 * 			property="error",
 *     	 	description="Error  details",
 *    	 	type="object",
 *   		@OA\Property(property="message", type="string", example="Invalid method", description="To show more details turn on: config\Debug.php apiShowExceptionMessages = true"),
 *   		@OA\Property(property="code", type="integer", example=405),
 *   		@OA\Property(property="file", type="string", example="api\webservice\WebservicePremium\BaseAction\Files.php", description="default disabled to enable set: config\Debug.php apiShowExceptionBacktrace = true"),
 *   		@OA\Property(property="line", type="integer", example=101, description="default disabled to enable set: config\Debug.php apiShowExceptionBacktrace = true"),
 * 			@OA\Property(property="backtrace", type="string", example="#0 api\webservice\WebservicePremium\BaseAction\Files.php (101) ....", description="default disabled to enable set: config\Debug.php apiShowExceptionBacktrace = true"),
 *    	),
 *	),
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

<?php
/**
 * RestApi container - Users Login action file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\RestApi\Users;

use OpenApi\Annotations as OA;

/**
 * RestApi container - Users Login action class.
 */
class Login extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['POST'];

	/** @var string Date time format 'Y-m-d H:i:s' */
	public const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

	/** {@inheritdoc}  */
	public function checkPermission(): void
	{
	}

	/** {@inheritdoc}  */
	public function checkPermissionToModule(): void
	{
	}

	/**
	 * Post method.
	 *
	 * @return array
	 *
	 * @OA\Post(
	 *		path="/webservice/RestApi/Users/Login",
	 *		summary="Logs user into the system",
	 *		tags={"Users"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}}
	 *		},
	 *		@OA\RequestBody(
	 *  		required=true,
	 *  		description="Input data format",
	 *    		@OA\JsonContent(ref="#/components/schemas/UsersLoginRequestBody"),
	 *     		@OA\MediaType(
	 *         		mediaType="multipart/form-data",
	 *         		@OA\Schema(ref="#/components/schemas/UsersLoginRequestBody")
	 *     		),
	 *     		@OA\MediaType(
	 *         		mediaType="application/x-www-form-urlencoded",
	 *         		@OA\Schema(ref="#/components/schemas/UsersLoginRequestBody")
	 *     		),
	 *		),
	 *		@OA\Parameter(
	 *			name="X-ENCRYPTED",
	 *			in="header",
	 *			required=true,
	 * 			@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *      ),
	 *		@OA\Response(
	 *			response=200,
	 *			description="User details",
	 *			@OA\JsonContent(ref="#/components/schemas/UsersLoginResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/UsersLoginResponseBody")
	 *		),
	 *		@OA\Response(
	 *			response=401,
	 *			description="Invalid data access OR Invalid user password OR No crmid",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 *		@OA\Response(
	 *			response=405,
	 *			description="Invalid method",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 * ),
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
	 * @OA\Schema(
	 *		schema="X-ENCRYPTED",
	 *		type="string",
	 *  	description="Is the content request is encrypted",
	 *  	enum={0, 1},
	 *   	default=0
	 * ),
	 * @OA\Schema(
	 * 		schema="UsersLoginRequestBody",
	 * 		title="Users module - Users login request body",
	 * 		description="JSON or form-data",
	 *		type="object",
	 *  	@OA\Property(
	 *       	property="userName",
	 *			description="Webservice user name",
	 *			type="string",
	 * 		),
	 *	@OA\Property(
	 *		property="password",
	 *		description="Webservice user password",
	 *		type="string"
	 *	),
	 *	@OA\Property(
	 *		property="params",
	 *		description="Additional parameters sent by the user, extending the current settings, e.g. language",
	 *		type="object",
	 *		@OA\Property(property="language", type="string", example="pl-PL"),
	 * 	)
	 * ),
	 * @OA\Schema(
	 * 		schema="UsersLoginResponseBody",
	 * 		title="Users module - Users login response body",
	 * 		description="Users login response body",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 * 			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 * 			enum={0, 1},
	 *     	  	type="integer",
	 * 			example=1
	 * 		),
	 *		@OA\Property(
	 *     		property="result",
	 *     	 	description="Content of responses from a given method",
	 *    	 	type="object",
	 *   		@OA\Property(property="token", type="string", minLength=40, maxLength=40),
	 *   		@OA\Property(property="name", type="string"),
	 *    		@OA\Property(property="lastLoginTime", type="string", format="date-time", example="2019-10-07 08:32:38"),
	 *    		@OA\Property(property="lastLogoutTime", type="string", format="date-time", example=null),
	 *    		@OA\Property(property="language", type="string", example="pl-PL"),
	 *    		@OA\Property(property="type", type="integer"),
	 *    		@OA\Property(property="logged", type="boolean"),
	 *    		@OA\Property(
	 *    			property="preferences",
	 *    			type="object",
	 *    			@OA\Property(property="hour_format", type="string", example="24"),
	 *    			@OA\Property(property="start_hour", type="string", example="08:00"),
	 *    			@OA\Property(property="end_hour", type="string", example="16:00"),
	 *    			@OA\Property(property="date_format", type="string", example="yyyy-mm-dd"),
	 *    			@OA\Property(property="time_zone", type="string", example="Europe/Warsaw"),
	 *    			@OA\Property(property="currency_id", type="integer", example=1),
	 *    			@OA\Property(property="currency_grouping_pattern", type="string", example="123,456,789"),
	 *    			@OA\Property(property="currency_decimal_separator", type="string", example="."),
	 *    			@OA\Property(property="currency_grouping_separator", type="string", example=" "),
	 *    			@OA\Property(property="currency_symbol_placement", type="string", example="1.0$"),
	 *    			@OA\Property(property="no_of_currency_decimals", type="integer", example=2),
	 *    			@OA\Property(property="currency_name", type="string", example="Poland, Zlotych"),
	 *    			@OA\Property(property="currency_code", type="string", example="PLN"),
	 *    			@OA\Property(property="currency_symbol", type="string", example="zł"),
	 *    			@OA\Property(property="conv_rate", type="number", format="float", example="1.00000"),
	 * 			),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="Exception",
	 *		title="Error exception",
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
	 *   		@OA\Property(property="file", type="string", example="api\webservice\RestApi\BaseAction\Files.php", description="default disabled to enable set: config\Debug.php apiShowExceptionBacktrace = true"),
	 *   		@OA\Property(property="line", type="integer", example=101, description="default disabled to enable set: config\Debug.php apiShowExceptionBacktrace = true"),
	 * 			@OA\Property(property="backtrace", type="string", example="#0 api\webservice\RestApi\BaseAction\Files.php (101) ....", description="default disabled to enable set: config\Debug.php apiShowExceptionBacktrace = true"),
	 *    	),
	 * ),
	 * @OA\Tag(
	 *		name="Users",
	 *		description="Access to user methods"
	 * )
	 */
	public function post(): array
	{
		$db = \App\Db::getInstance('webservice');
		$row = (new \App\Db\Query())
			->from('w_#__api_user')
			->where(['user_name' => $this->controller->request->get('userName'), 'status' => 1])
			->limit(1)->one($db);
		if (!$row) {
			throw new \Api\Core\Exception('Invalid data access', 401);
		}
		if (\App\Encryption::getInstance()->decrypt($row['password']) !== $this->controller->request->getRaw('password')) {
			throw new \Api\Core\Exception('Invalid user password', 401);
		}
		if (\Api\Portal\Privilege::USER_PERMISSIONS !== $row['type'] && (empty($row['crmid']) || !\App\Record::isExists($row['crmid']))) {
			throw new \Api\Core\Exception('No crmid', 401);
		}
		$db->createCommand()->update('w_#__api_user', ['login_time' => date(static::DATE_TIME_FORMAT)], ['id' => $row['id']])->execute();
		$row = $this->updateSession($row);
		$userModel = \App\User::getUserModel($row['user_id']);
		return [
			'token' => $row['token'],
			'name' => $row['crmid'] ? \App\Record::getLabel($row['crmid']) : $userModel->getName(),
			'lastLoginTime' => $row['login_time'],
			'lastLogoutTime' => $row['logout_time'],
			'language' => $row['language'],
			'type' => $row['type'],
			'logged' => true,
			'preferences' => [
				'hour_format' => $userModel->getDetail('hour_format'),
				'start_hour' => $userModel->getDetail('start_hour'),
				'end_hour' => $userModel->getDetail('end_hour'),
				'date_format' => $userModel->getDetail('date_format'),
				'time_zone' => $userModel->getDetail('time_zone'),
				'currency_id' => $userModel->getDetail('currency_id'),
				'currency_grouping_pattern' => $userModel->getDetail('currency_grouping_pattern'),
				'currency_decimal_separator' => $userModel->getDetail('currency_decimal_separator'),
				'currency_grouping_separator' => $userModel->getDetail('currency_grouping_separator'),
				'currency_symbol_placement' => $userModel->getDetail('currency_symbol_placement'),
				'no_of_currency_decimals' => (int) $userModel->getDetail('no_of_currency_decimals'),
				'currency_name' => $userModel->getDetail('currency_name'),
				'currency_code' => $userModel->getDetail('currency_code'),
				'currency_symbol' => $userModel->getDetail('currency_symbol'),
				'conv_rate' => $userModel->getDetail('conv_rate'),
			],
		];
	}

	/**
	 * Update user session.
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	protected function updateSession(array $row): array
	{
		$row['token'] = hash('sha256', microtime(true) . random_int(1, 999999) . random_int(1, 999999));
		$params = $this->controller->request->getArray('params');
		if (!empty($params['language'])) {
			$language = $params['language'];
		} else {
			$language = empty($row['language']) ? $this->getLanguage() : $row['language'];
		}
		\App\Db::getInstance('webservice')
			->createCommand()
			->insert(\Api\Core\Containers::$listTables[$this->controller->app['type']]['session'], [
				'id' => $row['token'],
				'user_id' => $row['id'],
				'created' => date(self::DATE_TIME_FORMAT),
				'changed' => date(self::DATE_TIME_FORMAT),
				'language' => $language,
				'params' => \App\Json::encode($params),
			])->execute();
		$row['language'] = $language;
		return $row;
	}
}

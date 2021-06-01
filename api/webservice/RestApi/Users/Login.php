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
	protected function checkPermission(): void
	{
	}

	/** {@inheritdoc}  */
	protected function checkPermissionToModule(): void
	{
	}

	/**
	 * Post method.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return array
	 *
	 * @OA\Post(
	 *		path="/webservice/RestApi/Users/Login",
	 *		description="Logs user into the system",
	 *		summary="Logs user",
	 *		tags={"Users"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}}
	 *		},
	 *		@OA\RequestBody(
	 *  		required=true,
	 *  		description="Input data format",
	 *    		@OA\JsonContent(ref="#/components/schemas/Users_Login_RequestBody"),
	 *     		@OA\MediaType(
	 *         		mediaType="multipart/form-data",
	 *         		@OA\Schema(ref="#/components/schemas/Users_Login_RequestBody")
	 *     		),
	 *     		@OA\MediaType(
	 *         		mediaType="application/x-www-form-urlencoded",
	 *         		@OA\Schema(ref="#/components/schemas/Users_Login_RequestBody")
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
	 *			description="User access details",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_Login_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_Login_ResponseBody")
	 *		),
	 *		@OA\Response(
	 *			response=401,
	 *			description="`Invalid data access` OR `Invalid user password` OR `No crmid` OR `2FA verification error`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 *		@OA\Response(
	 *			response=405,
	 *			description="Invalid method",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 *		@OA\Response(
	 *			response=412,
	 *			description="No 2FA TOTP code",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 *	),
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
	 *	@OA\Schema(
	 *		schema="X-ENCRYPTED",
	 *		type="string",
	 *  	description="Is the content request is encrypted",
	 *  	enum={0, 1},
	 *   	default=0
	 *	),
	 *	@OA\Schema(
	 *		schema="Conditions-Mix-For-Query-Generator",
	 *		type="object",
	 *  	description="Multiple or one condition for a query query generator",
	 *		oneOf={
	 *			@OA\Schema(ref="#/components/schemas/Condition-For-Query-Generator"),
	 *			@OA\Schema(ref="#/components/schemas/Conditions-For-Query-Generator"),
	 *		}
	 *	),
	 *	@OA\Schema(
	 *		schema="Condition-For-Query-Generator",
	 *		type="object",
	 *  	description="One condition for query generator",
	 *		@OA\Property(property="fieldName", description="Field name", type="string", example="lastname"),
	 *		@OA\Property(property="value", description="Search value", type="string", example="Kowalski"),
	 *		@OA\Property(property="operator", description="Field operator", type="string", example="e"),
	 *		@OA\Property(property="group", description="Condition group if true is AND", type="boolean", example=true),
	 *	),
	 *	@OA\Schema(
	 *		schema="Conditions-For-Query-Generator",
	 *		type="object",
	 *  	description="Multiple conditions for query generator",
	 *		@OA\AdditionalProperties(
	 *			description="Condition details",
	 *			type="object",
	 *			@OA\Schema(ref="#/components/schemas/Condition-For-Query-Generator"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="Conditions-For-Native-Query",
	 *		type="object",
	 *  	description="Conditions for native query, based on YII 2",
	 *		example={"column_name1" : "searched value 1", "column_name2" : "searched value 2"},
	 *		@OA\ExternalDocumentation(
	 *			description="Database communication engine",
	 *			url="https://yetiforce.com/en/knowledge-base/documentation/developer-documentation/item/new-db-communication-engine"
	 *		),
	 *	),
	 *	@OA\Schema(
	 * 		schema="Users_Login_RequestBody",
	 * 		title="Users module - Users login request body",
	 * 		description="JSON or form-data",
	 *		type="object",
	 *  	@OA\Property(
	 *       	property="userName",
	 *			description="Webservice user name",
	 *			type="string",
	 * 		),
	 *		@OA\Property(
	 *			property="password",
	 *			description="Webservice user password",
	 *			type="string"
	 *		),
	 *		@OA\Property(
	 *			property="code",
	 *			description="2FA TOTP code (optional property), Pass code length = 6, Code period = 30",
	 *			type="string"
	 *		),
	 *		@OA\Property(
	 *			property="params",
	 *			description="Additional parameters sent by the user, extending the current settings, e.g. language",
	 *			type="object",
	 *			@OA\Property(property="language", type="string", example="pl-PL"),
	 *		)
	 *	),
	 *	@OA\Schema(
	 * 		schema="Users_Login_ResponseBody",
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
	 *    		@OA\Property(property="login_method", type="string", enum={"PLL_PASSWORD", "PLL_PASSWORD_2FA"}, example="PLL_PASSWORD_2FA"),
	 *    		@OA\Property(property="authy_methods", type="string", enum={"", "PLL_AUTHY_TOTP"}, example="PLL_AUTHY_TOTP"),
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
	 *	),
	 *	@OA\Schema(
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
	 *	),
	 *	@OA\Tag(
	 *		name="Users",
	 *		description="Access to user methods"
	 *	)
	 */
	public function post(): array
	{
		$this->checkAccess();
		if ('PLL_PASSWORD_2FA' === $this->userData['login_method'] && ($response = $this->twoFactorAuth())) {
			$this->saveLoginHistory([
				'status' => $response,
			]);
			$this->controller->response->setStatus(412);
			return ['error' => [
				'message' => $response,
				'code' => 412,
			]];
		}
		if (\Api\Portal\Privilege::USER_PERMISSIONS !== $this->userData['type'] && (empty($this->userData['crmid']) || !\App\Record::isExists($this->userData['crmid']))) {
			$this->saveLoginHistory([
				'status' => 'ERR_NO_CRMID',
			]);
			$this->updateUser([
				'custom_params' => [
					'invalid_login' => 'No crmid',
					'invalid_login_time' => date(static::DATE_TIME_FORMAT),
				]
			]);
			throw new \Api\Core\Exception('No crmid', 401);
		}
		$this->updateUser([
			'login_time' => date(static::DATE_TIME_FORMAT),
		]);
		$this->createSession();
		$this->saveLoginHistory([
			'status' => 'LBL_SIGNED_IN',
		]);
		return $this->returnData();
	}

	/**
	 * Build data for api response.
	 *
	 * @return array
	 */
	protected function returnData(): array
	{
		$userModel = \App\User::getUserModel($this->userData['user_id']);
		return [
			'token' => $this->userData['sid'],
			'name' => $this->userData['crmid'] ? \App\Record::getLabel($this->userData['crmid']) : $userModel->getName(),
			'lastLoginTime' => $this->userData['login_time'],
			'lastLogoutTime' => $this->userData['custom_params']['logout_time'] ?? '',
			'language' => $this->userData['language'],
			'type' => $this->userData['type'],
			'login_method' => $this->userData['login_method'],
			'authy_methods' => $this->userData['auth']['authy_methods'] ?? '',
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
	 * @return void
	 */
	protected function createSession(): void
	{
		$this->userData['sid'] = hash('sha256', microtime(true) . random_int(1, 999999) . random_int(1, 999999));
		$params = $this->controller->request->getArray('params');
		if (!empty($params['language'])) {
			$this->userData['language'] = $params['language'];
			unset($params['language']);
		} else {
			$this->userData['language'] = $this->getLanguage();
		}
		\App\Db::getInstance('webservice')
			->createCommand()
			->insert($this->controller->app['tables']['session'], [
				'id' => $this->userData['sid'],
				'user_id' => $this->userData['id'],
				'created' => date(self::DATE_TIME_FORMAT),
				'changed' => date(self::DATE_TIME_FORMAT),
				'language' => $this->userData['language'],
				'params' => \App\Json::encode($params),
				'ip' => $this->controller->request->getServer('REMOTE_ADDR'),
				'last_method' => $this->controller->request->getServer('REQUEST_URI'),
				'agent' => \App\TextParser::textTruncate($this->controller->request->getServer('HTTP_USER_AGENT', '-'), 100, false),
			])->execute();
	}

	/**
	 * Check access data.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return void
	 */
	protected function checkAccess(): void
	{
		$db = \App\Db::getInstance('webservice');
		$this->userData = (new \App\Db\Query())->from($this->controller->app['tables']['user'])
			->where(['user_name' => $this->controller->request->get('userName'), 'status' => 1])
			->limit(1)->one($db);
		if (!$this->userData) {
			$this->saveLoginHistory([
				'status' => 'ERR_USER_NOT_FOUND',
			]);
			\App\Encryption::verifyPasswordHash($this->controller->request->getRaw('password'), '', $this->controller->app['type']);
			throw new \Api\Core\Exception('Invalid data access', 401);
		}
		$this->userData['type'] = (int) $this->userData['type'];
		$this->userData['custom_params'] = \App\Json::isEmpty($this->userData['custom_params']) ? [] : \App\Json::decode($this->userData['custom_params']);
		$this->userData['custom_params']['ip'] = $this->controller->request->getServer('REMOTE_ADDR');
		if ($this->userData['auth']) {
			$this->userData['auth'] = \App\Json::decode(\App\Encryption::getInstance()->decrypt($this->userData['auth']));
		}
		if (!\App\Encryption::verifyPasswordHash($this->controller->request->getRaw('password'), $this->userData['password'], $this->controller->app['type'])) {
			$this->updateUser([
				'custom_params' => [
					'invalid_login' => 'Invalid user password',
					'invalid_login_time' => date(static::DATE_TIME_FORMAT),
				]
			]);
			$this->saveLoginHistory([
				'status' => 'ERR_INCORRECT_PASSWORD',
			]);
			throw new \Api\Core\Exception('Invalid data access', 401);
		}
	}

	/**
	 * Check two factor authorization.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return string
	 */
	protected function twoFactorAuth(): string
	{
		$multiFactorAuth = new \Api\Core\TwoFactorAuth($this);
		if (!$multiFactorAuth->isActive() || '' !== $multiFactorAuth->check()) {
			return '';
		}
		if ($additionalData = $multiFactorAuth->hasRequiresAdditionalData()) {
			return $additionalData;
		}
		try {
			$multiFactorAuth->verify();
		} catch (\Throwable $th) {
			$this->updateUser([
				'custom_params' => [
					'invalid_login' => '2FA verification error: ' . $th->getMessage(),
					'invalid_login_time' => date(static::DATE_TIME_FORMAT),
				]
			]);
			$this->saveLoginHistory([
				'status' => '2FA:' . $th->getMessage()
			]);
			throw new \Api\Core\Exception('2FA verification error: ' . $th->getMessage(), 401, $th);
		}
		return '';
	}

	/**
	 * Function to store the login history.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	protected function saveLoginHistory(array $data): void
	{
		\App\Db::getInstance('webservice')->createCommand()
			->insert($this->controller->app['tables']['loginHistory'], array_merge([
				'time' => date(self::DATE_TIME_FORMAT),
				'ip' => $this->controller->request->getServer('REMOTE_ADDR'),
				'agent' => \App\TextParser::textTruncate($this->controller->request->getServer('HTTP_USER_AGENT', '-'), 100, false),
				'user_name' => $this->controller->request->get('userName'),
				'user_id' => $this->userData['id'] ?? null,
			],
			$data))->execute();
	}
}

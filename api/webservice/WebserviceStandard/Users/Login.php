<?php
/**
 * Webservice standard container - Users Login action file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WebserviceStandard\Users;

use OpenApi\Annotations as OA;

/**
 * Webservice standard container - Users Login action class.
 */
class Login extends \Api\Core\BaseAction
{
	use \Api\Core\Traits\LoginHistory;

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
	 * @api
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return array|null
	 *
	 * @OA\Post(
	 *		path="/webservice/WebserviceStandard/Users/Login",
	 *		description="Logs user into the system",
	 *		summary="Logs user",
	 *		tags={"Users"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}}
	 *		},
	 *		@OA\RequestBody(
	 *  		required=true,
	 *  		description="Input data format",
	 *    		@OA\JsonContent(ref="#/components/schemas/Users_Post_Login_Request"),
	 *     		@OA\MediaType(
	 *         		mediaType="multipart/form-data",
	 *         		@OA\Schema(ref="#/components/schemas/Users_Post_Login_Request")
	 *     		),
	 *     		@OA\MediaType(
	 *         		mediaType="application/x-www-form-urlencoded",
	 *         		@OA\Schema(ref="#/components/schemas/Users_Post_Login_Request")
	 *     		),
	 *		),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200,
	 *			description="User details",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_Post_Login_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_Post_Login_Response")
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
	 *		schema="Header-Encrypted",
	 *		type="integer",
	 *		title="Header - Encrypted",
	 *  	description="Is the content request is encrypted",
	 *  	enum={0, 1},
	 *   	default=0
	 *	),
	 *	@OA\Schema(
	 *		schema="Conditions-Mix-For-Query-Generator",
	 *		type="object",
	 *		title="General - Mix conditions for query generator",
	 *  	description="Multiple or one condition for a query generator",
	 *		oneOf={
	 *			@OA\Schema(ref="#/components/schemas/Condition-For-Query-Generator"),
	 *			@OA\Schema(ref="#/components/schemas/Conditions-For-Query-Generator"),
	 *		}
	 *	),
	 *	@OA\Schema(
	 *		schema="Condition-For-Query-Generator",
	 *		type="object",
	 *		title="General - Condition for query generator",
	 *  	description="One condition for query generator",
	 *  	required={"fieldName", "value", "operator"},
	 *		@OA\Property(property="fieldName", description="Field name", type="string", example="lastname"),
	 *		@OA\Property(property="value", description="Search value", type="string", example="Kowalski"),
	 *		@OA\Property(property="operator", description="Field operator", type="string", example="e"),
	 *		@OA\Property(property="group", description="Condition group if true is AND", type="boolean", example=true),
	 *	),
	 *	@OA\Schema(
	 *		schema="Conditions-For-Query-Generator",
	 *		type="object",
	 *		title="General - Conditions for query generator",
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
	 *		title="General - Conditions for native query",
	 *  	description="Conditions for native query, based on YII 2",
	 *		example={"column_name1" : "searched value 1", "column_name2" : "searched value 2"},
	 *		@OA\ExternalDocumentation(
	 *			description="Database communication engine",
	 *			url="https://yetiforce.com/en/knowledge-base/documentation/developer-documentation/item/new-db-communication-engine"
	 *		),
	 *	),
	 *	@OA\Schema(
	 * 		schema="Users_Post_Login_Request",
	 * 		title="Users module - Users login request body",
	 * 		description="JSON or form-data",
	 *		type="object",
	 *		required={"userName", "password"},
	 *  	@OA\Property(property="userName", description="Webservice user name", type="string"),
	 *  	@OA\Property(property="password", description="Webservice user password", type="string"),
	 *  	@OA\Property(property="code", description="2FA TOTP code (optional property), Pass code length = 6, Code period = 30", type="string"),
	 *		@OA\Property(
	 *			property="params",
	 *			description="Additional parameters sent by the user, extending the current settings, e.g. language",
	 *			type="object",
	 *			@OA\Property(property="language", type="string", description="Users language", example="pl-PL"),
	 *		)
	 *	),
	 *	@OA\Schema(
	 * 		schema="Users_Post_Login_Response",
	 * 		title="Users module - Users login response body",
	 * 		description="Users login response body",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *     		property="result",
	 *     	 	description="Content of responses from a given method",
	 *    	 	type="object",
	 *    	 	required={"token", "name", "lastLoginTime", "lastLogoutTime", "language", "type", "login_method", "preferences"},
	 *   		@OA\Property(property="token", type="string", minLength=40, maxLength=40),
	 *   		@OA\Property(property="name", type="string"),
	 *    		@OA\Property(property="lastLoginTime", type="string", example="2019-10-07 08:32:38"),
	 *    		@OA\Property(property="lastLogoutTime", type="string", example=""),
	 *    		@OA\Property(property="language", type="string", example="pl-PL"),
	 *    		@OA\Property(property="type", type="integer"),
	 *    		@OA\Property(property="login_method", type="string", enum={"PLL_PASSWORD", "PLL_PASSWORD_2FA"}, example="PLL_PASSWORD_2FA"),
	 *    		@OA\Property(property="logged", type="boolean"),
	 *    		@OA\Property(
	 *    			property="preferences",
	 *    			type="object",
	 * 				required={"hour_format", "start_hour", "end_hour", "date_format", "time_zone", "raw_time_zone", "currency_id", "currency_grouping_pattern", "currency_decimal_separator", "currency_grouping_separator", "currency_symbol_placement", "no_of_currency_decimals", "currency_name", "currency_code", "currency_symbol", "conv_rate"},
	 *    			@OA\Property(property="hour_format", type="string", example="24"),
	 *    			@OA\Property(property="start_hour", type="string", example="08:00"),
	 *    			@OA\Property(property="end_hour", type="string", example="16:00"),
	 *    			@OA\Property(property="date_format", type="string", example="yyyy-mm-dd"),
	 *    			@OA\Property(property="time_zone", type="string", example="Europe/Warsaw", description="User time zone, all data you will be in this time zone"),
	 *    			@OA\Property(property="raw_time_zone", type="string", example="UTC", description="System main time zone (data in database format)"),
	 *    			@OA\Property(property="currency_id", type="integer", example=1),
	 *    			@OA\Property(property="currency_grouping_pattern", type="string", example="123,456,789"),
	 *    			@OA\Property(property="currency_decimal_separator", type="string", example="."),
	 *    			@OA\Property(property="currency_grouping_separator", type="string", example=" "),
	 *    			@OA\Property(property="currency_symbol_placement", type="string", example="1.0$"),
	 *    			@OA\Property(property="no_of_currency_decimals", type="integer", example=2),
	 *    			@OA\Property(property="currency_name", type="string", example="Poland, Zlotych"),
	 *    			@OA\Property(property="currency_code", type="string", example="PLN"),
	 *    			@OA\Property(property="currency_symbol", type="string", example="zł"),
	 *    			@OA\Property(property="conv_rate", type="number", format="float", example=1.00000),
	 * 			),
	 *    		@OA\Property(property="authy_methods", type="string", enum={"", "PLL_AUTHY_TOTP"}, example="PLL_AUTHY_TOTP"),
	 *    		@OA\Property(property="2faObligatory", type="boolean", example=true),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="Exception",
	 *		title="General - Error exception",
	 *		type="object",
	 *		required={"status", "error"},
	 *		@OA\Property(property="status", type="integer", enum={0}, title="0 - error", example=0),
	 *		@OA\Property(
	 * 			property="error",
	 *     	 	description="Error  details",
	 *    	 	type="object",
	 *    	 	required={"message", "code"},
	 *   		@OA\Property(property="message", type="string", example="Invalid method", description="To show more details turn on: config\Debug.php apiShowExceptionMessages = true"),
	 *   		@OA\Property(property="code", type="integer", example=405),
	 *   		@OA\Property(property="file", type="string", example="api\webservice\WebserviceStandard\BaseAction\Files.php", description="default disabled to enable set: config\Debug.php apiShowExceptionBacktrace = true"),
	 *   		@OA\Property(property="line", type="integer", example=101, description="default disabled to enable set: config\Debug.php apiShowExceptionBacktrace = true"),
	 *   		@OA\Property(property="previous", type="object", description="Previous exception"),
	 * 			@OA\Property(property="backtrace", type="string", example="#0 api\webservice\WebserviceStandard\BaseAction\Files.php (101) ....", description="default disabled to enable set: config\Debug.php apiShowExceptionBacktrace = true"),
	 *    	),
	 *	),
	 */
	public function post(): ?array
	{
		$this->checkAccess();
		if ('PLL_PASSWORD_2FA' === $this->getUserData('login_method') && ($response = $this->twoFactorAuth())) {
			$this->saveLoginHistory([
				'status' => $response,
			]);
			$this->controller->response->setStatus(412);
			$this->controller->response->setBody([
				'status' => 0,
				'error' => [
					'message' => \App\Language::translate($response, 'Other.Exceptions'),
					'code' => 412,
				],
			]);
			return null;
		}
		if (\Api\WebservicePremium\Privilege::USER_PERMISSIONS !== $this->getPermissionType() && (empty($this->getUserCrmId()) || !\App\Record::isExists($this->getUserCrmId()))) {
			$this->saveLoginHistory([
				'status' => 'ERR_NO_CRMID',
			]);
			$this->updateUser([
				'custom_params' => [
					'invalid_login' => 'No crmid',
					'invalid_login_time' => date(static::DATE_TIME_FORMAT),
				],
			]);
			throw new \Api\Core\Exception('No crmid', 401);
		}
		$this->saveData();
		return $this->returnData();
	}

	/**
	 * Save user data and logs.
	 *
	 * @return void
	 */
	protected function saveData(): void
	{
		$this->updateUser([
			'login_time' => date(static::DATE_TIME_FORMAT),
		]);
		$this->createSession();
		$this->saveLoginHistory([
			'status' => 'LBL_SIGNED_IN',
		]);
	}

	/**
	 * Build data for api response.
	 *
	 * @return array
	 */
	protected function returnData(): array
	{
		\App\User::setCurrentUserId($this->getUserData('user_id'));
		$userModel = \App\User::getCurrentUserModel();
		$data = [
			'token' => $this->getUserData('sid'),
			'name' => $this->getUserCrmId() ? \App\Record::getLabel($this->getUserCrmId()) : $userModel->getName(),
			'lastLoginTime' => $this->getUserData('login_time'),
			'lastLogoutTime' => $this->getUserData('custom_params', 'logout_time') ?? '',
			'language' => $this->getUserData('language'),
			'type' => $this->getPermissionType(),
			'login_method' => $this->getUserData('login_method'),
			'logged' => true,
			'preferences' => [
				'hour_format' => $userModel->getDetail('hour_format'),
				'start_hour' => $userModel->getDetail('start_hour'),
				'end_hour' => $userModel->getDetail('end_hour'),
				'date_format' => $userModel->getDetail('date_format'),
				'time_zone' => $userModel->getDetail('time_zone'),
				'raw_time_zone' => \App\Fields\DateTime::getTimeZone(),
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
		if ('PLL_PASSWORD_2FA' === $this->getUserData('login_method')) {
			$data['authy_methods'] = $this->getUserData('auth')['authy_methods'] ?? '';
			if ($this->controller->request->isEmpty('code')) {
				$data['2faObligatory'] = 'TOTP_OBLIGATORY' === \App\Config::security('USER_AUTHY_MODE');
			}
		}
		return $data;
	}

	/**
	 * Update user session.
	 *
	 * @return void
	 */
	protected function createSession(): void
	{
		$this->setUserData('sid', hash('sha256', microtime(true) . random_int(1, 999999) . random_int(1, 999999)));
		$params = $this->controller->request->getArray('params');
		if (!empty($params['language'])) {
			$this->setUserData('language', $params['language']);
			unset($params['language']);
		} else {
			$this->setUserData('language', $this->getLanguage());
		}
		\App\Db::getInstance('webservice')
			->createCommand()
			->insert($this->controller->app['tables']['session'], [
				'id' => $this->getUserData('sid'),
				'user_id' => $this->getUserData('id'),
				'created' => date(self::DATE_TIME_FORMAT),
				'changed' => date(self::DATE_TIME_FORMAT),
				'language' => $this->getUserData('language'),
				'params' => \App\Json::encode($params),
				'ip' => $this->controller->request->getServer('REMOTE_ADDR'),
				'last_method' => $this->controller->request->getServer('REQUEST_URI'),
				'agent' => \App\TextUtils::textTruncate($this->controller->request->getServer('HTTP_USER_AGENT', '-'), 100, false),
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
		$userData = (new \App\Db\Query())->from($this->controller->app['tables']['user'])
			->where(['server_id' => $this->controller->app['id'], 'user_name' => $this->controller->request->get('userName'), 'status' => 1])
			->limit(1)->one($db);
		if (!$userData) {
			$this->saveLoginHistory([
				'status' => 'ERR_USER_NOT_FOUND',
			]);
			\App\Encryption::verifyPasswordHash($this->controller->request->getRaw('password'), '', $this->controller->app['type']);
			throw new \Api\Core\Exception('Invalid data access', 401);
		}
		$this->setAllUserData($userData);
		$this->setUserData('type', (int) $this->getUserData('type'));
		$this->setUserData('custom_params', [
			'ip' => $this->controller->request->getServer('REMOTE_ADDR'),
		]);
		if (!\App\Encryption::verifyPasswordHash($this->controller->request->getRaw('password'), $this->getUserData('password'), $this->controller->app['type'])) {
			$this->updateUser([
				'custom_params' => [
					'invalid_login' => 'Invalid user password',
					'invalid_login_time' => date(static::DATE_TIME_FORMAT),
				],
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
				],
			]);
			$this->saveLoginHistory([
				'status' => '2FA:' . $th->getMessage(),
			]);
			throw new \Api\Core\Exception('2FA verification error: ' . $th->getMessage(), 401, $th);
		}
		return '';
	}
}

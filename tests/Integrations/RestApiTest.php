<?php
/**
 * RestApi integrations test file.
 *
 * @see https://github.com/Maks3w/SwaggerAssertions/
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\Integrations;

use FR3D\SwaggerAssertions\PhpUnit\AssertsTrait;
use FR3D\SwaggerAssertions\SchemaManager;

/**
 * @internal
 * @coversNothing
 */
final class RestApiTest extends \Tests\Base
{
	use AssertsTrait;

	/**
	 * Api server id.
	 *
	 * @var int
	 */
	private static $serverId;

	/**
	 * Api user id.
	 *
	 * @var int
	 */
	private static $apiUserId;

	/**
	 * Request options.
	 *
	 * @var array
	 */
	private static $requestOptions = [];

	/**
	 * Details about logged in user.
	 *
	 * @var array
	 */
	private static $authUserParams;
	private static $recordId;

	/**
	 * @var SchemaManager
	 */
	protected static $schemaManager;

	/**
	 * @var \GuzzleHttp\Client
	 */
	protected $httpClient;

	public static function setUpBeforeClass(): void
	{
		self::$schemaManager = new SchemaManager(json_decode(file_get_contents(ROOT_DIRECTORY . '/public_html/api/RestApi.json')));
	}

	protected function setUp(): void
	{
		$this->httpClient = new \GuzzleHttp\Client(\App\Utils::merge(\App\RequestHttp::getOptions(), [
			'base_uri' => \App\Config::main('site_URL') . 'webservice/RestApi/',
			'auth' => ['api', 'api'],
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
			'timeout' => 60,
			'connect_timeout' => 60,
			'http_errors' => false,
			'headers' => [
				'x-raw-data' => 1,
			],
		]));
	}

	/**
	 * Testing add configuration.
	 */
	public function testAddConfiguration(): void
	{
		$app = \Settings_WebserviceApps_Record_Model::getCleanInstance();
		$app->set('type', 'RestApi');
		$app->set('status', 1);
		$app->set('name', 'api');
		$app->set('acceptable_url', '');
		$app->set('pass', 'api');
		$app->save();
		self::$serverId = (int) $app->getId();

		$row = (new \App\Db\Query())->from('w_#__servers')->where(['id' => self::$serverId])->one();
		static::assertNotFalse($row, 'No record id: ' . self::$serverId);
		static::assertSame($row['type'], 'RestApi');
		static::assertSame($row['status'], 1);
		static::assertSame($row['name'], 'api');
		static::assertSame($row['pass'], 'api');
		self::$requestOptions['headers']['x-api-key'] = $row['api_key'];

		$user = \Settings_WebserviceUsers_Record_Model::getCleanInstance('RestApi');
		$user->setData([
			'server_id' => self::$serverId,
			'status' => 1,
			'user_name' => 'api@yetiforce.com',
			'password' => \App\Encryption::createPasswordHash('api', 'RestApi'),
			'type' => 1,
			'popupReferenceModule' => 'Contacts',
			'crmid' => 0,
			'crmid_display' => '',
			'login_method' => 'PLL_PASSWORD',
			'authy_methods' => 'PLL_AUTHY_TOTP',
			'user_id' => \App\User::getActiveAdminId(),
		]);
		$user->save();
		self::$apiUserId = $user->getId();
		$row = (new \App\Db\Query())->from('w_#__api_user')->where(['id' => self::$apiUserId])->one();
		static::assertNotFalse($row, 'No record id: ' . self::$apiUserId);
		static::assertSame((int) $row['server_id'], self::$serverId);
		static::assertSame($row['user_name'], 'api@yetiforce.com');
		static::assertTrue(\App\Encryption::verifyPasswordHash('api', $row['password'], 'RestApi'));

		$fieldModel = \Vtiger_Field_Model::init('Accounts', \App\Field::SYSTEM_FIELDS['share_externally']);
		$fieldModel->fieldparams = self::$serverId;
		$fieldModel->column = $fieldModel->name = 'share_externally_api';
		$blockInstance = \vtlib\Block::getInstance('LBL_ACCOUNT_INFORMATION', 'Accounts');
		$blockInstance->addField($fieldModel);
	}

	/**
	 * Testing login.
	 */
	public function testLogIn(): void
	{
		$request = $this->httpClient->post('Users/Login', \App\Utils::merge(
				[
					'json' => [
						'userName' => 'api@yetiforce.com',
						'password' => 'api',
						'params' => ['language' => 'pl-PL'],
					],
				], self::$requestOptions)
		);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Users/Login API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Users/Login API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::$authUserParams = $response['result'];
		self::$requestOptions['headers']['x-token'] = self::$authUserParams['token'];
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/RestApi/Users/Login', 'post', 200);
	}

	/**
	 * Test logon 2fa .
	 */
	public function testLogIn2fa(): void
	{
		$request = $this->httpClient->get('Users/TwoFactorAuth', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Users/TwoFactorAuth API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Users/TwoFactorAuth API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame('TOTP', $response['result']['authMethods'], 'Users/TwoFactorAuth API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$secretKey = $response['result']['secretKey'];
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/RestApi/Users/TwoFactorAuth', 'get', 200);

		$request = $this->httpClient->post('Users/Login', \App\Utils::merge(
			[
				'json' => [
					'userName' => 'api@yetiforce.com',
					'password' => 'api',
					'code' => (new \Sonata\GoogleAuthenticator\GoogleAuthenticator())->getCode($secretKey),
				],
			], self::$requestOptions)
		);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Users/Login API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Users/Login API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::$authUserParams = $response['result'];
		self::$requestOptions['headers']['x-token'] = self::$authUserParams['token'];
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/RestApi/Users/Login', 'post', 200);

		$request = $this->httpClient->delete('Users/TwoFactorAuth', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Users/TwoFactorAuth API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Users/TwoFactorAuth API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertTrue($response['result'], 'Users/TwoFactorAuth API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/RestApi/Users/TwoFactorAuth', 'delete', 200);
	}


	/**
	 * Testing Logout.
	 */
	public function testLogout(): void
	{
		$request = $this->httpClient->put('Users/Logout', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Users/Logout API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Users/Logout API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/RestApi/Users/Logout', 'put', 200);
	}
}

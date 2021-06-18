<?php
/**
 * PortalTest integrations test file.
 *
 * @see https://github.com/Maks3w/SwaggerAssertions/
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Tests\Integrations;

use FR3D\SwaggerAssertions\PhpUnit\AssertsTrait;
use FR3D\SwaggerAssertions\SchemaManager;

/**
 * @internal
 * @coversNothing
 */
final class PortalTest extends \Tests\Base
{
	use AssertsTrait;

	/** @var int Api server id. */
	private static $serverId;

	/** @var int Api user id. */
	private static $apiUserId;

	/** @var int Api user id. */
	private static $apiUserName = 'demo@yetiforce.com';

	/** @var int Api user id. */
	private static $apiUserPass = 'demo';

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
		self::$schemaManager = new SchemaManager(json_decode(file_get_contents(ROOT_DIRECTORY . '/public_html/api/Portal.json')));
	}

	protected function setUp(): void
	{
		$this->httpClient = new \GuzzleHttp\Client(\App\Utils::merge(\App\RequestHttp::getOptions(), [
			'base_uri' => \App\Config::main('site_URL') . 'webservice/Portal/',
			'auth' => ['portal', 'portal'],
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
			'timeout' => 60,
			'connect_timeout' => 60,
			'http_errors' => false,
			'headers' => [
				'x-raw-data' => 1,
				'x-header-fields' => 1,
			],
		]));
	}

	/**
	 * Testing add configuration.
	 */
	public function testAddConfiguration(): void
	{
		$app = \Settings_WebserviceApps_Record_Model::getCleanInstance();
		$app->set('type', 'Portal');
		$app->set('status', 1);
		$app->set('name', 'portal');
		$app->set('url', '');
		$app->set('ips', '');
		$app->set('pass', 'portal');
		$app->save();
		self::$serverId = (int) $app->getId();

		$row = (new \App\Db\Query())->from('w_#__servers')->where(['id' => self::$serverId])->one();
		static::assertNotFalse($row, 'No record id: ' . self::$serverId);
		static::assertSame($row['type'], 'Portal');
		static::assertSame($row['status'], 1);
		static::assertSame($row['name'], 'portal');
		static::assertSame($row['pass'], 'portal');
		self::$requestOptions['headers']['x-api-key'] = $row['api_key'];

		$user = \Settings_WebserviceUsers_Record_Model::getCleanInstance('Portal');
		$user->setData([
			'server_id' => self::$serverId,
			'status' => 1,
			'user_name' => self::$apiUserName,
			'password' => \App\Encryption::createPasswordHash(self::$apiUserPass, 'Portal'),
			'type' => 1,
			'popupReferenceModule' => 'Contacts',
			'crmid' => 0,
			'crmid_display' => '',
			'login_method' => 'PLL_PASSWORD',
			'user_id' => \App\User::getActiveAdminId(),
		]);
		$user->save();
		self::$apiUserId = $user->getId();
		$row = (new \App\Db\Query())->from('w_#__portal_user')->where(['id' => self::$apiUserId])->one();
		static::assertNotFalse($row, 'No record id: ' . self::$apiUserId);
		static::assertSame((int) $row['server_id'], self::$serverId);
		static::assertSame($row['user_name'], self::$apiUserName);
		static::assertTrue(\App\Encryption::verifyPasswordHash(self::$apiUserPass, $row['password'], 'Portal'));

		$fieldModel = \Vtiger_Field_Model::init('Accounts', \App\Field::SYSTEM_FIELDS['share_externally']);
		$fieldModel->fieldparams = self::$serverId;
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
						'userName' => self::$apiUserName,
						'password' => self::$apiUserPass,
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
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/Users/Login', 'post', 200);
	}

	/**
	 * Testing add record.
	 */
	public function testAddRecord(): void
	{
		$request = $this->httpClient->post('Accounts/Record/', \App\Utils::merge(['json' => [
			'accountname' => 'Api YetiForce Sp. z o.o.',
			'addresslevel5a' => 'Warszawa',
			'addresslevel8a' => 'Marszałkowska',
			'buildingnumbera' => 111,
			'legal_form' => 'PLL_GENERAL_PARTNERSHIP',
			'share_externally' => 1,
		]], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Accounts/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Accounts/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::$recordId = $response['result']['id'];
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/Record', 'post', 200);
	}

	/**
	 * Testing add inventory record.
	 */
	public function testAddInventoryRecord(): void
	{
		$request = $this->httpClient->post('SCalculations/Record/', \App\Utils::merge(['json' => [
			'subject' => 'Api YetiForce Sp. z o.o.',
			'inventory' => [
				1 => [
					'name' => \Tests\Base\C_RecordActions::createProductRecord()->getId(),
					'qty' => 2,
					'price' => 5,
					'total' => 10,
					'marginp' => 0,
					'margin' => 10,
					'purchase' => 0,
					'comment1' => 0,
					'unit' => 'Incidents',
				],
			],
		]], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'SCalculations/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'SCalculations/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/Record', 'post', 200);
	}

	/**
	 * Testing edit record.
	 */
	public function testEditRecord(): void
	{
		$request = $this->httpClient->put('Accounts/Record/' . self::$recordId, \App\Utils::merge(['json' => [
			'accountname' => 'Api YetiForce Sp. z o.o. New name',
			'buildingnumbera' => 222,
		]], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Accounts/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Accounts/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/Record/{recordId}', 'put', 200);
	}

	/**
	 * Testing get record.
	 */
	public function testGetRecord(): void
	{
		$request = $this->httpClient->get('Accounts/Record/' . self::$recordId, self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Accounts/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Accounts/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame($response['result']['rawData']['accountname'], 'Api YetiForce Sp. z o.o. New name');
		static::assertSame($response['result']['rawData']['legal_form'], 'PLL_GENERAL_PARTNERSHIP');
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/Record/{recordId}', 'get', 200);
	}

	/**
	 * Testing get record history.
	 */
	public function testGetRecordHistory(): void
	{
		$request = $this->httpClient->get('Accounts/RecordHistory/' . self::$recordId, self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Accounts/RecordHistory/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Accounts/RecordHistory/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/RecordHistory/{recordId}', 'get', 200);
	}

	/**
	 * Testing delete record.
	 */
	public function testDeleteRecord(): void
	{
		$request = $this->httpClient->post('Accounts/Record/', \App\Utils::merge(['json' => [
			'accountname' => 'Api Delete YetiForce Sp. z o.o.',
			'legal_form' => 'PLL_GENERAL_PARTNERSHIP',
			'share_externally' => 1,
		]], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Accounts/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Accounts/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);

		$request = $this->httpClient->delete('Accounts/Record/' . $response['result']['id'], self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Accounts/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Accounts/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertTrue($response['result']);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/Record/{recordId}', 'delete', 200);
	}

	/**
	 * Testing get record related list.
	 */
	public function testGetRecordRelatedList(): void
	{
		$request = $this->httpClient->get('Accounts/RecordRelatedList/' . self::$recordId . '/Contacts', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Accounts/RecordRelatedList/{ID}/Contacts API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Accounts/RecordRelatedList/{ID}/Contacts API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/RecordRelatedList/{recordId}/{relatedModuleName}', 'get', 200);
	}

	/**
	 * Testing get related modules list.
	 */
	public function testRelatedModules(): void
	{
		$request = $this->httpClient->get('Accounts/RelatedModules', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Accounts/RelatedModules/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Accounts/RelatedModules/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertTrue(isset($response['result']['base']), 'Accounts/RelatedModules/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertTrue(isset($response['result']['related']), 'Accounts/RelatedModules/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/RelatedModules/{recordId}', 'get', 200);
	}

	/**
	 * Testing record list.
	 */
	public function testRecordList(): void
	{
		$request = $this->httpClient->get('Accounts/RecordsList', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Accounts/RecordsList/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Accounts/RecordsList/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertGreaterThanOrEqual(1, \count($response['result']['records']));
		static::assertGreaterThanOrEqual(1, \count($response['result']['rawData']));
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/RecordsList', 'get', 200);

		$request = $this->httpClient->get('Users/RecordsList', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Users/RecordsList/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Users/RecordsList/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertGreaterThanOrEqual(1, \count($response['result']['records']));
		static::assertGreaterThanOrEqual(1, \count($response['result']['rawData']));
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/RecordsList', 'get', 200);
	}

	/**
	 * Testing get fields.
	 */
	public function testGetFields(): void
	{
		$request = $this->httpClient->get('SQuotes/Fields/', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'SQuotes/Fields/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'SQuotes/Fields/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertTrue(!empty($response['result']['fields']), 'SQuotes/Fields/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertTrue(!empty($response['result']['blocks']), 'SQuotes/Fields/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/Fields', 'get', 200);
	}

	/**
	 * Testing get privileges.
	 */
	public function testGetPrivileges(): void
	{
		$request = $this->httpClient->get('Accounts/Privileges/', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Accounts/Privileges/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Accounts/Privileges/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertTrue(!empty($response['result']), 'Accounts/Privileges/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/Privileges', 'get', 200);
	}

	/**
	 * Testing get Dashboard.
	 */
	public function testGetDashboard(): void
	{
		$request = $this->httpClient->get('Accounts/Dashboard/', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Accounts/Dashboard/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Accounts/Dashboard/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/Dashboard', 'get', 200);
	}

	/**
	 * Testing get PDF.
	 */
	public function testGetPdfTemplates(): void
	{
		$request = $this->httpClient->get('Accounts/PdfTemplates/' . self::$recordId, self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Accounts/PdfTemplates/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Accounts/PdfTemplates/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/PdfTemplates/{recordId}', 'get', 200);
	}

	/**
	 * Testing get modules.
	 */
	public function testGetModules(): void
	{
		$request = $this->httpClient->get('Modules', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Modules API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Modules API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertTrue(!empty($response['result']['Accounts']), 'Modules API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/Modules', 'get', 200);
	}

	/**
	 * Testing get api menu.
	 */
	public function testGetMenu(): void
	{
		$request = $this->httpClient->get('Menu', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Methods API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Methods API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/Menu', 'get', 200);
	}

	/**
	 * Tests for custom view api method.
	 */
	public function testCustomView(): void
	{
		$request = $this->httpClient->get('Accounts/CustomView', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Methods API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Methods API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertNotEmpty($response['result'], 'Result should not be empty' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/CustomView', 'get', 200);

		$cvId = key($response['result']);
		$request = $this->httpClient->get('Accounts/CustomView/' . $cvId, self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Methods API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Methods API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertNotEmpty($response['result'], 'Result should not be empty' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame($cvId, $response['result']['cvid'], 'Result should the same' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/CustomView/{cvId}', 'get', 200);
	}

	/**
	 * Testing get api Install.
	 */
	public function testGetInstall(): void
	{
		$request = $this->httpClient->put('Install', \App\Utils::merge(['json' => ['xxx' => 'yyy']], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Methods API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Methods API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/Install', 'put', 200);
	}

	/**
	 * Testing get user.
	 */
	public function testGetUser(): void
	{
		$request = $this->httpClient->get('Users/Record/' . \App\User::getActiveAdminId(), self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Users/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Users/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/Users/Record/{userId}', 'get', 200);
	}

	/**
	 * Testing get products.
	 */
	public function testGetProducts(): void
	{
		$record = \Tests\Base\C_RecordActions::createProductRecord(false);
		$request = $this->httpClient->get('Products/Record/' . $record->getId(), \App\Utils::merge([
			'headers' => [
				'x-unit-price' => 1,
				'x-unit-gross' => 1,
				'x-product-bundles' => 1,
			],
		], self::$requestOptions));
		$this->logs = $record->getData();
		$body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Products/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Products/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame($response['result']['rawData']['productname'], 'System CRM YetiForce');
		static::assertTrue(isset($response['result']['ext']['unit_price']), 'Products/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertTrue(isset($response['result']['ext']['unit_gross']), 'Products/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertTrue(isset($response['result']['productBundles']), 'Products/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/Products/Record/{recordId}', 'get', 200);

		$request = $this->httpClient->get('Products/RecordsTree', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Products/RecordsTree API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Products/RecordsTree API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/Products/RecordsTree', 'get', 200);
	}

	/**
	 * Testing get Files.
	 */
	public function testGetFiles(): void
	{
		$record = \Tests\Base\C_RecordActions::createDocumentsRecord();
		$fileDetails = $record->getFileDetails();
		$savedFile = $fileDetails['path'] . $fileDetails['attachmentsid'];
		$fileInstance = \App\Fields\File::loadFromPath($savedFile);
		$request = $this->httpClient->put('Files', \App\Utils::merge(['json' => [
			'module' => 'Documents',
			'actionName' => 'DownloadFile',
			'record' => $record->getId(),
			'fileid' => 1,
		]], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		static::assertSame(200, $request->getStatusCode(), 'Files API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame($body, $fileInstance->getContents(), 'Files API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($body, self::$schemaManager, '/webservice/Portal/Files', 'put', 200);
	}

	/**
	 * Testing get access activity history.
	 */
	public function testAccessActivityHistory(): void
	{
		$request = $this->httpClient->get('Users/AccessActivityHistory', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Users/AccessActivityHistory API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Users/AccessActivityHistory API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertNotEmpty(isset($response['result']), 'Users/AccessActivityHistory API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/Users/AccessActivityHistory', 'get', 200);
	}

	/**
	 * Testing change password.
	 */
	public function testChangePassword(): void
	{
		$request = $this->httpClient->put('Users/ChangePassword', \App\Utils::merge(
			[
				'json' => [
					'currentPassword' => self::$apiUserPass,
					'newPassword' => 'demo2',
				],
			], self::$requestOptions)
		);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Users/ChangePassword API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Users/ChangePassword API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/Users/ChangePassword', 'put', 200);
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
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/Users/Logout', 'put', 200);
		unset(self::$requestOptions['headers']['x-token']);
	}

	/**
	 * Testing reset password .
	 */
	public function testResetPassword(): void
	{
		$request = $this->httpClient->post('Users/ResetPassword', \App\Utils::merge(
			[
				'json' => [
					'userName' => self::$apiUserName,
					'deviceId' => 'tests',
				],
			], self::$requestOptions)
		);
		$this->logs = $body = $request->getBody()->getContents();
		$assertMessage = "Users/ResetPassword API error: \n{$request->getReasonPhrase()}|$body";
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), $assertMessage);
		static::assertSame(1, $response['status'], $assertMessage);
		static::assertTrue($response['result']['mailerStatus'], $assertMessage);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/Users/ResetPassword', 'post', 200);

		$row = (new \App\Db\Query())->from('s_#__tokens')->where(['method' => '\Api\RestApi\Users\ResetPassword'])->one();
		static::assertNotEmpty($row, $assertMessage);
		static::assertNotEmpty($row['uid'], $assertMessage);

		$request = $this->httpClient->put('Users/ResetPassword', \App\Utils::merge(
			[
				'json' => [
					'token' => $row['uid'],
					'password' => self::$apiUserPass,
					'deviceId' => 'tests',
				],
			], self::$requestOptions)
		);
		$this->logs = $body = $request->getBody()->getContents();
		$assertMessage = "Users/ResetPassword API error: \n{$request->getReasonPhrase()}|$body";
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), $assertMessage);
		static::assertSame(1, $response['status'], $assertMessage);
		static::assertTrue($response['result'], $assertMessage);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/Users/ResetPassword', 'post', 200);
	}

	/**
	 * Testing delete configuration.
	 */
	public function testDeleteConfiguration(): void
	{
		\Settings_WebserviceUsers_Record_Model::getInstanceById(self::$apiUserId, 'Portal')->delete();
		\Settings_WebserviceApps_Record_Model::getInstanceById(self::$serverId)->delete();

		static::assertFalse((new \App\Db\Query())->from('w_#__servers')->where(['id' => self::$serverId])->exists(), 'Record in the database should not exist');
		static::assertFalse((new \App\Db\Query())->from('w_#__portal_user')->where(['id' => self::$apiUserId])->exists(), 'Record in the database should not exist');
	}
}

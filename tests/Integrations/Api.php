<?php
/**
 * Api integrations test class.
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

class Api extends \Tests\Base
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
		self::$schemaManager = new SchemaManager(json_decode(file_get_contents(ROOT_DIRECTORY . '/public_html/api/Portal.json')));
	}

	protected function setUp(): void
	{
		$this->httpClient = new \GuzzleHttp\Client(array_merge(\App\RequestHttp::getOptions(), [
			'base_uri' => \App\Config::main('site_URL') . 'webservice/Portal/',
			'auth' => ['portal', 'portal'],
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
			'timeout' => 60,
			'connect_timeout' => 60,
			'http_errors' => false,
			'headers' => [
				'x-raw-data' => 1
			],
		]));
	}

	/**
	 * Testing add configuration.
	 */
	public function testAddConfiguration(): void
	{
		$webserviceApps = \Settings_WebserviceApps_Record_Model::getCleanInstance();
		$webserviceApps->set('type', 'Portal');
		$webserviceApps->set('status', 1);
		$webserviceApps->set('name', 'portal');
		$webserviceApps->set('acceptable_url', '');
		$webserviceApps->set('pass', 'portal');
		$webserviceApps->save();
		self::$serverId = (int) $webserviceApps->getId();

		$row = (new \App\Db\Query())->from('w_#__servers')->where(['id' => self::$serverId])->one();
		$this->assertNotFalse($row, 'No record id: ' . self::$serverId);
		$this->assertSame($row['type'], 'Portal');
		$this->assertSame($row['status'], 1);
		$this->assertSame($row['name'], 'portal');
		$this->assertSame($row['pass'], 'portal');
		self::$requestOptions['headers']['x-api-key'] = $row['api_key'];

		$webserviceUsers = \Settings_WebserviceUsers_Record_Model::getCleanInstance('Portal');
		$webserviceUsers->setData([
			'server_id' => self::$serverId,
			'status' => '1',
			'user_name' => 'demo@yetiforce.com',
			'password' => 'demo',
			'type' => '1',
			'language' => 'pl-PL',
			'popupReferenceModule' => 'Contacts',
			'crmid' => 0,
			'crmid_display' => '',
			'user_id' => \App\User::getActiveAdminId(),
		]);
		$webserviceUsers->save();
		self::$apiUserId = $webserviceUsers->getId();
		$row = (new \App\Db\Query())->from('w_#__portal_user')->where(['id' => self::$apiUserId])->one();
		$this->assertNotFalse($row, 'No record id: ' . self::$apiUserId);
		$this->assertSame((int) $row['server_id'], self::$serverId);
		$this->assertSame($row['user_name'], 'demo@yetiforce.com');
		$this->assertSame($row['password'], 'demo');
		$this->assertSame($row['language'], 'pl-PL');

		$blockInstance = \vtlib\Block::getInstance('LBL_ACCOUNT_INFORMATION', 'Accounts');
		$fieldInstance = new \Vtiger_Field_Model();
		$fieldInstance->table = 'vtiger_account';
		$fieldInstance->label = 'FL_SHARE_EXTERNALLY';
		$fieldInstance->name = 'share_externally';
		$fieldInstance->column = 'share_externally';
		$fieldInstance->columntype = 'tinyint(1)';
		$fieldInstance->uitype = 318;
		$fieldInstance->typeofdata = 'C~O';
		$fieldInstance->fieldparams = self::$serverId;
		$blockInstance->addField($fieldInstance);
	}

	/**
	 * Testing login.
	 */
	public function testLogIn(): void
	{
		$request = $this->httpClient->post('Users/Login', array_merge(
				[
					'json' => [
						'userName' => 'demo@yetiforce.com',
						'password' => 'demo',
					]
				], self::$requestOptions)
		);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		$this->assertSame(200, $request->getStatusCode(), 'Users/Login API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame(1, $response['status'], 'Users/Login API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::$authUserParams = $response['result'];
		self::$requestOptions['headers']['x-token'] = self::$authUserParams['token'];
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/Users/Login', 'post', 200);
	}

	/**
	 * Testing add record.
	 */
	public function testAddRecord(): void
	{
		$request = $this->httpClient->post('Accounts/Record/', array_merge(['json' => [
			'accountname' => 'Api YetiForce Sp. z o.o.',
			'addresslevel5a' => 'Warszawa',
			'addresslevel8a' => 'Marszałkowska',
			'buildingnumbera' => 111,
			'legal_form' => 'PLL_GENERAL_PARTNERSHIP',
			'share_externally' => 1
		]], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		$this->assertSame($request->getStatusCode(), 200, 'Accounts/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame($response['status'], 1, 'Accounts/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::$recordId = $response['result']['id'];
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/Record', 'post', 200);
	}

	/**
	 * Testing add inventory record.
	 */
	public function testAddInventoryRecord(): void
	{
		$request = $this->httpClient->post('SCalculations/Record/', array_merge(['json' => [
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
				]
			]
		]], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		$this->assertSame($request->getStatusCode(), 200, 'SCalculations/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame($response['status'], 1, 'SCalculations/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/Record', 'post', 200);
	}

	/**
	 * Testing edit record.
	 */
	public function testEditRecord(): void
	{
		$request = $this->httpClient->put('Accounts/Record/' . self::$recordId, array_merge(['json' => [
			'accountname' => 'Api YetiForce Sp. z o.o. New name',
			'buildingnumbera' => 222,
		]], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		$this->assertSame($request->getStatusCode(), 200, 'Accounts/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame($response['status'], 1, 'Accounts/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
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
		$this->assertSame($request->getStatusCode(), 200, 'Accounts/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame($response['status'], 1, 'Accounts/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame($response['result']['rawData']['accountname'], 'Api YetiForce Sp. z o.o. New name');
		$this->assertSame($response['result']['rawData']['legal_form'], 'PLL_GENERAL_PARTNERSHIP');
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
		$this->assertSame(200, $request->getStatusCode(), 'Accounts/RecordHistory/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame(1, $response['status'], 'Accounts/RecordHistory/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/RecordHistory/{recordId}', 'get', 200);
	}

	/**
	 * Testing delete record.
	 */
	public function testDeleteRecord(): void
	{
		$request = $this->httpClient->post('Accounts/Record/', array_merge(['json' => [
			'accountname' => 'Api Delete YetiForce Sp. z o.o.',
			'legal_form' => 'PLL_GENERAL_PARTNERSHIP',
			'share_externally' => 1
		]], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		$this->assertSame(200, $request->getStatusCode(), 'Accounts/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame(1, $response['status'], 'Accounts/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);

		$request = $this->httpClient->delete('Accounts/Record/' . $response['result']['id'], self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		$this->assertSame(200, $request->getStatusCode(), 'Accounts/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame(1, $response['status'], 'Accounts/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertTrue($response['result']);
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
		$this->assertSame($request->getStatusCode(), 200, 'Accounts/RecordRelatedList/{ID}/Contacts API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame($response['status'], 1, 'Accounts/RecordRelatedList/{ID}/Contacts API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/RecordRelatedList/{recordId}/{relatedModuleName}', 'get', 200);
	}

	/**
	 * Testing record list.
	 */
	public function testRecordList(): void
	{
		$request = $this->httpClient->get('Accounts/RecordsList', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		$this->assertSame($request->getStatusCode(), 200, 'Accounts/RecordsList/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame($response['status'], 1, 'Accounts/RecordsList/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertGreaterThanOrEqual(1, \count($response['result']['records']));
		$this->assertGreaterThanOrEqual(1, \count($response['result']['rawData']));
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/RecordsList', 'get', 200);

		$request = $this->httpClient->get('Users/RecordsList', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		$this->assertSame(200, $request->getStatusCode(), 'Users/RecordsList/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame(1, $response['status'], 'Users/RecordsList/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertGreaterThanOrEqual(1, \count($response['result']['records']));
		$this->assertGreaterThanOrEqual(1, \count($response['result']['rawData']));
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/{moduleName}/RecordsList', 'get', 200);
	}

	/**
	 * Testing get fields.
	 */
	public function testGetFields(): void
	{
		$request = $this->httpClient->get('Accounts/Fields/', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		$this->assertSame($request->getStatusCode(), 200, 'Accounts/Fields/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame($response['status'], 1, 'Accounts/Fields/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertTrue(!empty($response['result']['fields']), 'Accounts/Fields/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertTrue(!empty($response['result']['blocks']), 'Accounts/Fields/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
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
		$this->assertSame($request->getStatusCode(), 200, 'Accounts/Privileges/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame($response['status'], 1, 'Accounts/Privileges/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertTrue(!empty($response['result']), 'Accounts/Privileges/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
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
		$this->assertSame($request->getStatusCode(), 200, 'Accounts/Dashboard/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame($response['status'], 1, 'Accounts/Dashboard/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
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
		$this->assertSame($request->getStatusCode(), 200, 'Accounts/PdfTemplates/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame($response['status'], 1, 'Accounts/PdfTemplates/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
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
		$this->assertSame($request->getStatusCode(), 200, 'Modules API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame($response['status'], 1, 'Modules API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertTrue(!empty($response['result']['Accounts']), 'Modules API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
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
		$this->assertSame($request->getStatusCode(), 200, 'Methods API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame($response['status'], 1, 'Methods API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/Menu', 'get', 200);
	}

	/**
	 * Testing get api Install.
	 */
	public function testGetInstall(): void
	{
		$request = $this->httpClient->put('Install', array_merge(['json' => ['xxx' => 'yyy']], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		$this->assertSame($request->getStatusCode(), 200, 'Methods API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame($response['status'], 1, 'Methods API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/Install', 'put', 200);
	}

	/**
	 * Testing Logout.
	 */
	public function testLogout(): void
	{
		$request = $this->httpClient->put('Users/Logout', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		$this->assertSame(200, $request->getStatusCode(), 'Users/Logout API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->assertSame(1, $response['status'], 'Users/Logout API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/Portal/Users/Logout', 'put', 200);
	}

	/**
	 * Testing delete configuration.
	 */
	public function testDeleteConfiguration(): void
	{
		\Settings_WebserviceUsers_Record_Model::getInstanceById(self::$apiUserId, 'Portal')->delete();
		\Settings_WebserviceApps_Record_Model::getInstanceById(self::$serverId)->delete();

		$this->assertFalse((new \App\Db\Query())->from('w_#__servers')->where(['id' => self::$serverId])->exists(), 'Record in the database should not exist');
		$this->assertFalse((new \App\Db\Query())->from('w_#__portal_user')->where(['id' => self::$apiUserId])->exists(), 'Record in the database should not exist');
	}
}

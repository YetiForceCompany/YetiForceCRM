<?php
/**
 * Webservice Premium Test integrations test file.
 *
 * @see https://github.com/Maks3w/SwaggerAssertions/
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
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
final class WebservicePremiumTest extends \Tests\Base
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

	/** @var \Vtiger_Record_Model Temporary Contacts record object. */
	protected static $recordContacts;

	/** @var array Request options. */
	private static $requestOptions = [];

	/** @var array Details about logged in user. */
	private static $authUserParams;

	/** @var int Record ID */
	private static $recordId;

	/** @var int Inventory Record ID */
	private static $inventoryRecordId;

	/** @var SchemaManager */
	protected static $schemaManager;

	/** @var \GuzzleHttp\Client */
	protected $httpClient;

	/**
	 * PHPunit setUpBeforeClass method.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void
	{
		self::$schemaManager = new SchemaManager(json_decode(file_get_contents(ROOT_DIRECTORY . \App\Installer\Developer::PATH . '/WebservicePremium.json')));
	}

	/**
	 * PHPunit setUp method.
	 *
	 * @return void
	 */
	protected function setUp(): void
	{
		$this->httpClient = new \GuzzleHttp\Client(\App\Utils::merge(\App\RequestHttp::getOptions(), [
			'base_uri' => \App\Config::main('site_URL') . 'webservice/WebservicePremium/',
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
		$app->set('type', 'WebservicePremium');
		$app->set('status', 1);
		$app->set('name', 'portal');
		$app->set('url', '');
		$app->set('ips', '');
		$app->set('pass', 'portal');
		$app->save();
		self::$serverId = (int) $app->getId();

		$row = \App\Fields\ServerAccess::get(self::$serverId);
		static::assertNotFalse($row, 'No record id: ' . self::$serverId);
		static::assertSame($row['type'], 'WebservicePremium');
		static::assertSame($row['status'], 1);
		static::assertSame($row['name'], 'portal');
		static::assertSame($row['pass'], 'portal');
		self::$requestOptions['headers']['x-api-key'] = $row['api_key'];

		foreach ([
			// module name => block name or id
			'Accounts' => 'LBL_ACCOUNT_INFORMATION',
			'Contacts' => 'LBL_CONTACT_INFORMATION',
			'HelpDesk' => 'LBL_TICKET_INFORMATION',
			'FInvoiceProforma' => 'LBL_BASIC_DETAILS',
			'Products' => 'LBL_PRODUCT_INFORMATION',
			'Documents' => 17,
		] as $moduleName => $block) {
			$fieldModel = \Vtiger_Field_Model::init($moduleName, \App\Field::SYSTEM_FIELDS['share_externally']);
			$fieldModel->fieldparams = self::$serverId;
			$blockInstance = \vtlib\Block::getInstance($block, $moduleName);
			$blockInstance->addField($fieldModel);
		}

		self::$recordContacts = \Tests\Base\C_RecordActions::createContactRecord(false);
		self::$recordContacts->set('share_externally', 1);
		self::$recordContacts->save();

		$user = \Settings_WebserviceUsers_Record_Model::getCleanInstance('WebservicePremium');
		$user->setData([
			'server_id' => self::$serverId,
			'status' => 1,
			'user_name' => self::$apiUserName,
			'password' => \App\Encryption::createPasswordHash(self::$apiUserPass, 'WebservicePremium'),
			'type' => 4,
			'popupReferenceModule' => 'Contacts',
			'crmid' => self::$recordContacts->getId(),
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
		static::assertTrue(\App\Encryption::verifyPasswordHash(self::$apiUserPass, $row['password'], 'WebservicePremium'));
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
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/Users/Login', 'post', 200);
	}

	/**
	 * Testing add record.
	 */
	public function testAddRecord(): void
	{
		$recordModel = \Tests\Base\C_RecordActions::createAccountRecord();
		$request = $this->httpClient->post('HelpDesk/Record/', \App\Utils::merge(['json' => [
			'ticket_title' => 'Api HelpDesk',
			'parent_id' => $recordModel->getId(),
		]], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'HelpDesk/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'HelpDesk/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::$recordId = $response['result']['id'];
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/Record', 'post', 200);

		$request = $this->httpClient->post('Accounts/Record/', \App\Utils::merge(['json' => [
			'accountname' => 'Api YetiForce 2',
			'legal_form' => 'PLL_COMPANY',
			'account_id' => $recordModel->getId(),
		]], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Accounts/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Accounts/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/Record', 'post', 200);

		$request = $this->httpClient->post('ModComments/Record/', \App\Utils::merge(['json' => [
			'commentcontent' => 'Api comment content',
			'related_to' => $recordModel->getId(),
		]], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'ModComments/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'ModComments/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/Record', 'post', 200);
	}

	/**
	 * Testing add inventory record.
	 */
	public function testAddInventoryRecord(): void
	{
		$request = $this->httpClient->post('FInvoiceProforma/Record/', \App\Utils::merge(['json' => [
			'subject' => 'Api YetiForce FInvoiceProforma',
			'paymentdate' => date('Y-m-d'),
			'saledate' => date('Y-m-d'),
			'accountid' => \Tests\Base\C_RecordActions::createAccountRecord()->getId(),
			'payment_methods' => 'PLL_TRANSFER',
			'finvoiceproforma_status' => 'None',
			'payment_methods' => 'PLL_TRANSFER',
			'inventory' => [
				1 => [
					'name' => \Tests\Base\C_RecordActions::createProductRecord()->getId(),
					'qty' => 2,
					'price' => 5,
					'total' => 10,
					'unit' => 'l',
					'subunit' => '300g',
					'currency' => 1,
					'discount' => 0,
					'discountparam' => '',
					'discountmode' => 0,
					'net' => 10,
					'gross' => 10,
					'tax' => 0,
					'taxmode' => 0,
					'taxparam' => '[]',
					'comment1' => 0,
				],
			],
		]], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'FInvoiceProforma/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'FInvoiceProforma/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::$inventoryRecordId = $response['result']['id'];
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/Record', 'post', 200);
	}

	/**
	 * Testing edit record.
	 */
	public function testEditRecord(): void
	{
		$request = $this->httpClient->put('HelpDesk/Record/' . self::$recordId, \App\Utils::merge(['json' => [
			'ticket_title' => 'Api HelpDesk New name',
		]], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'HelpDesk/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'HelpDesk/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/Record/{recordId}', 'put', 200);
	}

	/**
	 * Testing get record.
	 */
	public function testGetRecord(): void
	{
		$request = $this->httpClient->get('HelpDesk/Record/' . self::$recordId, self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'HelpDesk/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'HelpDesk/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->logs = $response;
		static::assertSame('Api HelpDesk New name', $response['result']['rawData']['ticket_title']);
		static::assertSame(\Tests\Base\C_RecordActions::createAccountRecord()->getId(), $response['result']['rawData']['parent_id']);
		static::assertSame(1, $response['result']['rawData']['share_externally']);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/Record/{recordId}', 'get', 200);

		$request = $this->httpClient->get('FInvoiceProforma/Record/' . self::$inventoryRecordId, self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'FInvoiceProforma/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'FInvoiceProforma/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		$this->logs = $response;
		static::assertSame('Api YetiForce FInvoiceProforma', $response['result']['rawData']['subject']);
		static::assertSame(\Tests\Base\C_RecordActions::createAccountRecord()->getId(), $response['result']['rawData']['accountid']);
		static::assertSame(1, $response['result']['rawData']['share_externally']);
		static::assertSame(date('Y-m-d'), $response['result']['rawData']['paymentdate']);
		static::assertSame(date('Y-m-d'), $response['result']['rawData']['saledate']);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/Record/{recordId}', 'get', 200);
	}

	/**
	 * Testing get hierarchy.
	 */
	public function testGetHierarchy(): void
	{
		$request = $this->httpClient->get('Accounts/Hierarchy', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Accounts/Hierarchy/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Accounts/Hierarchy/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertNotEmpty($response['result']);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/Hierarchy', 'get', 200);
	}

	/**
	 * Testing get record history.
	 */
	public function testGetRecordHistory(): void
	{
		$request = $this->httpClient->get('HelpDesk/RecordHistory/' . self::$recordId, self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'HelpDesk/RecordHistory/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'HelpDesk/RecordHistory/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/RecordHistory/{recordId}', 'get', 200);
	}

	/**
	 * Testing delete record.
	 */
	public function testDeleteRecord(): void
	{
		$request = $this->httpClient->post('HelpDesk/Record/', \App\Utils::merge(['json' => [
			'ticket_title' => 'Api HelpDesk',
			'parent_id' => \Tests\Base\C_RecordActions::createAccountRecord()->getId(),
		]], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'HelpDesk/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'HelpDesk/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/Record', 'post', 200);

		$request = $this->httpClient->delete('HelpDesk/Record/' . $response['result']['id'], self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'HelpDesk/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'HelpDesk/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertTrue($response['result']);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/Record/{recordId}', 'delete', 200);
	}

	/**
	 * Testing get record related list.
	 */
	public function testGetRecordRelatedList(): void
	{
		$relationModel = \Vtiger_Relation_Model::getInstance(\Vtiger_Module_Model::getInstance('HelpDesk'), \Vtiger_Module_Model::getInstance('Contacts'));
		$relationModel->addRelation(self::$recordId, self::$recordContacts->getId());

		$request = $this->httpClient->get('HelpDesk/RecordRelatedList/' . self::$recordId . '/Contacts', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'HelpDesk/RecordRelatedList/{ID}/Contacts API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'HelpDesk/RecordRelatedList/{ID}/Contacts API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/RecordRelatedList/{recordId}/{relatedModuleName}', 'get', 200);
	}

	/**
	 * Testing get related modules list.
	 */
	public function testRelatedModules(): void
	{
		$request = $this->httpClient->get('Accounts/RelatedModules', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Accounts/RelatedModules API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Accounts/RelatedModules API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertTrue(isset($response['result']['base']), 'Accounts/RelatedModules API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertTrue(isset($response['result']['related']), 'Accounts/RelatedModules API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/RelatedModules', 'get', 200);
	}

	/**
	 * Testing record list.
	 */
	public function testRecordList(): void
	{
		$request = $this->httpClient->get('HelpDesk/RecordsList', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'HelpDesk/RecordsList/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'HelpDesk/RecordsList/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertGreaterThanOrEqual(1, \count($response['result']['records']));
		static::assertGreaterThanOrEqual(1, \count($response['result']['rawData']));
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/RecordsList', 'get', 200);

		$request = $this->httpClient->get('Users/RecordsList', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Users/RecordsList/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Users/RecordsList/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertCount(0, $response['result']['records']);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/RecordsList', 'get', 200);
	}

	/**
	 * Testing get fields.
	 */
	public function testGetFields(): void
	{
		$request = $this->httpClient->get('SQuotes/Fields/', \App\Utils::merge(['headers' => ['x-response-params' => '["inventory", "blocks", "privileges", "dbStructure", "queryOperators"]']], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'SQuotes/Fields/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'SQuotes/Fields/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertTrue(!empty($response['result']['fields']), 'SQuotes/Fields/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertTrue(!empty($response['result']['blocks']), 'SQuotes/Fields/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/Fields', 'get', 200);
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
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/Privileges', 'get', 200);
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
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/Dashboard', 'get', 200);
	}

	/**
	 * Testing get PDF Templates.
	 */
	public function testGetPdfTemplates(): void
	{
		$request = $this->httpClient->get('FInvoiceProforma/PdfTemplates/' . self::$inventoryRecordId, self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'FInvoiceProforma/PdfTemplates/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'FInvoiceProforma/PdfTemplates/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/PdfTemplates/{recordId}', 'get', 200);
	}

	/**
	 * Testing get PDF.
	 */
	public function testGetPdf(): void
	{
		$request = $this->httpClient->get('FInvoiceProforma/Pdf/' . self::$inventoryRecordId, self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'FInvoiceProforma/Pdf/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'FInvoiceProforma/Pdf/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/Pdf/{recordId}', 'get', 200);
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
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/Modules', 'get', 200);
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
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/Menu', 'get', 200);
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
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/CustomView', 'get', 200);

		$cvId = key($response['result']);
		$request = $this->httpClient->get('Accounts/CustomView/' . $cvId, self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Methods API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Methods API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertNotEmpty($response['result'], 'Result should not be empty' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame($cvId, $response['result']['cvid'], 'Result should be the same' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/CustomView/{cvId}', 'get', 200);
	}

	/**
	 * Tests for widgets api method.
	 */
	public function testWidgets(): void
	{
		$request = $this->httpClient->get('Accounts/Widgets', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Methods API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Methods API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertNotEmpty($response['result'], 'Result should not be empty' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/Widgets', 'get', 200);
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
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/Install', 'put', 200);
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
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/Users/Record/{userId}', 'get', 200);
	}

	/**
	 * Testing get products.
	 */
	public function testGetProducts(): void
	{
		$recordModel = \Tests\Base\C_RecordActions::createProductRecord();
		$dataBefore = $recordModel->getData();
		$recordModel->set('share_externally', 1);
		$recordModel->save();

		$createCommand = \App\Db::getInstance()->createCommand();
		$createCommand->update('vtiger_products', ['share_externally' => 1], ['productid' => $recordModel->getId()])->execute();

		$request = $this->httpClient->get('Products/Record/' . $recordModel->getId(), \App\Utils::merge([
			'headers' => [
				'x-unit-price' => 1,
				'x-unit-gross' => 1,
				'x-product-bundles' => 1,
			],
		], self::$requestOptions));
		$body = $request->getBody()->getContents();

		$this->logs = [
			'$body' => $body,
			'$recordModel->getData()1' => $dataBefore,
			'$recordModel->getData()2' => $recordModel->getData(),
			'row_products' => (new \App\Db\Query())->from('vtiger_products')->where(['productid' => $recordModel->getId()])->one(),
		];

		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Products/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Products/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame('System CRM YetiForce', $response['result']['rawData']['productname']);
		static::assertTrue(isset($response['result']['ext']['unit_price']), 'Products/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertTrue(isset($response['result']['ext']['unit_gross']), 'Products/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertTrue(isset($response['result']['productBundles']), 'Products/Record/{ID} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/Products/Record/{recordId}', 'get', 200);

		$request = $this->httpClient->get('Products/RecordsTree', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Products/RecordsTree API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Products/RecordsTree API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/Products/RecordsTree', 'get', 200);
	}

	/**
	 * Testing get Files.
	 */
	public function testGetFiles(): void
	{
		$recordModel = \Tests\Base\C_RecordActions::createDocumentsRecord(false);
		$recordModel->set('share_externally', 1);
		$recordModel->save();

		$apiUser = \Settings_WebserviceUsers_Record_Model::getInstanceById(self::$apiUserId, 'WebservicePremium');
		$contactId = $apiUser->get('crmid');
		$accountId = \Vtiger_Record_Model::getInstanceById($contactId)->get('parent_id');
		$relationModel = \Vtiger_Relation_Model::getInstance(\Vtiger_Module_Model::getInstance('Accounts'), $recordModel->getModule());
		$relationModel->addRelation($accountId, $recordModel->getId());

		$fileDetails = $recordModel->getFileDetails();
		$savedFile = $fileDetails['path'] . $fileDetails['attachmentsid'];
		$fileInstance = \App\Fields\File::loadFromPath($savedFile);
		$request = $this->httpClient->put('Files', \App\Utils::merge(['json' => [
			'module' => 'Documents',
			'actionName' => 'DownloadFile',
			'record' => $recordModel->getId(),
		]], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		static::assertSame(200, $request->getStatusCode(), 'Files API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame($body, $fileInstance->getContents(), 'Files API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($body, self::$schemaManager, '/webservice/WebservicePremium/Files', 'put', 200);
	}

	/**
	 * Testing upload files.
	 */
	public function testUploadFiles(): void
	{
		$request = $this->httpClient->post('Documents/Record/', \App\Utils::merge(['multipart' => [
			['name' => 'notes_title', 'contents' => 'test request 1'],
			['name' => 'filelocationtype', 'contents' => 'I'],
			['name' => 'filename', 'contents' => file_get_contents(ROOT_DIRECTORY . '/tests/data/stringHtml.txt'), 'filename' => 'stringHtml.txt'],
			['name' => 'relationOperation', 'contents' => true],
			['name' => 'relationId', 'contents' => 27],
			['name' => 'sourceModule', 'contents' => 'Contacts'],
			['name' => 'sourceRecord', 'contents' => self::$recordContacts->getId()],
		]], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Documents/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Documents/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertIsInt($response['result']['id'], 'Documents/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/Record', 'post', 200);

		$this->logs = $row = (new \App\Db\Query())->from('vtiger_notes')->where(['notesid' => $response['result']['id']])->one();
		static::assertSame('test request 1', $row['title']);
		static::assertSame('stringHtml.txt', $row['filename']);
		static::assertSame('I', $row['filelocationtype']);

		$request = $this->httpClient->put('Documents/Record/' . $response['result']['id'], \App\Utils::merge(['multipart' => [
			['name' => 'notes_title', 'contents' => 'test request 2'],
			['name' => 'filelocationtype', 'contents' => 'I'],
			['name' => 'filename', 'contents' => file_get_contents(ROOT_DIRECTORY . '/tests/data/TestLinux.zip'), 'filename' => 'TestLinux.zip'],
		]], self::$requestOptions));
		$body = $request->getBody()->getContents();
		$this->logs = print_r($row, true) . PHP_EOL . $body;
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Documents/Record/{recordId} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Documents/Record/{recordId} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertIsInt($response['result']['id'], 'Documents/Record/{recordId} API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/Record/{recordId}', 'put', 200);

		$this->logs = $row = (new \App\Db\Query())->from('vtiger_notes')->where(['notesid' => $response['result']['id']])->one();
		static::assertSame('test request 2', $row['title']);
		static::assertSame('TestLinux.zip', $row['filename']);
		static::assertSame('I', $row['filelocationtype']);
		static::assertSame(7, $row['filesize']);
		static::assertSame('application/zip', $row['filetype']);
	}

	/**
	 * Testing exceptions.
	 */
	public function testExceptions(): void
	{
		$request = $this->httpClient->post('HelpDesk/Record/', \App\Utils::merge(['json' => []], self::$requestOptions));
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(406, $request->getStatusCode(), 'HelpDesk/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(0, $response['status'], 'HelpDesk/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame('No input data', $response['error']['message'], 'HelpDesk/Record/ API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/{moduleName}/Record', 'post', 406);
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
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/Users/AccessActivityHistory', 'get', 200);
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
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/Users/ChangePassword', 'put', 200);
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
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/Users/Logout', 'put', 200);
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
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/Users/ResetPassword', 'post', 200);

		$row = (new \App\Db\Query())->from('s_#__tokens')->where(['method' => '\Api\WebserviceStandard\Users\ResetPassword'])->one();
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
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/WebservicePremium/Users/ResetPassword', 'post', 200);
	}

	/**
	 * Testing delete configuration.
	 */
	public function testDeleteConfiguration(): void
	{
		\Settings_WebserviceUsers_Record_Model::getInstanceById(self::$apiUserId, 'WebservicePremium')->delete();
		\Settings_WebserviceApps_Record_Model::getInstanceById(self::$serverId)->delete();

		static::assertFalse((bool) \App\Fields\ServerAccess::get(self::$serverId), 'Record in the database should not exist');
		static::assertFalse((new \App\Db\Query())->from('w_#__portal_user')->where(['id' => self::$apiUserId])->exists(), 'Record in the database should not exist');
	}
}

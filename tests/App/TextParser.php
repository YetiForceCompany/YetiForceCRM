<?php
/**
 * TextParser test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\App;

class TextParser extends \Tests\Base
{
	/**
	 * Test record instance.
	 *
	 * @var \App\TextParser
	 */
	private static $parserRecord;
	/**
	 * Test clean instance.
	 *
	 * @var \App\TextParser
	 */
	private static $parserClean;
	/**
	 * Test clean instance with module.
	 *
	 * @var \App\TextParser
	 */
	private static $parserCleanModule;

	/**
	 * Testing instances creation.
	 */
	public function testInstancesCreation()
	{
		static::$parserClean = \App\TextParser::getInstance();
		$this->assertInstanceOf('\App\TextParser', static::$parserClean, 'Expected clean instance without module of \App\TextParser');

		static::$parserCleanModule = \App\TextParser::getInstance('Leads');
		$this->assertInstanceOf('\App\TextParser', static::$parserCleanModule, 'Expected clean instance with module Leads of \App\TextParser');

		$this->assertInstanceOf('\App\TextParser', \App\TextParser::getInstanceById(\Tests\Base\C_RecordActions::createLeadRecord()->getId(), 'Leads'), 'Expected instance from lead id and module string of \App\TextParser');

		static::$parserRecord = \App\TextParser::getInstanceByModel(\Tests\Base\C_RecordActions::createLeadRecord());
		$this->assertInstanceOf('\App\TextParser', static::$parserRecord, 'Expected instance from record model of \App\TextParser');
	}

	/**
	 * Tests empty content condition.
	 */
	public function testEmptyContent()
	{
		$this->assertSame('', static::$parserClean
			->setContent('')
			->parse()
			->getContent(), 'Clean instance: empty content should return empty result');
	}

	/**
	 * Tests base variables list.
	 */
	public function testGetBaseListVariable()
	{
		$arr = static::$parserClean->getBaseListVariable();
		$this->assertInternalType('array', $arr, 'Expected array type');
		$this->assertNotEmpty($arr, 'Expected any related list data');
		foreach ($arr as $option) {
			$this->assertSame(1, \App\TextParser::isVaribleToParse($option['key']), 'Option: ' . $option['label'] . ', value: ' . $option['key'] . ' should be parseable');
		}
	}

	/**
	 * Tests related module variables list.
	 */
	public function testGetRelatedListVariable()
	{
		$arr = static::$parserCleanModule->getRelatedListVariable();
		$this->assertInternalType('array', $arr, 'Expected array type');
		$this->assertNotEmpty($arr, 'Expected any related list data');
		foreach ($arr as $option) {
			$this->assertSame(1, \App\TextParser::isVaribleToParse($option['key']), 'Option: ' . $option['label'] . ', value: ' . $option['key'] . ' should be parseable');
		}
	}

	/**
	 * Tests static methods.
	 */
	public function testStaticMethods()
	{
		$this->assertSame(1, \App\TextParser::isVaribleToParse('$(TestGroup : TestVar)$'), 'Clean instance: string should be parseable');
		$this->assertSame(0, \App\TextParser::isVaribleToParse('$X(TestGroup : TestVar)$'), 'Clean instance: string should be not parseable');
		$this->assertSame((\App\Config::main('listview_max_textlength') + 3), strlen(\App\TextParser::textTruncate(\Tests\Base\C_RecordActions::createLoremIpsumText(), false, true)), 'Clean instance: string should be truncated in expexted format (default length)');
		$this->assertSame(13, strlen(\App\TextParser::textTruncate(\Tests\Base\C_RecordActions::createLoremIpsumText(), 10, true)), 'Clean instance: string should be truncated in expexted format (text length: 10)');

		$this->assertSame((\App\Config::main('listview_max_textlength') + 993), strlen(\App\TextParser::htmlTruncate(\Tests\Base\C_RecordActions::createLoremIpsumHtml(), false, true)), 'Clean instance: html should be truncated in expected format (default length)');

		$this->assertSame(1008, strlen(\App\TextParser::htmlTruncate(\Tests\Base\C_RecordActions::createLoremIpsumHtml(), 10, true)), 'Clean instance: html should be truncated in expected format (text length: 10)');
	}

	/**
	 * Tests empty content condition.
	 */
	public function testUnregisteredFunction()
	{
		$this->assertSame('+  +', static::$parserClean
			->setContent('+ $(notExist : CurrentTime)$ +')
			->parse()
			->getContent(), 'Clean instance: unregistered function placeholder should return empty string');
	}

	/**
	 * Tests general placeholders replacement.
	 */
	public function testGeneral()
	{
		$this->assertSame('+ ' . (new \DateTimeField(null))->getDisplayDate() . ' +', static::$parserClean
			->setContent('+ $(general : CurrentDate)$ +')
			->parse()
			->getContent(), 'Clean instance: $(general : CurrentDate)$ should return current date');
		$this->assertSame('+ ' . \Vtiger_Util_Helper::convertTimeIntoUsersDisplayFormat(date('h:i:s')) . ' +', static::$parserClean
			->setContent('+ $(general : CurrentTime)$ +')
			->parse()
			->getContent(), 'Clean instance: $(general : CurrentTime)$ should return current time');
		$this->assertSame(
			'+ ' . (empty($defaultTimeZone = date_default_timezone_get()) ? \App\Config::main('default_timezone') : $defaultTimeZone) . ' +',
			static::$parserClean->setContent('+ $(general : BaseTimeZone)$ +')->parse()->getContent(),
			'Clean instance: $(general : BaseTimeZone)$ should return system timezone');
		$user = \App\User::getCurrentUserModel();
		$this->assertSame(
			'+ ' . ($user->getDetail('time_zone') ? $user->getDetail('time_zone') : \App\Config::main('default_timezone')) . ' +',
			static::$parserClean->setContent('+ $(general : UserTimeZone)$ +')->parse()->getContent(),
			'Clean instance: $(general : UserTimeZone)$ should return user timezone'
		);
		$currUser = \App\User::getCurrentUserId();
		\App\User::setCurrentUserId(0);
		$this->assertSame('+ ' . \App\Config::main('default_timezone') . ' +', static::$parserClean
			->setContent('+ $(general : UserTimeZone)$ +')
			->parse()
			->getContent(), 'Clean instance: $(general : UserTimeZone)$ when current user not set/exist should return default timezone');
		\App\User::setCurrentUserId($currUser);

		$this->assertSame('+ ' . \App\Config::main('site_URL') . ' +', static::$parserClean
			->setContent('+ $(general : SiteUrl)$ +')
			->parse()
			->getContent(), 'Clean instance: $(general : SiteUrl)$ should return site url');

		$this->assertSame('+ ' . \App\Config::main('PORTAL_URL') . ' +', static::$parserClean
			->setContent('+ $(general : PortalUrl)$ +')
			->parse()
			->getContent(), 'Clean instance: $(general : PortalUrl)$ should return portal url');

		$this->assertSame('+ PlaceholderNotExist +', static::$parserClean
			->setContent('+ $(general : PlaceholderNotExist)$ +')
			->parse()
			->getContent(), 'Clean instance: $(general : PlaceholderNotExist)$ should return placeholder var name');
	}

	/**
	 * Tests date placeholders replacement.
	 */
	public function testDate()
	{
		$this->assertSame('+ ' . \date('Y-m-d') . ' +', static::$parserClean
			->setContent('+ $(date : now)$ +')
			->parse()
			->getContent(), 'Clean instance: $(date : now)$ should return current date');

		$this->assertSame('+ ' . \date('Y-m-d', \strtotime('+1 day')) . ' +', static::$parserClean
			->setContent('+ $(date : tomorrow)$ +')
			->parse()
			->getContent(), 'Clean instance: $(date : tomorrow)$ should return tommorow date');

		$this->assertSame('+ ' . \date('Y-m-d', \strtotime('-1 day')) . ' +', static::$parserClean
			->setContent('+ $(date : yesterday)$ +')
			->parse()
			->getContent(), 'Clean instance: $(date : yesterday)$ should return yesterday date');

		$this->assertSame('+ ' . \date('Y-m-d', \strtotime('monday this week')) . ' +', static::$parserClean
			->setContent('+ $(date : monday this week)$ +')
			->parse()
			->getContent(), 'Clean instance: $(date : monday this week)$ should return this week monday date');

		$this->assertSame('+ ' . \date('Y-m-d', \strtotime('monday next week')) . ' +', static::$parserClean
			->setContent('+ $(date : monday next week)$ +')
			->parse()
			->getContent(), 'Clean instance: $(date : monday next week)$ should return next week monday date');

		$this->assertSame('+ ' . \date('Y-m-d', \strtotime('first day of this month')) . ' +', static::$parserClean
			->setContent('+ $(date : first day of this month)$ +')
			->parse()
			->getContent(), 'Clean instance: $(date : first day of this month)$ should return this month first day date');

		$this->assertSame('+ ' . \date('Y-m-d', \strtotime('last day of this month')) . ' +', static::$parserClean
			->setContent('+ $(date : last day of this month)$ +')
			->parse()
			->getContent(), 'Clean instance: $(date : last day of this month)$ should return this month last day date');

		$this->assertSame('+ ' . \date('Y-m-d', \strtotime('first day of next month')) . ' +', static::$parserClean
			->setContent('+ $(date : first day of next month)$ +')
			->parse()
			->getContent(), 'Clean instance: $(date : first day of next month)$ should return next month first day date');
	}

	/**
	 * Testing params functions.
	 */
	public function testParams()
	{
		static::$parserClean->setParams(['test_var' => 'test']);
		$text = '+ $(params : test_var)$ +';
		$this->assertSame('+ test +', static::$parserClean
			->setContent($text)
			->parse()
			->getContent(), 'Clean instance: Test params placeholder should return value test');
		$text = '+ $(params : test_var_not_exist)$ +';
		$this->assertSame('+  +', static::$parserClean
			->setContent($text)
			->parse()
			->getContent(), 'Clean instance: Test param not exist, placeholder should return empty value');
		$this->assertSame('test', static::$parserClean->getParam('test_var'), 'Clean instance: getParam should return value test');
		$this->assertFalse(static::$parserClean->getParam('test_var_not_exist'), 'Clean instance: key not exist, getParam should return false');
	}

	/**
	 * Testing basic field placeholder replacement.
	 */
	public function testBasicField()
	{
		$tmpUser = \App\User::getCurrentUserId();
		\App\User::setCurrentUserId((new \App\Db\Query())->select(['id'])->from('vtiger_users')->where(['status' => 'Active'])->andWhere(['not in', 'id', (new \App\Db\Query())->select(['smownerid'])->from('vtiger_crmentity')->where(['deleted' => 0, 'setype' => 'OSSEmployees'])
			->column()])
			->limit(1)->scalar());
		$text = '+ $(employee : last_name)$ +';
		$this->assertSame('+  +', static::$parserClean
			->setContent($text)
			->parse()
			->getContent(), 'Clean instance: By default employee last name should be empty');
		$this->assertSame('+  +', static::$parserClean
			->setContent($text)
			->parse()
			->getContent(), 'Clean instance: By default employee last name should be empty(cached)');
		$this->assertSame('+  +', static::$parserRecord
			->setContent($text)
			->parse()
			->getContent(), 'Record instance: By default employee last name should be empty');
		\App\User::setCurrentUserId($tmpUser);
		\App\Cache::clear();
	}

	/**
	 * Testing Employee placeholders.
	 */
	public function testEmployee()
	{
		$currentUser = \App\User::getCurrentUserId();
		$userName = 'Employee';
		$userExistsId = (new \App\Db\Query())->select(['id'])->from('vtiger_users')->where(['user_name' => $userName])
			->limit(1)->scalar();
		$employeeUser = $userExistsId ? \Vtiger_Record_Model::getInstanceById($userExistsId, 'Users') : \Vtiger_Record_Model::getCleanInstance('Users');

		$employeeUser->set('user_name', $userName);
		$employeeUser->set('email1', $userName . '@yetiforce.com');
		$employeeUser->set('first_name', $userName);
		$employeeUser->set('last_name', 'YetiForce');
		$employeeUser->set('user_password', \Tests\Base\A_User::$defaultPassrowd);
		$employeeUser->set('confirm_password', \Tests\Base\A_User::$defaultPassrowd);
		$employeeUser->set('roleid', 'H2');
		$employeeUser->set('is_admin', 'on');
		$employeeUser->save();
		$this->assertNotEmpty($employeeUser->getId(), 'New user id should be not empty');
		\App\User::setCurrentUserId($employeeUser->getId());
		$employeeExistsId = (new \App\Db\Query())
			->select(['crmid'])
			->from('vtiger_crmentity')->where(['deleted' => 0, 'setype' => 'OSSEmployees', 'smownerid' => $employeeUser->getId()])
			->limit(1)
			->scalar();
		$employeeModel = $employeeExistsId ? \Vtiger_Record_Model::getInstanceById($employeeExistsId, 'OSSEmployees') : \Vtiger_Record_Model::getCleanInstance('OSSEmployees');
		$employeeModel->set('assigned_user_id', $employeeUser->getId());
		$employeeModel->set('name', 'Test employee');
		$employeeModel->save();
		\App\Cache::clear();

		$text = '+ $(employee : name)$ +';
		$this->assertSame(
			'+ ' . \Vtiger_Record_Model::getInstanceById($employeeModel->getId(), 'OSSEmployees')->get('name') . ' +',
			\App\TextParser::getInstance()
				->setContent($text)
				->parse()
				->getContent(),
			'Clean instance: Employee name should be same as in db'
		);
		$this->assertSame(
			'+ ' . \Vtiger_Record_Model::getInstanceById($employeeModel->getId(), 'OSSEmployees')->get('name') . ' +',
			\App\TextParser::getInstance()
				->setContent($text)
				->parse()
				->getContent(),
			'Clean instance: Employee name should be same as in db(cached)'
		);
		\App\User::setCurrentUserId($currentUser);
	}

	/**
	 * Testing records list placeholders replacement.
	 */
	public function testRecordsListPlaceholders()
	{
		$text = '$(recordsList : Leads|lead_no,company,email,description|[[["company","a","Test"]]]|All|5)$';
		$result = \App\TextParser::getInstance()
			->setContent($text)
			->parse()
			->getContent();
		$this->assertNotEmpty($result, 'recordsList should return not empty string');
		$this->assertNotFalse(strpos($result, 'records-list'), 'Record list should contain html class recordsList');
		$this->assertSame(4, \substr_count($result, '<th '), 'Columns count should be equal to provided list');
		$text = '$(recordsList : Leads|lead_no,lastname,phone,description|[[["company","a","Test"]]]|NotExist|5)$';
		$result = \App\TextParser::getInstance()->withoutTranslations(true)
			->setContent($text)
			->parse()
			->getContent();
		$this->assertNotEmpty($result, 'recordsList should return not empty string(CustomView not exists)');
	}

	/**
	 * Testing related records list placeholders replacement.
	 */
	public function testRelatedRecordsList()
	{
		$text = '$(relatedRecordsList : Accounts|lead_no,lastname,phone,description|[[["company","a","Test"]]]|All|5)$';
		$result = \App\TextParser::getInstanceByModel(\Tests\Base\C_RecordActions::createLeadRecord())
			->setContent($text)
			->parse()
			->getContent();
		$this->assertEmpty($result, 'relatedRecordsList should return empty string if no related records found');
		$text = '$(relatedRecordsList : Leads|lead_no,lastname,phone,description|[[["company","a","Test"]]]|NotExist|5)$';
		$result = \App\TextParser::getInstanceByModel(\Tests\Base\C_RecordActions::createLeadRecord())->withoutTranslations(true)
			->setContent($text)
			->parse()
			->getContent();
		$this->assertEmpty($result, 'relatedRecordsList should return empty string if no related records found(CustomView not exists)');
		$accountModel = \Tests\Base\C_RecordActions::createAccountRecord(false);
		$contactModel = \Tests\Base\C_RecordActions::createContactRecord(false);
		$text = '$(relatedRecordsList : Contacts|firstname,decision_maker,createdtime,contactstatus,verification|[[["firstname","a","Test"]]]|All|5)$';
		$result = \App\TextParser::getInstanceByModel($accountModel)->withoutTranslations(true)
			->setContent($text)
			->parse()
			->getContent();
		$this->assertNotEmpty($result, 'relatedRecordsList should return not empty string if related records found');
		$this->assertNotFalse(\strpos($result, $contactModel->get('firstname')), 'relatedRecordsList should contain test record row');

		$text = '$(relatedRecordsList : Contacts|firstname,decision_maker,createdtime,contactstatus,verification|[[["firstname","a","Test"]]]|NotExists|5)$';
		$result = \App\TextParser::getInstanceByModel(\Tests\Base\C_RecordActions::createAccountRecord())->withoutTranslations(true)
			->setContent($text)
			->parse()
			->getContent();
		$this->assertNotEmpty($result, 'relatedRecordsList should return not empty string if related records found');
		$this->assertNotFalse(\strpos($result, $contactModel->get('firstname')), 'relatedRecordsList should contain test record row');
	}

	/**
	 * Testing custom placeholders.
	 */
	public function testCustomPlaceholders()
	{
		$text = '+ $(custom : NotExists)$ +';
		$this->assertSame('+  +', \App\TextParser::getInstance()
			->setContent($text)
			->parse()
			->getContent(), 'custom function with not existent parser should return empty string');
		$text = '+ $(custom : NotExists|NotExists)$ +';
		$this->assertSame('+  +', \App\TextParser::getInstance()
			->setContent($text)
			->parse()
			->getContent(), 'custom function with not existent parser should return empty string');
		$text = '+ $(custom : TableTaxSummary)$ +';
		$this->assertSame('+  +', \App\TextParser::getInstance()
			->setContent($text)
			->parse()
			->getContent(), 'custom function with TableTaxSummary parser should return empty string');
		$text = '+ $(custom : NotExists|Leads)$ +';
		$this->assertSame('+  +', \App\TextParser::getInstance()
			->setContent($text)
			->parse()
			->getContent(), 'custom function with not existent Leads module parser should return empty string');
	}

	/**
	 * Testing record vars placeholders replacement.
	 */
	public function testRecord()
	{
		$text = '+ $(record : NotExists)$ +';
		$this->assertSame('+  +', static::$parserClean->setContent($text)
			->parse()
			->getContent(), 'Expected empty string');

		$text = '+ $(record : CrmDetailViewURL)$ +';
		$this->assertSame('+ ' . \App\Config::main('site_URL') . 'index.php?module=Leads&view=Detail&record=' . \Tests\Base\C_RecordActions::createLeadRecord()->getId() . ' +', static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'Expected url is different');

		$text = '+ $(record : PortalDetailViewURL)$ +';
		$this->assertSame('+ ' . \App\Config::main('PORTAL_URL') . '/index.php?module=Leads&action=index&id=' . \Tests\Base\C_RecordActions::createLeadRecord()->getId() . ' +', static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'Expected url is different');

		$text = '+ $(record : ModuleName)$ +';
		$this->assertSame('+ Leads +', static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'Expected module name is different');

		$text = '+ $(record : RecordId)$ +';
		$this->assertSame('+ ' . \Tests\Base\C_RecordActions::createLeadRecord()->getId() . ' +', static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'Expected record id is different');

		$text = '+ $(record : RecordLabel)$ +';
		$this->assertSame('+ ' . \Tests\Base\C_RecordActions::createLeadRecord()->getName() . ' +', static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'Expected record label is different');

		$text = '+ $(record : ChangesListChanges)$ +';
		$this->assertSame('+  +', static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'Test record changes list should be empty');

		$text = '+ $(record : ChangesListValues)$ +';
		$this->assertNotSame('+  +', static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'Test record changes list values should be not empty');
		static::$parserRecord->withoutTranslations(true);
		$text = '+ $(record : ChangesListChanges)$ +';
		$this->assertSame('+  +', static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'Test record changes list should be empty(withoutTranslations)');

		$text = '+ $(record : ChangesListValues)$ +';
		$this->assertNotSame('+  +', static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'Test record changes list values should be not empty(withoutTranslations)');
		static::$parserRecord->withoutTranslations(false);
		$this->assertNotFalse(\strpos(static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'TestLead sp. z o.o.'), 'Test record changes list values should contain "TestLead sp. z o.o."');
		$this->assertNotFalse(\strpos(static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'autogenerated test lead for \App\TextParser tests'), 'Test record changes list values should contain "autogenerated test lead for \App\TextParser tests"');

		$changesModel = \Tests\Base\C_RecordActions::createLeadRecord();
		$changesModel->set('vat_id', 'test');
		$changesModel->save();
		$changesModel->set('vat_id', 'testing');
		$changesModel->save();

		$text = '+ $(record : ChangesListChanges)$ +';
		$this->assertNotFalse(strpos(static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'test'), 'Test record changes list should should contain vat_id info');
		static::$parserRecord->withoutTranslations(true);
		$text = '+ $(record : ChangesListChanges)$ +';
		$this->assertNotFalse(strpos(static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'test'), 'Test record changes list should should contain vat_id info');
		static::$parserRecord->withoutTranslations(false);
		$text = '+ $(record : ChangesListValues)$ +';
		$this->assertNotFalse(strpos(static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'testing'), 'Test record changes list values should be not empty');
		$text = '+ $(record : company)$ +';
		$this->assertSame('+ ' . \Tests\Base\C_RecordActions::createLeadRecord()->get('company') . ' +', static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'Test record company should be same as in db');
		$text = '+ $(record : Comments 5|true)$ +';
		$comment = \Vtiger_Record_Model::getCleanInstance('ModComments');
		$comment->set('commentcontent', 'TestComment');
		$comment->set('related_to', \Tests\Base\C_RecordActions::createLeadRecord()->getId());
		$comment->save();
		$this->assertSame('+ TestComment +', static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'Test record comments list should be empty');
		$text = '+ $(record : FieldNotExists)$ +';
		$this->assertSame('+  +', static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'Test function record when field not exists should return empty string');
	}

	/**
	 * Testing basic translate function.
	 */
	public function testTranslate()
	{
		$this->assertSame(
			'+$(general : CurrentDate)$ | ' . \App\Language::translate('LBL_SECONDS') . '==' . \App\Language::translate('LBL_COPY_BILLING_ADDRESS', 'Accounts') . '+',
			static::$parserClean->setContent('+$(general : CurrentDate)$ | $(translate : LBL_SECONDS)$==$(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$+')->parseTranslations()->getContent(),
			'Clean instance: Only translations should be replaced');
		$this->assertSame(
			'+$(general : CurrentDate)$ | ' . \App\Language::translate('LBL_SECONDS') . '==' . \App\Language::translate('LBL_COPY_BILLING_ADDRESS', 'Accounts') . '+',
			static::$parserClean->setLanguage('pl-PL')->setContent('+$(general : CurrentDate)$ | $(translate : LBL_SECONDS)$==$(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$+')->parseTranslations()->getContent(),
			'Clean instance: Only translations should be replaced(setLanguage)');

		$this->assertSame(
			'+' . \App\Language::translate('LBL_SECONDS') . '==' . \App\Language::translate('LBL_COPY_BILLING_ADDRESS', 'Accounts') . '+',
			static::$parserClean->setContent('+$(translate : LBL_SECONDS)$==$(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$+')->parse()->getContent(),
			'Clean instance: Translations should be equal');
		static::$parserClean->withoutTranslations(true);

		$this->assertSame(
			'+$(translate : LBL_SECONDS)$==$(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$+',
			static::$parserClean->setContent('+$(translate : LBL_SECONDS)$==$(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$+')->parse()->getContent(),
			'Clean instance: Translations should be equal');
		static::$parserClean->withoutTranslations(false);

		$this->assertSame(
			'+' . \App\Language::translate('LBL_SECONDS') . '==' . \App\Language::translate('LBL_COPY_BILLING_ADDRESS', 'Accounts') . '+',
			static::$parserRecord->setContent('+$(translate : LBL_SECONDS)$==$(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$+')->parse()->getContent(),
			'Record instance: Translations should be equal');
		static::$parserRecord->withoutTranslations(true);

		$this->assertSame(
			'+$(translate : LBL_SECONDS)$==$(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$+',
			static::$parserRecord->setContent('+$(translate : LBL_SECONDS)$==$(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$+')->parse()->getContent(),
			'Record instance: Translations should be equal');
		static::$parserRecord->withoutTranslations(false);
	}

	/**
	 * Tests record variables array.
	 */
	public function testGetRecordVariable()
	{
		$arr = static::$parserCleanModule->getRecordVariable();
		$this->assertInternalType('array', $arr, 'Expected array type');
		$this->assertNotEmpty($arr, 'Expected any related variables data');
		foreach ($arr as $group => $data) {
			$this->assertInternalType('array', $data, 'Expected array type');
			$this->assertNotEmpty($data, 'Expected any related variables data');
			foreach ($data as $key => $element) {
				$this->assertSame(1, \App\TextParser::isVaribleToParse($element['var_value']), 'Option: ' . $element['label'] . ', value: ' . $element['var_value'] . ' should be parseable in group: ' . $group);
				$this->assertSame(1, \App\TextParser::isVaribleToParse($element['var_label']), 'Option: ' . $element['label'] . ', value: ' . $element['var_label'] . ' should be parseable in group: ' . $group);
			}
		}
		$arr = static::$parserCleanModule->getRecordVariable();
		$this->assertInternalType('array', $arr, 'Expected (cached) array type');
		$this->assertNotEmpty($arr, 'Expected any (cached) related variables data');
	}

	/**
	 * Tests source variables array.
	 */
	public function testGetSourceVariable()
	{
		$this->assertFalse(\App\TextParser::getInstance('Leads')->setSourceRecord(\Tests\Base\C_RecordActions::createLeadRecord()->getId())->getSourceVariable(), 'TextParser::getSourceVariable() should return false for Leads module');
		$arr = \App\TextParser::getInstance('Campaigns')->setSourceRecord(\Tests\Base\C_RecordActions::createLeadRecord()->getId())->getSourceVariable();
		$this->assertInternalType('array', $arr, 'Expected array type');
		$this->assertNotEmpty($arr, 'Expected any related variables data');
		foreach ($arr as $key => $content) {
			$this->assertInternalType('array', $content, 'Expected array type');
			$this->assertNotEmpty($content, 'Expected any related variables data');
			foreach ($content as $group => $data) {
				$this->assertInternalType('array', $data, 'Expected array type');
				$this->assertNotEmpty($data, 'Expected any related variables data');
				if (isset($data['var_value'])) {
					$this->assertSame(1, \App\TextParser::isVaribleToParse($data['var_value']), 'Option: ' . $data['label'] . ', value: ' . $data['var_value'] . ' should be parseable in group: ' . $group);
					$this->assertSame(1, \App\TextParser::isVaribleToParse($data['var_label']), 'Option: ' . $data['label'] . ', value: ' . $data['var_label'] . ' should be parseable in group: ' . $group);
				} else {
					foreach ($data as $element) {
						$this->assertSame(1, \App\TextParser::isVaribleToParse($element['var_value']), 'Option: ' . $element['label'] . ', value: ' . $element['var_value'] . ' should be parseable in group: ' . $group);
						$this->assertSame(1, \App\TextParser::isVaribleToParse($element['var_label']), 'Option: ' . $element['label'] . ', value: ' . $element['var_label'] . ' should be parseable in group: ' . $group);
					}
				}
			}
		}
	}

	/**
	 * Tests related variables array.
	 */
	public function testGetRelatedVariable()
	{
		$arr = static::$parserCleanModule->getRelatedVariable();
		$this->assertInternalType('array', $arr, 'Expected array type');
		$this->assertNotEmpty($arr, 'Expected any related variables data');
		foreach ($arr as $key => $content) {
			$this->assertInternalType('array', $content, 'Expected array type');
			$this->assertNotEmpty($content, 'Expected any related variables data');
			foreach ($content as $group => $data) {
				$this->assertInternalType('array', $data, 'Expected array type');
				$this->assertNotEmpty($data, 'Expected any related variables data');
				foreach ($data as $element) {
					$this->assertSame(1, \App\TextParser::isVaribleToParse($element['var_value']), 'Option: ' . $element['label'] . ', value: ' . $element['var_value'] . ' should be parseable in group: ' . $group);
					$this->assertSame(1, \App\TextParser::isVaribleToParse($element['var_label']), 'Option: ' . $element['label'] . ', value: ' . $element['var_label'] . ' should be parseable in group: ' . $group);
				}
			}
		}
		$arr = static::$parserCleanModule->getRelatedVariable();
		$this->assertInternalType('array', $arr, 'Expected (cached) array type');
		$this->assertNotEmpty($arr, 'Expected any (cached) related variables data');
	}

	/**
	 * Tests general variables array.
	 */
	public function testGetGeneralVariable()
	{
		$arr = \App\TextParser::getInstance('IStorages')->getGeneralVariable();
		$this->assertInternalType('array', $arr, 'Expected array type');
		$this->assertNotEmpty($arr, 'Expected any general variables data');
		foreach ($arr as $groupName => $group) {
			$this->assertInternalType('array', $group, 'Expected array type from group: ' . $groupName);
			$this->assertNotEmpty($group, 'Expected any data in group: ' . $groupName);
			if (!empty($group)) {
				foreach ($group as $placeholder => $translation) {
					if (!\strpos($placeholder, ', ')) {
						$this->assertSame(1, \App\TextParser::isVaribleToParse($placeholder), 'Option: ' . $translation . ', value: ' . $placeholder . ' should be parseable in group: ' . $groupName);
					} else {
						$placeholders = \explode(', ', $placeholder);
						$this->assertInternalType('array', $placeholders, 'Expected array type  in group: ' . $groupName);
						$this->assertNotEmpty($placeholders, 'Expected any group data in group: ' . $groupName);
						foreach ($placeholders as $item) {
							$this->assertSame(1, \App\TextParser::isVaribleToParse($item), 'Option: ' . $translation . ', value: ' . $item . ' should be parseable in group: ' . $groupName);
						}
					}
				}
			}
		}
	}

	/**
	 * Testing basic source record related functions.
	 */
	public function testBasicSrcRecord()
	{
		$this->assertSame(
			'+  +', static::$parserClean->setContent('+ $(sourceRecord : description)$ +')->parse()->getContent(),
			'Clean instance: returned string should be empty if no source record provided');

		$this->assertSame(
			'+autogenerated test lead for \App\TextParser tests+', static::$parserClean->setContent('+$(sourceRecord : description)$+')->setSourceRecord(\Tests\Base\C_RecordActions::createLeadRecord()->getId())->parse()->getContent(),
			'Clean instance: Translations should be equal');

		$this->assertSame(
			'+autogenerated test lead for \App\TextParser tests+',
			static::$parserRecord->setContent('+$(sourceRecord : description)$+')->setSourceRecord(\Tests\Base\C_RecordActions::createLeadRecord()->getId())->parse()->getContent(),
			'Record instance: Translations should be equal');
	}

	/**
	 * Testing related record placeholders.
	 */
	public function testRelatedRecord()
	{
		$this->assertNotSame('+  +', '+ ' . \App\TextParser::getInstanceByModel(\Tests\Base\C_RecordActions::createContactRecord())->setContent('+$(relatedRecord : parent_id|accountname|Accounts)$+')->parse()->getContent() . ' +', 'Account name should be not empty');
		$this->assertNotSame('+  +', '+ ' . \App\TextParser::getInstanceByModel(\Tests\Base\C_RecordActions::createContactRecord())->setContent('+$(relatedRecord : parent_id|accountname)$+')->parse()->getContent() . ' +', 'Account name should be not empty(without module)');
		$this->assertNotSame('+  +', '+ ' . static::$parserRecord->setContent('+$(relatedRecord : assigned_user_id|user_name|Users)$+')->parse()->getContent() . ' +', 'Lead creator user_name should be not empty');
		$comment = \Vtiger_Record_Model::getCleanInstance('ModComments');
		$comment->set('commentcontent', 'TestComment');
		$comment->set('related_to', \Tests\Base\C_RecordActions::createLeadRecord()->getId());
		$comment->save();
		$this->assertNotSame('+  +', '+ ' . \App\TextParser::getInstanceById($comment->getId(), 'ModComments')->setContent('+ $(relatedRecord : related_to|company)$ +')->parse()->getContent() . ' +', 'Lead creator email should be not empty');
	}
}

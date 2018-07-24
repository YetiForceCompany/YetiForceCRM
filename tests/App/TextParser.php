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

		$this->assertInstanceOf('\App\TextParser', \App\TextParser::getInstanceById(\Tests\Entity\C_RecordActions::createLeadRecord()->getId(), 'Leads'), 'Expected instance from lead id and module string of \App\TextParser');

		static::$parserRecord = \App\TextParser::getInstanceByModel(\Tests\Entity\C_RecordActions::createLeadRecord());
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
		$this->assertSame((\AppConfig::main('listview_max_textlength') + 3), strlen(\App\TextParser::textTruncate(\Tests\Entity\C_RecordActions::createLoremIpsumText(), false, true)), 'Clean instance: string should be truncated in expexted format (default length)');
		$this->assertSame(13, strlen(\App\TextParser::textTruncate(\Tests\Entity\C_RecordActions::createLoremIpsumText(), 10, true)), 'Clean instance: string should be truncated in expexted format (text length: 10)');

		$this->assertSame((\AppConfig::main('listview_max_textlength') + 993), strlen(\App\TextParser::htmlTruncate(\Tests\Entity\C_RecordActions::createLoremIpsumHtml(), false, true)), 'Clean instance: html should be truncated in expected format (default length)');

		$this->assertSame(1008, strlen(\App\TextParser::htmlTruncate(\Tests\Entity\C_RecordActions::createLoremIpsumHtml(), 10, true)), 'Clean instance: html should be truncated in expected format (text length: 10)');
	}

	/**
	 * Tests empty content condition.
	 */
	public function testUnregisteredPlaceholderFunction()
	{
		$this->assertSame('+  +', static::$parserClean
			->setContent('+ $(notExist : CurrentTime)$ +')
			->parse()
			->getContent(), 'Clean instance: unregistered function placeholder should return empty string');
	}

	/**
	 * Tests general placeholders replacement.
	 */
	public function testGeneralPlaceholders()
	{
		$this->assertSame('+ ' . (new \DateTimeField(null))->getDisplayDate() . ' +', static::$parserClean
			->setContent('+ $(general : CurrentDate)$ +')
			->parse()
			->getContent(), 'Clean instance: $(general : CurrentDate)$ should return current date');
		$this->assertSame('+ ' . \Vtiger_Util_Helper::convertTimeIntoUsersDisplayFormat(date('h:i:s')) . ' +', static::$parserClean
			->setContent('+ $(general : CurrentTime)$ +')
			->parse()
			->getContent(), 'Clean instance: $(general : CurrentTime)$ should return current time');
		$this->assertSame('+ ' . \AppConfig::main('default_timezone') . ' +', static::$parserClean
			->setContent('+ $(general : BaseTimeZone)$ +')
			->parse()
			->getContent(), 'Clean instance: $(general : BaseTimeZone)$ should return system timezone');
		$user = \App\User::getCurrentUserModel();
		$this->assertSame('+ ' . ($user->getDetail('time_zone') ? $user->getDetail('time_zone') : \AppConfig::main('default_timezone')) . ' +', static::$parserClean
			->setContent('+ $(general : UserTimeZone)$ +')
			->parse()
			->getContent(), 'Clean instance: $(general : UserTimeZone)$ should return user timezone');
		$currUser = \App\User::getCurrentUserId();
		\App\User::setCurrentUserId(0);
		$this->assertSame('+ ' . \AppConfig::main('default_timezone') . ' +', static::$parserClean
			->setContent('+ $(general : UserTimeZone)$ +')
			->parse()
			->getContent(), 'Clean instance: $(general : UserTimeZone)$ when current user not set/exist should return default timezone');
		\App\User::setCurrentUserId($currUser);

		$this->assertSame('+ ' . \AppConfig::main('site_URL') . ' +', static::$parserClean
			->setContent('+ $(general : SiteUrl)$ +')
			->parse()
			->getContent(), 'Clean instance: $(general : SiteUrl)$ should return site url');

		$this->assertSame('+ ' . \AppConfig::main('PORTAL_URL') . ' +', static::$parserClean
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
	public function testDatePlaceholders()
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
	 * Testing basic field placeholder replacement.
	 */
	public function testBasicFieldPlaceholderReplacement()
	{
		if ((new \App\Db\Query())->select(['crmid'])->from('vtiger_crmentity')->where(['deleted' => 0, 'setype' => 'OSSEmployees', 'smownerid' => \App\User::getCurrentUserId()])
			->limit(1)->exists()) {
			$tmpUser = \App\User::getCurrentUserId();
			\App\User::setCurrentUserId((new \App\Db\Query())->select(['id'])->from('vtiger_users')->where(['status' => 'Active'])->andWhere(['not in', 'id', (new \App\Db\Query())->select(['smownerid'])->from('vtiger_crmentity')->where(['deleted' => 0, 'setype' => 'OSSEmployees'])
				->column()])
				->limit(1)->scalar());
		}
		$text = '+ $(employee : last_name)$ +';
		$this->assertSame('+  +', static::$parserClean
			->setContent($text)
			->parse()
			->getContent(), 'Clean instance: By default employee last name should be empty');
		$this->assertSame('+  +', static::$parserRecord
			->setContent($text)
			->parse()
			->getContent(), 'Record instance: By default employee last name should be empty');
		if (isset($tmpUser)) {
			\App\User::setCurrentUserId($tmpUser);
		}
	}

	/**
	 * Testing record vars placeholders replacement.
	 */
	public function testRecordPlaceholdersReplacement()
	{
		$text = '+ $(record : CrmDetailViewURL)$ +';
		$this->assertSame('+ ' . \AppConfig::main('site_URL') . 'index.php?module=Leads&view=Detail&record=' . \Tests\Entity\C_RecordActions::createLeadRecord()->getId() . ' +', static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'Expected url is different');

		$text = '+ $(record : PortalDetailViewURL)$ +';
		$this->assertSame('+ ' . \AppConfig::main('PORTAL_URL') . '/index.php?module=Leads&action=index&id=' . \Tests\Entity\C_RecordActions::createLeadRecord()->getId() . ' +', static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'Expected url is different');

		$text = '+ $(record : ModuleName)$ +';
		$this->assertSame('+ Leads +', static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'Expected module name is different');

		$text = '+ $(record : RecordId)$ +';
		$this->assertSame('+ ' . \Tests\Entity\C_RecordActions::createLeadRecord()->getId() . ' +', static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'Expected record id is different');

		$text = '+ $(record : RecordLabel)$ +';
		$this->assertSame('+ ' . \Tests\Entity\C_RecordActions::createLeadRecord()->getName() . ' +', static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'Expected record label is different');

		$text = '+ $(record : ChangesListChanges)$ +';
		$this->assertSame('+  +', static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'Test record changes list should be empty');

		$text = '+ $(record : ChangesListValues)$ +';
		$this->assertSame('+  +', static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'Test record changes list values should be empty');

		\Tests\Entity\C_RecordActions::createLeadRecord()->set('lastname', 'test');
		\Tests\Entity\C_RecordActions::createLeadRecord()->save();
		\Tests\Entity\C_RecordActions::createLeadRecord()->set('lastname', 'testing');
		\Tests\Entity\C_RecordActions::createLeadRecord()->save();

		$text = '+ $(record : ChangesListChanges)$ +';
		$this->assertNotFalse(strpos(static::$parserRecord->setContent($text)
			->parse()
			->getContent(), 'test'), 'Test record changes list should should contain lastname info');

		//$text = '+ $(record : ChangesListValues)$ +';
		//$this->assertSame('+ testing +', static::$parserRecord->setContent($text)
		//	->parse()
		//	->getContent(), 'Test record changes list values should be not empty');
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
		$this->assertFalse(\App\TextParser::getInstance('Leads')->setSourceRecord(\Tests\Entity\C_RecordActions::createLeadRecord()->getId())->getSourceVariable(), 'TextParser::getSourceVariable() should return false for Leads module');
		$arr = \App\TextParser::getInstance('Campaigns')->setSourceRecord(\Tests\Entity\C_RecordActions::createLeadRecord()->getId())->getSourceVariable();
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
		$fieldsArr = ['assigned_user_id'];
		$arr = static::$parserCleanModule->getRelatedVariable();
		$this->assertInternalType('array', $arr, 'Expected array type');
		$this->assertNotEmpty($arr, 'Expected any related variables data');
		foreach ($arr as $key => $content) {
			if (!is_array($content) || \in_array($content, $fieldsArr)) {
				continue;
			}
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
				foreach ($group as $placeholder=>$translation) {
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
			'+autogenerated test lead for \App\TextParser tests+', static::$parserClean->setContent('+$(sourceRecord : description)$+')->setSourceRecord(\Tests\Entity\C_RecordActions::createLeadRecord()->getId())->parse()->getContent(),
			'Clean instance: Translations should be equal');

		$this->assertSame(
			'+autogenerated test lead for \App\TextParser tests+',
			static::$parserRecord->setContent('+$(sourceRecord : description)$+')->setSourceRecord(\Tests\Entity\C_RecordActions::createLeadRecord()->getId())->parse()->getContent(),
			'Record instance: Translations should be equal');
	}
}
